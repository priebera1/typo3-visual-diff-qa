<?php

defined('TYPO3') or die();

// Register Scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Devsk\Visualdiff\Task\VisualDiffTask::class] = [
    'extension' => 'devsk_visualdiff',
    'title' => 'Visual Diff Comparison',
    'description' => 'Compare two bases (A,B) and identify pages with frontend differences',
    'additionalFields' => \Devsk\Visualdiff\Task\VisualDiffTaskAdditionalFieldProvider::class,
];
