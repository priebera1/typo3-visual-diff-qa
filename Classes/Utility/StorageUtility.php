<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Utility;

use Devsk\Visualdiff\Domain\Model\Job;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Utility for managing storage of visual diff data
 */
class StorageUtility implements SingletonInterface
{
    protected string $baseStoragePath;

    public function __construct()
    {
        $this->baseStoragePath = Environment::getVarPath() . '/visual-diff';
        
        // Ensure base directory exists
        if (!is_dir($this->baseStoragePath)) {
            mkdir($this->baseStoragePath, 0755, true);
        }
    }

    /**
     * Get job directory path
     */
    public function getJobPath(string $jobId): string
    {
        return $this->baseStoragePath . '/' . $jobId;
    }

    /**
     * Get image path for a URL
     */
    public function getImagePath(string $jobId, string $url, string $base): string
    {
        $jobPath = $this->getJobPath($jobId);
        $safeFileName = $this->sanitizeFileName($url);
        
        return $jobPath . '/images/' . $base . '/' . $safeFileName . '.png';
    }

    /**
     * Get diff image path for a URL
     */
    public function getDiffImagePath(string $jobId, string $url): string
    {
        $jobPath = $this->getJobPath($jobId);
        $safeFileName = $this->sanitizeFileName($url);
        
        return $jobPath . '/diffs/' . $safeFileName . '.png';
    }

    /**
     * Save job data
     */
    public function saveJob(Job $job): void
    {
        $jobPath = $this->getJobPath($job->getJobId());
        
        if (!is_dir($jobPath)) {
            mkdir($jobPath, 0755, true);
        }

        $jobFile = $jobPath . '/job.json';
        $jobData = [
            'jobId' => $job->getJobId(),
            'baseUrlA' => $job->getBaseUrlA(),
            'baseUrlB' => $job->getBaseUrlB(),
            'urls' => $job->getUrls(),
            'threshold' => $job->getThreshold(),
            'status' => $job->getStatus(),
            'createdAt' => $job->getCreatedAt()?->format('Y-m-d H:i:s'),
            'completedAt' => $job->getCompletedAt()?->format('Y-m-d H:i:s'),
            'results' => $job->getResults(),
        ];

        file_put_contents($jobFile, json_encode($jobData, JSON_PRETTY_PRINT));
    }

    /**
     * Load job data
     */
    public function loadJob(string $jobId): ?Job
    {
        $jobFile = $this->getJobPath($jobId) . '/job.json';
        
        if (!file_exists($jobFile)) {
            return null;
        }

        $jobData = json_decode(file_get_contents($jobFile), true);

        $job = new Job();
        $job->setJobId($jobData['jobId']);
        $job->setBaseUrlA($jobData['baseUrlA']);
        $job->setBaseUrlB($jobData['baseUrlB']);
        $job->setUrls($jobData['urls']);
        $job->setThreshold($jobData['threshold']);
        $job->setStatus($jobData['status']);
        
        if (!empty($jobData['createdAt'])) {
            $job->setCreatedAt(new \DateTime($jobData['createdAt']));
        }
        
        if (!empty($jobData['completedAt'])) {
            $job->setCompletedAt(new \DateTime($jobData['completedAt']));
        }
        
        if (!empty($jobData['results'])) {
            $job->setResults($jobData['results']);
        }

        return $job;
    }

    /**
     * Sanitize a filename
     */
    protected function sanitizeFileName(string $name): string
    {
        // Remove leading slash
        $name = ltrim($name, '/');
        
        // Replace slashes with underscores
        $name = str_replace('/', '_', $name);
        
        // Remove special characters
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        
        // If empty, use 'index'
        if (empty($name)) {
            $name = 'index';
        }

        return $name;
    }

    /**
     * List all jobs
     */
    public function listJobs(): array
    {
        $jobs = [];
        
        if (!is_dir($this->baseStoragePath)) {
            return $jobs;
        }

        $directories = glob($this->baseStoragePath . '/job-*', GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $jobId = basename($directory);
            $job = $this->loadJob($jobId);
            
            if ($job !== null) {
                $jobs[] = $job;
            }
        }

        return $jobs;
    }
}
