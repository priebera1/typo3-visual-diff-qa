<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Service;

use Devsk\Visualdiff\Domain\Model\Job;
use Devsk\Visualdiff\Utility\ImageComparator;
use Devsk\Visualdiff\Utility\ImageRenderer;
use Devsk\Visualdiff\Utility\StorageUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Main service for visual diff operations
 */
class VisualDiffService implements SingletonInterface
{
    protected ImageRenderer $imageRenderer;
    protected ImageComparator $imageComparator;
    protected StorageUtility $storageUtility;

    public function __construct()
    {
        $this->imageRenderer = GeneralUtility::makeInstance(ImageRenderer::class);
        $this->imageComparator = GeneralUtility::makeInstance(ImageComparator::class);
        $this->storageUtility = GeneralUtility::makeInstance(StorageUtility::class);
    }

    /**
     * Create a new comparison job
     */
    public function createJob(string $baseUrlA, string $baseUrlB, array $urls, float $threshold): string
    {
        $jobId = 'job-' . date('YmdHis') . '-' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
        
        $job = new Job();
        $job->setJobId($jobId);
        $job->setBaseUrlA($baseUrlA);
        $job->setBaseUrlB($baseUrlB);
        $job->setUrls($urls);
        $job->setThreshold($threshold);
        $job->setCreatedAt(new \DateTime());
        $job->setStatus('pending');
        
        $this->storageUtility->saveJob($job);
        
        return $jobId;
    }

    /**
     * Execute a comparison job
     */
    public function executeJob(string $jobId): array
    {
        $job = $this->storageUtility->loadJob($jobId);
        
        if (!$job) {
            throw new \RuntimeException('Job not found: ' . $jobId);
        }
        
        $job->setStatus('running');
        $this->storageUtility->saveJob($job);
        
        $results = [];
        
        foreach ($job->getUrls() as $url) {
            $result = $this->compareUrl($jobId, $job->getBaseUrlA(), $job->getBaseUrlB(), $url, $job->getThreshold());
            $results[] = $result;
        }
        
        $job->setStatus('completed');
        $job->setCompletedAt(new \DateTime());
        $job->setResults($results);
        $this->storageUtility->saveJob($job);
        
        return $results;
    }

    /**
     * Compare a single URL between two bases
     */
    protected function compareUrl(string $jobId, string $baseUrlA, string $baseUrlB, string $url, float $threshold): array
    {
        $urlA = rtrim($baseUrlA, '/') . '/' . ltrim($url, '/');
        $urlB = rtrim($baseUrlB, '/') . '/' . ltrim($url, '/');
        
        $result = [
            'url' => $url,
            'urlA' => $urlA,
            'urlB' => $urlB,
            'hasDifference' => false,
            'differencePercentage' => 0.0,
            'error' => null,
        ];
        
        try {
            // Render both URLs
            $imagePathA = $this->storageUtility->getImagePath($jobId, $url, 'A');
            $imagePathB = $this->storageUtility->getImagePath($jobId, $url, 'B');
            
            $this->imageRenderer->render($urlA, $imagePathA);
            $this->imageRenderer->render($urlB, $imagePathB);
            
            // Compare images
            $diffImagePath = $this->storageUtility->getDiffImagePath($jobId, $url);
            $differencePercentage = $this->imageComparator->compare($imagePathA, $imagePathB, $diffImagePath);
            
            $result['differencePercentage'] = $differencePercentage;
            $result['hasDifference'] = $differencePercentage >= $threshold;
            $result['imagePathA'] = $imagePathA;
            $result['imagePathB'] = $imagePathB;
            $result['diffImagePath'] = $diffImagePath;
            
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $result['hasDifference'] = true; // Consider errors as differences
        }
        
        return $result;
    }

    /**
     * Get job results (only pages with differences)
     */
    public function getJobIssues(string $jobId): array
    {
        $job = $this->storageUtility->loadJob($jobId);
        
        if (!$job) {
            throw new \RuntimeException('Job not found: ' . $jobId);
        }
        
        $results = $job->getResults();
        
        // Filter only pages with differences
        return array_filter($results, fn($result) => $result['hasDifference']);
    }
}
