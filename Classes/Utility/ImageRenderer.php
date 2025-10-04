<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Utility;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Utility for rendering web pages to images using wkhtmltoimage
 */
class ImageRenderer implements SingletonInterface
{
    /**
     * Path to wkhtmltoimage binary
     */
    protected string $binaryPath = '/usr/bin/wkhtmltoimage';

    /**
     * Default options for wkhtmltoimage
     */
    protected array $defaultOptions = [
        '--format' => 'png',
        '--width' => '1920',
        '--height' => '1080',
        '--quality' => '100',
        '--enable-javascript' => '',
        '--javascript-delay' => '2000',
        '--load-error-handling' => 'ignore',
        '--load-media-error-handling' => 'ignore',
    ];

    public function __construct()
    {
        // Try to find wkhtmltoimage in common locations
        $possiblePaths = [
            '/usr/bin/wkhtmltoimage',
            '/usr/local/bin/wkhtmltoimage',
            '/opt/bin/wkhtmltoimage',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                $this->binaryPath = $path;
                break;
            }
        }
    }

    /**
     * Render a URL to an image file
     */
    public function render(string $url, string $outputPath, array $options = []): void
    {
        if (!file_exists($this->binaryPath)) {
            throw new \RuntimeException(
                'wkhtmltoimage binary not found at: ' . $this->binaryPath . '. ' .
                'Please install wkhtmltopdf package: apt-get install wkhtmltopdf'
            );
        }

        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Merge options
        $options = array_merge($this->defaultOptions, $options);

        // Build command
        $command = escapeshellcmd($this->binaryPath);
        
        foreach ($options as $key => $value) {
            $command .= ' ' . escapeshellarg($key);
            if ($value !== '') {
                $command .= ' ' . escapeshellarg($value);
            }
        }
        
        $command .= ' ' . escapeshellarg($url) . ' ' . escapeshellarg($outputPath);

        // Execute command
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new \RuntimeException(
                'Failed to render ' . $url . ': ' . implode("\n", $output)
            );
        }
    }

    /**
     * Check if wkhtmltoimage is available
     */
    public function isAvailable(): bool
    {
        return file_exists($this->binaryPath) && is_executable($this->binaryPath);
    }

    /**
     * Get wkhtmltoimage version
     */
    public function getVersion(): string
    {
        if (!$this->isAvailable()) {
            return 'not installed';
        }

        $output = [];
        exec(escapeshellcmd($this->binaryPath) . ' --version 2>&1', $output);
        
        return implode("\n", $output);
    }
}
