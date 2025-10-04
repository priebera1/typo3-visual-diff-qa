# TYPO3 Visual Diff Extension (devsk_visualdiff)

A TYPO3 v13 extension for automated visual regression testing. Compare two website bases (A and B) and identify pages with frontend differences.

## Features

- **Scheduler-based**: Run visual comparisons as TYPO3 Scheduler tasks
- **Issues-only reporting**: Shows ONLY pages with detected frontend differences
- **No Node.js/Chromium required**: Uses `wkhtmltoimage` for HTML→PNG rendering
- **Pure PHP image comparison**: Uses PHP Imagick or GD for computing differences
- **Organized storage**: Results stored under `var/visual-diff/job-*`

## Requirements

- TYPO3 v13.0 or later
- PHP 8.1 or later
- `wkhtmltoimage` binary installed on the system
- PHP Imagick extension (recommended) or GD extension

### Installing wkhtmltoimage

#### Debian/Ubuntu
```bash
apt-get install wkhtmltopdf
```

#### Red Hat/CentOS
```bash
yum install wkhtmltopdf
```

#### macOS
```bash
brew install wkhtmltopdf
```

## Installation

1. Install via Composer:
```bash
composer require devsk/visualdiff
```

2. Activate the extension in the TYPO3 Extension Manager or via CLI:
```bash
./vendor/bin/typo3 extension:activate devsk_visualdiff
```

## Configuration

### Creating a Visual Diff Scheduler Task

1. Go to **System → Scheduler** in the TYPO3 Backend
2. Click **"Create new task"**
3. Select **"Visual Diff Comparison"** from the Class dropdown
4. Configure the task:
   - **Base URL A (Reference)**: URL of the reference site (e.g., `https://production.example.com`)
   - **Base URL B (Comparison)**: URL of the comparison site (e.g., `https://staging.example.com`)
   - **Page URLs**: Comma-separated list of page paths to compare (e.g., `/page1, /page2, /about-us`)
   - **Difference Threshold (%)**: Minimum difference percentage to report (default: 1.0)
5. Set the execution schedule (e.g., daily at 2 AM)
6. Save the task

### Example Configuration

```
Base URL A: https://production.example.com
Base URL B: https://staging.example.com
Page URLs: /, /products, /about, /contact
Threshold: 1.0
```

## Usage

### Running via Scheduler

Once configured, the Scheduler task will run automatically according to its schedule. You can also run it manually from the Scheduler module.

### Understanding Results

The extension stores results in `var/visual-diff/job-YYYYMMDDHHMMSS-HASH/`:

```
var/visual-diff/
└── job-20240101120000-abc12345/
    ├── job.json              # Job metadata and results
    ├── images/
    │   ├── A/                # Screenshots from Base A
    │   │   ├── index.png
    │   │   ├── products.png
    │   │   └── about.png
    │   └── B/                # Screenshots from Base B
    │       ├── index.png
    │       ├── products.png
    │       └── about.png
    └── diffs/                # Visual diff images
        ├── index.png
        ├── products.png
        └── about.png
```

### job.json Format

```json
{
    "jobId": "job-20240101120000-abc12345",
    "baseUrlA": "https://production.example.com",
    "baseUrlB": "https://staging.example.com",
    "urls": ["/", "/products", "/about"],
    "threshold": 1.0,
    "status": "completed",
    "createdAt": "2024-01-01 12:00:00",
    "completedAt": "2024-01-01 12:05:00",
    "results": [
        {
            "url": "/products",
            "urlA": "https://production.example.com/products",
            "urlB": "https://staging.example.com/products",
            "hasDifference": true,
            "differencePercentage": 5.2,
            "imagePathA": "var/visual-diff/job-.../images/A/products.png",
            "imagePathB": "var/visual-diff/job-.../images/B/products.png",
            "diffImagePath": "var/visual-diff/job-.../diffs/products.png",
            "error": null
        }
    ]
}
```

## Architecture

### Components

1. **Scheduler Task** (`VisualDiffTask`)
   - Configurable task for running comparisons
   - Validates configuration and executes jobs

2. **Visual Diff Service** (`VisualDiffService`)
   - Core service orchestrating the comparison workflow
   - Creates jobs, executes comparisons, manages results

3. **Image Renderer** (`ImageRenderer`)
   - Renders web pages to PNG images using `wkhtmltoimage`
   - Configurable rendering options

4. **Image Comparator** (`ImageComparator`)
   - Compares images using Imagick (preferred) or GD
   - Generates visual diff images with highlighted differences
   - Calculates difference percentage

5. **Storage Utility** (`StorageUtility`)
   - Manages file storage under `var/visual-diff/`
   - Handles job persistence and retrieval

6. **Job Model** (`Job`)
   - Domain model for comparison jobs
   - Tracks status, configuration, and results

## Limitations

- Only compares publicly accessible pages
- Does not interact with JavaScript-heavy dynamic content (2-second JS delay is used)
- Image comparison is pixel-based (layout shifts are detected as differences)
- Requires `wkhtmltoimage` to be installed on the system

## Troubleshooting

### wkhtmltoimage not found
```
Error: wkhtmltoimage binary not found
```
**Solution**: Install wkhtmltoimage package: `apt-get install wkhtmltopdf`

### Image comparison fails
```
Error: Failed to compare images
```
**Solution**: Ensure PHP Imagick or GD extension is installed and enabled

### Permission errors
```
Error: Failed to create directory
```
**Solution**: Ensure web server has write permissions to `var/` directory

## License

GPL-2.0-or-later

## Author

DevSK