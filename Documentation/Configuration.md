# Configuration Guide

## Installation

### Via Composer (Recommended)

```bash
composer require devsk/visualdiff
```

### Via Extension Manager

1. Download the extension from the TYPO3 Extension Repository (TER)
2. Upload to `typo3conf/ext/devsk_visualdiff/`
3. Activate via Extension Manager or CLI:
   ```bash
   ./vendor/bin/typo3 extension:activate devsk_visualdiff
   ```

## System Requirements

### Required Software

1. **wkhtmltoimage**: Install the wkhtmltopdf package which includes wkhtmltoimage

   **Debian/Ubuntu:**
   ```bash
   sudo apt-get update
   sudo apt-get install wkhtmltopdf
   ```

   **Red Hat/CentOS:**
   ```bash
   sudo yum install wkhtmltopdf
   ```

   **macOS:**
   ```bash
   brew install wkhtmltopdf
   ```

2. **PHP Imagick Extension** (recommended for better performance):

   **Debian/Ubuntu:**
   ```bash
   sudo apt-get install php-imagick
   sudo systemctl restart apache2  # or php-fpm
   ```

   **macOS:**
   ```bash
   pecl install imagick
   ```

   If Imagick is not available, the extension will automatically fall back to GD.

### Verify Installation

Check if wkhtmltoimage is properly installed:

```bash
which wkhtmltoimage
wkhtmltoimage --version
```

Check if PHP Imagick is available:

```bash
php -m | grep imagick
```

Or check if GD is available (fallback):

```bash
php -m | grep gd
```

## Scheduler Task Configuration

### Step 1: Create a New Task

1. Log in to TYPO3 Backend
2. Navigate to **System → Scheduler**
3. Click **"Create new task"**
4. Select **"Visual Diff Comparison"** from the Class dropdown

### Step 2: Configure Task Settings

#### Basic Task Configuration

- **Task name**: Give your task a descriptive name (e.g., "Daily Visual Diff - Production vs Staging")
- **Frequency**: Set how often the task should run
  - One-time execution
  - Recurring (cron expression)
  - Multiple times per day/week/month

#### Visual Diff Specific Configuration

**Base URL A (Reference)**
- The URL of your reference/production site
- Example: `https://www.example.com`
- This is the baseline for comparison

**Base URL B (Comparison)**
- The URL of the site to compare (staging, development, etc.)
- Example: `https://staging.example.com`
- Changes in this site will be compared against Base A

**Page URLs**
- Comma-separated list of page paths to compare
- Can be relative paths or absolute URLs
- Example: `/, /products, /about-us, /contact, /news`
- Supports:
  - Root pages: `/`
  - Subpages: `/products/item-1`
  - Deep URLs: `/category/subcategory/page`

**Difference Threshold (%)**
- Minimum difference percentage to consider as significant
- Range: 0-100
- Default: 1.0 (1%)
- Lower values = more sensitive (reports smaller differences)
- Higher values = less sensitive (reports only major differences)

### Step 3: Save and Execute

1. Click **"Save"** to create the task
2. Execute immediately by clicking the ▶️ play button, or
3. Wait for the scheduled execution time

## Example Configurations

### Example 1: Production vs Staging

```
Task Name: Production vs Staging Comparison
Base URL A: https://www.mysite.com
Base URL B: https://staging.mysite.com
Page URLs: /, /products, /about, /contact, /blog
Threshold: 1.0
Frequency: Daily at 2:00 AM
```

### Example 2: Before and After Deployment

```
Task Name: Pre-Deployment Visual Check
Base URL A: https://www.mysite.com
Base URL B: https://preview.mysite.com
Page URLs: /, /homepage, /landing-page, /checkout
Threshold: 0.5
Frequency: Manual execution
```

### Example 3: Multi-Language Site

```
Task Name: German vs English Version
Base URL A: https://www.mysite.com/de/
Base URL B: https://www.mysite.com/en/
Page URLs: /, /products, /about, /services
Threshold: 2.0
Frequency: Weekly
```

## Output Location

Results are stored in:
```
var/visual-diff/job-YYYYMMDDHHMMSS-HASH/
```

Example:
```
var/visual-diff/job-20240101020000-abc12345/
├── job.json                  # Job metadata and results
├── images/
│   ├── A/                    # Screenshots from Base A
│   │   ├── index.png
│   │   ├── products.png
│   │   └── about.png
│   └── B/                    # Screenshots from Base B
│       ├── index.png
│       ├── products.png
│       └── about.png
└── diffs/                    # Highlighted differences
    ├── index.png
    ├── products.png
    └── about.png
```

## Accessing Results

### Via File System

Navigate to `var/visual-diff/` in your TYPO3 installation directory.

### Via Scheduler Log

Check the Scheduler module for task execution logs showing:
- Number of pages compared
- Number of pages with differences
- Any errors encountered

### Programmatic Access

```php
use Devsk\Visualdiff\Service\VisualDiffService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$service = GeneralUtility::makeInstance(VisualDiffService::class);
$issues = $service->getJobIssues('job-20240101020000-abc12345');

foreach ($issues as $issue) {
    echo "Page: {$issue['url']}\n";
    echo "Difference: {$issue['differencePercentage']}%\n";
    echo "Diff image: {$issue['diffImagePath']}\n";
}
```

## Performance Considerations

### Rendering Time

- Each page takes 2-5 seconds to render (depends on page complexity)
- JavaScript delay is set to 2000ms by default
- For 10 pages, expect ~30-60 seconds total execution time

### Storage Space

- PNG images are typically 100KB - 2MB each
- 3 images per page (A, B, diff) = 300KB - 6MB per page
- 10 pages ≈ 3-60 MB per job
- Consider cleanup of old jobs to manage disk space

### Resource Usage

- CPU: High during image rendering and comparison
- Memory: ~100-200 MB per job
- Network: Depends on page size and external resources

### Optimization Tips

1. **Schedule during off-peak hours** (e.g., 2-4 AM)
2. **Limit number of pages** per task (10-20 pages recommended)
3. **Use multiple tasks** for large sites (split by section)
4. **Clean up old jobs** periodically (manual or automated)
5. **Adjust JavaScript delay** if pages don't need 2 seconds to load

## Troubleshooting

See the main [README.md](../README.md) for troubleshooting common issues.

## Advanced Configuration

### Custom wkhtmltoimage Options

Modify the default options in `Classes/Utility/ImageRenderer.php` or pass custom options when using the API:

```php
$renderer->render($url, $outputPath, [
    '--width' => '1280',
    '--height' => '720',
    '--javascript-delay' => '3000',
    '--custom-header' => ['Authorization', 'Bearer token123']
]);
```

### Custom Comparison Method

Force the use of GD instead of Imagick by modifying `Classes/Utility/ImageComparator.php`:

```php
// Change in constructor
$this->useImagick = false; // Force GD
```

## Security Considerations

1. **Authentication**: If your sites require authentication, consider:
   - Using HTTP Basic Auth in URLs (not recommended for production)
   - Implementing custom authentication in ImageRenderer
   - Using IP whitelisting on staging/preview environments

2. **Access Control**: Ensure the `var/visual-diff/` directory is not publicly accessible

3. **Sensitive Data**: Be aware that screenshots may contain sensitive information

## Next Steps

- Read the [API Documentation](API.md) for programmatic usage
- Check the [Examples](Examples/) for code samples
- Review the [CHANGELOG.md](../CHANGELOG.md) for version history
