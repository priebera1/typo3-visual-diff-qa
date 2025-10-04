# Visual Diff Extension API Documentation

## Core Classes

### VisualDiffService

Main service orchestrating visual diff operations.

**Location:** `Classes/Service/VisualDiffService.php`

#### Methods

##### `createJob(string $baseUrlA, string $baseUrlB, array $urls, float $threshold): string`

Creates a new comparison job.

**Parameters:**
- `$baseUrlA` - Reference base URL
- `$baseUrlB` - Comparison base URL
- `$urls` - Array of page paths to compare
- `$threshold` - Minimum difference threshold (0-100%)

**Returns:** Job ID (e.g., `job-20240101120000-abc12345`)

**Example:**
```php
$service = GeneralUtility::makeInstance(VisualDiffService::class);
$jobId = $service->createJob(
    'https://production.example.com',
    'https://staging.example.com',
    ['/', '/about', '/contact'],
    1.0
);
```

##### `executeJob(string $jobId): array`

Executes a comparison job and returns results.

**Parameters:**
- `$jobId` - Job ID returned by createJob()

**Returns:** Array of comparison results

**Example:**
```php
$results = $service->executeJob($jobId);
foreach ($results as $result) {
    if ($result['hasDifference']) {
        echo "Difference found on: {$result['url']}\n";
    }
}
```

##### `getJobIssues(string $jobId): array`

Gets only pages with detected differences.

**Parameters:**
- `$jobId` - Job ID

**Returns:** Filtered array containing only results with differences

---

### ImageRenderer

Utility for rendering web pages to PNG images using wkhtmltoimage.

**Location:** `Classes/Utility/ImageRenderer.php`

#### Methods

##### `render(string $url, string $outputPath, array $options = []): void`

Renders a URL to an image file.

**Parameters:**
- `$url` - URL to render
- `$outputPath` - Where to save the PNG file
- `$options` - Optional rendering options (see Default Options)

**Default Options:**
```php
[
    '--format' => 'png',
    '--width' => '1920',
    '--height' => '1080',
    '--quality' => '100',
    '--enable-javascript' => '',
    '--javascript-delay' => '2000',
    '--load-error-handling' => 'ignore',
    '--load-media-error-handling' => 'ignore',
]
```

**Example:**
```php
$renderer = GeneralUtility::makeInstance(ImageRenderer::class);
$renderer->render(
    'https://example.com/page',
    '/path/to/output.png',
    ['--width' => '1280', '--height' => '720']
);
```

##### `isAvailable(): bool`

Checks if wkhtmltoimage is available.

##### `getVersion(): string`

Gets wkhtmltoimage version string.

---

### ImageComparator

Utility for comparing images using PHP Imagick or GD.

**Location:** `Classes/Utility/ImageComparator.php`

#### Methods

##### `compare(string $imagePathA, string $imagePathB, string $diffImagePath): float`

Compares two images and generates a diff image.

**Parameters:**
- `$imagePathA` - Path to first image
- `$imagePathB` - Path to second image
- `$diffImagePath` - Where to save the diff image

**Returns:** Difference percentage (0-100)

**Example:**
```php
$comparator = GeneralUtility::makeInstance(ImageComparator::class);
$difference = $comparator->compare(
    '/path/to/imageA.png',
    '/path/to/imageB.png',
    '/path/to/diff.png'
);

if ($difference > 1.0) {
    echo "Images differ by {$difference}%\n";
}
```

##### `getMethod(): string`

Returns the comparison method being used ('Imagick' or 'GD').

---

### StorageUtility

Utility for managing storage of visual diff data.

**Location:** `Classes/Utility/StorageUtility.php`

#### Methods

##### `getJobPath(string $jobId): string`

Gets the directory path for a job.

##### `getImagePath(string $jobId, string $url, string $base): string`

Gets the path for a rendered image.

**Parameters:**
- `$jobId` - Job ID
- `$url` - Page URL
- `$base` - 'A' or 'B'

##### `getDiffImagePath(string $jobId, string $url): string`

Gets the path for a diff image.

##### `saveJob(Job $job): void`

Persists a job to disk as JSON.

##### `loadJob(string $jobId): ?Job`

Loads a job from disk.

##### `listJobs(): array`

Lists all jobs in the storage directory.

---

### Job Model

Domain model for comparison jobs.

**Location:** `Classes/Domain/Model/Job.php`

#### Properties

- `jobId` (string) - Unique job identifier
- `baseUrlA` (string) - Reference base URL
- `baseUrlB` (string) - Comparison base URL
- `urls` (array) - Page paths to compare
- `threshold` (float) - Difference threshold
- `status` (string) - Job status ('pending', 'running', 'completed')
- `createdAt` (DateTime) - Job creation time
- `completedAt` (DateTime) - Job completion time
- `results` (array) - Comparison results

---

## Result Structure

Each comparison result contains:

```php
[
    'url' => '/page',                           // Page path
    'urlA' => 'https://prod.example.com/page',  // Full URL A
    'urlB' => 'https://stage.example.com/page', // Full URL B
    'hasDifference' => true,                    // Whether difference exceeds threshold
    'differencePercentage' => 5.2,              // Difference percentage
    'imagePathA' => '/path/to/A/page.png',      // Screenshot A
    'imagePathB' => '/path/to/B/page.png',      // Screenshot B
    'diffImagePath' => '/path/to/diff/page.png',// Diff image
    'error' => null                             // Error message if any
]
```

---

## Scheduler Task Integration

The extension registers a Scheduler task that can be configured via the TYPO3 Backend.

**Task Class:** `Devsk\Visualdiff\Task\VisualDiffTask`

**Configuration Fields:**
- Base URL A (required)
- Base URL B (required)
- Page URLs (required, comma-separated)
- Difference Threshold (optional, default: 1.0)

The task is automatically registered via `ext_localconf.php` and can be found under:
**System → Scheduler → Create new task → Visual Diff Comparison**

---

## Storage Structure

```
var/visual-diff/
└── job-20240101120000-abc12345/
    ├── job.json              # Job metadata and results
    ├── images/
    │   ├── A/                # Screenshots from Base A
    │   │   ├── index.png
    │   │   └── about.png
    │   └── B/                # Screenshots from Base B
    │       ├── index.png
    │       └── about.png
    └── diffs/                # Visual diff images
        ├── index.png
        └── about.png
```

---

## Error Handling

All utility classes throw `\RuntimeException` on errors:

- **ImageRenderer:** When wkhtmltoimage fails or is not installed
- **ImageComparator:** When images cannot be loaded or compared
- **StorageUtility:** When file operations fail
- **VisualDiffService:** When job execution fails

Errors are logged and reported in the Scheduler task output.
