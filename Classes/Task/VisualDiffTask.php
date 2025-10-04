<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Task;

use Devsk\Visualdiff\Service\VisualDiffService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Scheduler task for visual diff comparison
 */
class VisualDiffTask extends AbstractTask
{
    /**
     * Base URL A (reference)
     */
    public string $baseUrlA = '';

    /**
     * Base URL B (comparison)
     */
    public string $baseUrlB = '';

    /**
     * Comma-separated list of page URLs to compare
     */
    public string $pageUrls = '';

    /**
     * Minimum difference threshold (0-100%)
     */
    public float $threshold = 1.0;

    /**
     * Execute the task
     */
    public function execute(): bool
    {
        if (empty($this->baseUrlA) || empty($this->baseUrlB)) {
            $this->logMessage('Base URLs A and B must be configured', 2);
            return false;
        }

        if (empty($this->pageUrls)) {
            $this->logMessage('No page URLs configured for comparison', 2);
            return false;
        }

        $visualDiffService = GeneralUtility::makeInstance(VisualDiffService::class);
        
        $urls = GeneralUtility::trimExplode(',', $this->pageUrls, true);
        
        try {
            $jobId = $visualDiffService->createJob($this->baseUrlA, $this->baseUrlB, $urls, $this->threshold);
            $results = $visualDiffService->executeJob($jobId);
            
            $issuesCount = count(array_filter($results, fn($r) => $r['hasDifference']));
            
            $this->logMessage(
                sprintf(
                    'Visual diff job %s completed: %d pages compared, %d with differences',
                    $jobId,
                    count($results),
                    $issuesCount
                ),
                $issuesCount > 0 ? 1 : 0
            );
            
            return true;
        } catch (\Exception $e) {
            $this->logMessage('Visual diff task failed: ' . $e->getMessage(), 2);
            return false;
        }
    }

    /**
     * Log a message
     */
    protected function logMessage(string $message, int $severity = 0): void
    {
        // Severity: 0 = info, 1 = warning, 2 = error
        if ($severity === 0) {
            echo '[INFO] ' . $message . PHP_EOL;
        } elseif ($severity === 1) {
            echo '[WARNING] ' . $message . PHP_EOL;
        } else {
            echo '[ERROR] ' . $message . PHP_EOL;
        }
    }
}
