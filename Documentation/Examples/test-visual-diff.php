<?php

/**
 * Example script to test the Visual Diff extension functionality
 * 
 * This script demonstrates how to use the Visual Diff service programmatically.
 * In a real TYPO3 installation, this would be executed via the Scheduler task.
 * 
 * Usage: php test-visual-diff.php
 */

declare(strict_types=1);

// Example configuration
$config = [
    'baseUrlA' => 'https://example.com',        // Reference site
    'baseUrlB' => 'https://staging.example.com', // Comparison site
    'pageUrls' => ['/', '/about', '/contact'],  // Pages to compare
    'threshold' => 1.0,                         // 1% difference threshold
];

echo "=== Visual Diff Extension Test ===" . PHP_EOL . PHP_EOL;
echo "Configuration:" . PHP_EOL;
echo "  Base A: {$config['baseUrlA']}" . PHP_EOL;
echo "  Base B: {$config['baseUrlB']}" . PHP_EOL;
echo "  Pages: " . implode(', ', $config['pageUrls']) . PHP_EOL;
echo "  Threshold: {$config['threshold']}%" . PHP_EOL . PHP_EOL;

echo "This is a demonstration script. In a real TYPO3 installation:" . PHP_EOL;
echo "1. Install the extension via composer or Extension Manager" . PHP_EOL;
echo "2. Create a Scheduler task via Backend > System > Scheduler" . PHP_EOL;
echo "3. Configure the task with your base URLs and page paths" . PHP_EOL;
echo "4. Run the task manually or wait for scheduled execution" . PHP_EOL;
echo "5. Find results in var/visual-diff/job-*/" . PHP_EOL . PHP_EOL;

echo "Expected workflow:" . PHP_EOL;
echo "  → Create job with ID: job-YYYYMMDDHHMMSS-HASH" . PHP_EOL;
echo "  → Render screenshots using wkhtmltoimage:" . PHP_EOL;
echo "    - var/visual-diff/job-*/images/A/index.png" . PHP_EOL;
echo "    - var/visual-diff/job-*/images/B/index.png" . PHP_EOL;
echo "  → Compare images using Imagick/GD" . PHP_EOL;
echo "  → Generate diff images:" . PHP_EOL;
echo "    - var/visual-diff/job-*/diffs/index.png" . PHP_EOL;
echo "  → Save results to job.json" . PHP_EOL;
echo "  → Report pages with differences >= threshold" . PHP_EOL . PHP_EOL;

echo "Requirements:" . PHP_EOL;
echo "  ✓ TYPO3 v13.0+" . PHP_EOL;
echo "  ✓ PHP 8.1+" . PHP_EOL;
echo "  ✓ wkhtmltoimage (apt-get install wkhtmltopdf)" . PHP_EOL;
echo "  ✓ PHP Imagick or GD extension" . PHP_EOL . PHP_EOL;

echo "=== Test Complete ===" . PHP_EOL;
