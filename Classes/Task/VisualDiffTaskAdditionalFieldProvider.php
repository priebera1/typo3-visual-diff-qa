<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Task;

use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional field provider for Visual Diff Task
 */
class VisualDiffTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {
        $additionalFields = [];

        // Base URL A
        if (empty($taskInfo['baseUrlA'])) {
            $taskInfo['baseUrlA'] = $task->baseUrlA ?? '';
        }
        $additionalFields['baseUrlA'] = [
            'code' => '<input type="text" name="tx_scheduler[baseUrlA]" id="baseUrlA" value="' . htmlspecialchars($taskInfo['baseUrlA']) . '" size="50" />',
            'label' => 'Base URL A (Reference)',
            'cshKey' => '',
            'cshLabel' => ''
        ];

        // Base URL B
        if (empty($taskInfo['baseUrlB'])) {
            $taskInfo['baseUrlB'] = $task->baseUrlB ?? '';
        }
        $additionalFields['baseUrlB'] = [
            'code' => '<input type="text" name="tx_scheduler[baseUrlB]" id="baseUrlB" value="' . htmlspecialchars($taskInfo['baseUrlB']) . '" size="50" />',
            'label' => 'Base URL B (Comparison)',
            'cshKey' => '',
            'cshLabel' => ''
        ];

        // Page URLs
        if (empty($taskInfo['pageUrls'])) {
            $taskInfo['pageUrls'] = $task->pageUrls ?? '';
        }
        $additionalFields['pageUrls'] = [
            'code' => '<textarea name="tx_scheduler[pageUrls]" id="pageUrls" rows="5" cols="50">' . htmlspecialchars($taskInfo['pageUrls']) . '</textarea><br><small>Comma-separated list of page paths (e.g., /page1, /page2, /page3)</small>',
            'label' => 'Page URLs',
            'cshKey' => '',
            'cshLabel' => ''
        ];

        // Threshold
        if (empty($taskInfo['threshold'])) {
            $taskInfo['threshold'] = $task->threshold ?? 1.0;
        }
        $additionalFields['threshold'] = [
            'code' => '<input type="number" name="tx_scheduler[threshold]" id="threshold" value="' . htmlspecialchars((string)$taskInfo['threshold']) . '" step="0.1" min="0" max="100" /><br><small>Minimum difference threshold in % (default: 1.0)</small>',
            'label' => 'Difference Threshold (%)',
            'cshKey' => '',
            'cshLabel' => ''
        ];

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        $valid = true;

        if (empty($submittedData['baseUrlA'])) {
            $schedulerModule->addMessage('Base URL A is required', 2);
            $valid = false;
        }

        if (empty($submittedData['baseUrlB'])) {
            $schedulerModule->addMessage('Base URL B is required', 2);
            $valid = false;
        }

        if (empty($submittedData['pageUrls'])) {
            $schedulerModule->addMessage('At least one page URL is required', 2);
            $valid = false;
        }

        if (!is_numeric($submittedData['threshold']) || $submittedData['threshold'] < 0 || $submittedData['threshold'] > 100) {
            $schedulerModule->addMessage('Threshold must be a number between 0 and 100', 2);
            $valid = false;
        }

        return $valid;
    }

    /**
     * Takes care of saving the additional fields' values
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        /** @var VisualDiffTask $task */
        $task->baseUrlA = $submittedData['baseUrlA'];
        $task->baseUrlB = $submittedData['baseUrlB'];
        $task->pageUrls = $submittedData['pageUrls'];
        $task->threshold = (float)$submittedData['threshold'];
    }
}
