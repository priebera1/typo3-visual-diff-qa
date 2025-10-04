<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Utility;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Utility for comparing images using PHP Imagick or GD
 */
class ImageComparator implements SingletonInterface
{
    protected bool $useImagick;

    public function __construct()
    {
        $this->useImagick = extension_loaded('imagick');
    }

    /**
     * Compare two images and return the difference percentage
     * 
     * @param string $imagePathA Path to first image
     * @param string $imagePathB Path to second image
     * @param string $diffImagePath Path where diff image should be saved
     * @return float Difference percentage (0-100)
     */
    public function compare(string $imagePathA, string $imagePathB, string $diffImagePath): float
    {
        if (!file_exists($imagePathA)) {
            throw new \RuntimeException('Image A not found: ' . $imagePathA);
        }

        if (!file_exists($imagePathB)) {
            throw new \RuntimeException('Image B not found: ' . $imagePathB);
        }

        // Ensure output directory exists
        $outputDir = dirname($diffImagePath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        if ($this->useImagick) {
            return $this->compareWithImagick($imagePathA, $imagePathB, $diffImagePath);
        }

        return $this->compareWithGD($imagePathA, $imagePathB, $diffImagePath);
    }

    /**
     * Compare images using Imagick
     */
    protected function compareWithImagick(string $imagePathA, string $imagePathB, string $diffImagePath): float
    {
        $imageA = new \Imagick($imagePathA);
        $imageB = new \Imagick($imagePathB);

        // Ensure both images have the same dimensions
        $widthA = $imageA->getImageWidth();
        $heightA = $imageA->getImageHeight();
        $widthB = $imageB->getImageWidth();
        $heightB = $imageB->getImageHeight();

        // Use the larger dimensions
        $maxWidth = max($widthA, $widthB);
        $maxHeight = max($heightA, $heightB);

        if ($widthA !== $maxWidth || $heightA !== $maxHeight) {
            $imageA->extentImage($maxWidth, $maxHeight, 0, 0);
        }

        if ($widthB !== $maxWidth || $heightB !== $maxHeight) {
            $imageB->extentImage($maxWidth, $maxHeight, 0, 0);
        }

        // Compare images
        $result = $imageA->compareImages($imageB, \Imagick::METRIC_MEANSQUAREERROR);
        $diffImage = $result[0];
        $differenceValue = $result[1];

        // Save diff image
        $diffImage->setImageFormat('png');
        $diffImage->writeImage($diffImagePath);

        // Clean up
        $imageA->clear();
        $imageB->clear();
        $diffImage->clear();

        // Convert metric to percentage (0-1 range to 0-100)
        return $differenceValue * 100;
    }

    /**
     * Compare images using GD (fallback)
     */
    protected function compareWithGD(string $imagePathA, string $imagePathB, string $diffImagePath): float
    {
        $imageA = $this->loadImageGD($imagePathA);
        $imageB = $this->loadImageGD($imagePathB);

        $widthA = imagesx($imageA);
        $heightA = imagesy($imageA);
        $widthB = imagesx($imageB);
        $heightB = imagesy($imageB);

        // Use the larger dimensions
        $maxWidth = max($widthA, $widthB);
        $maxHeight = max($heightA, $heightB);

        // Create diff image
        $diffImage = imagecreatetruecolor($maxWidth, $maxHeight);
        
        if ($diffImage === false) {
            throw new \RuntimeException('Failed to create diff image');
        }

        $totalPixels = $maxWidth * $maxHeight;
        $differentPixels = 0;

        // Compare pixel by pixel
        for ($x = 0; $x < $maxWidth; $x++) {
            for ($y = 0; $y < $maxHeight; $y++) {
                $colorA = ($x < $widthA && $y < $heightA) ? imagecolorat($imageA, $x, $y) : 0;
                $colorB = ($x < $widthB && $y < $heightB) ? imagecolorat($imageB, $x, $y) : 0;

                if ($colorA !== $colorB) {
                    $differentPixels++;
                    // Mark difference in red
                    imagesetpixel($diffImage, $x, $y, imagecolorallocate($diffImage, 255, 0, 0));
                } else {
                    // Keep original
                    imagesetpixel($diffImage, $x, $y, $colorA);
                }
            }
        }

        // Save diff image
        imagepng($diffImage, $diffImagePath);

        // Clean up
        imagedestroy($imageA);
        imagedestroy($imageB);
        imagedestroy($diffImage);

        // Calculate difference percentage
        return ($differentPixels / $totalPixels) * 100;
    }

    /**
     * Load an image using GD
     */
    protected function loadImageGD(string $path)
    {
        $imageInfo = getimagesize($path);
        
        if ($imageInfo === false) {
            throw new \RuntimeException('Failed to get image info: ' . $path);
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($path);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($path);
                break;
            default:
                throw new \RuntimeException('Unsupported image type: ' . $mimeType);
        }

        if ($image === false) {
            throw new \RuntimeException('Failed to load image: ' . $path);
        }

        return $image;
    }

    /**
     * Check which comparison method is being used
     */
    public function getMethod(): string
    {
        return $this->useImagick ? 'Imagick' : 'GD';
    }
}
