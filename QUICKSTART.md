# Quick Start Guide

Get started with the Visual Diff extension in 5 minutes!

## Prerequisites

Ensure you have:
- ✅ TYPO3 v13 installation
- ✅ PHP 8.1 or later
- ✅ wkhtmltoimage installed (`apt-get install wkhtmltopdf`)
- ✅ PHP Imagick or GD extension

## Installation

### 1. Install the Extension

```bash
composer require devsk/visualdiff
```

### 2. Activate the Extension

```bash
./vendor/bin/typo3 extension:activate devsk_visualdiff
```

## Usage

### 3. Create a Scheduler Task

1. **Backend:** System → Scheduler
2. **Click:** "Create new task"
3. **Select:** "Visual Diff Comparison"
4. **Configure:**
   ```
   Base URL A: https://production.example.com
   Base URL B: https://staging.example.com
   Page URLs: /, /products, /about
   Threshold: 1.0
   ```
5. **Save** and click the ▶️ play button to run immediately

### 4. View Results

Check the output in:
```
var/visual-diff/job-YYYYMMDDHHMMSS-HASH/
```

Open `job.json` to see results:
```json
{
  "results": [
    {
      "url": "/products",
      "hasDifference": true,
      "differencePercentage": 5.2
    }
  ]
}
```

## What You Get

For each comparison, you'll find:

📁 **var/visual-diff/job-20240101120000-abc12345/**
- `job.json` - Comparison results and metadata
- `images/A/` - Screenshots from Base A (reference)
- `images/B/` - Screenshots from Base B (comparison)
- `diffs/` - Visual diff images showing differences

## Understanding Results

The extension shows **ONLY pages with differences** that exceed your threshold.

**Example output in job.json:**
```json
{
  "jobId": "job-20240101120000-abc12345",
  "status": "completed",
  "results": [
    {
      "url": "/products",
      "hasDifference": true,
      "differencePercentage": 5.2,
      "imagePathA": "var/visual-diff/.../images/A/products.png",
      "imagePathB": "var/visual-diff/.../images/B/products.png",
      "diffImagePath": "var/visual-diff/.../diffs/products.png"
    }
  ]
}
```

## Common Use Cases

### Case 1: Production vs Staging
Compare production with staging before deployment:
```
Base A: https://www.mysite.com
Base B: https://staging.mysite.com
Pages: /, /products, /checkout
```

### Case 2: Before and After
Compare same site at different times:
```
Base A: https://www.mysite.com
Base B: https://preview.mysite.com
Pages: /, /homepage, /landing
```

### Case 3: Multi-Version Testing
Compare different versions:
```
Base A: https://v1.mysite.com
Base B: https://v2.mysite.com
Pages: /, /features, /pricing
```

## Tips

💡 **Scheduling:** Run during off-peak hours (e.g., 2 AM) for better performance

💡 **Threshold:** Start with 1.0% and adjust based on your needs
- Lower = more sensitive
- Higher = less sensitive

💡 **Page Selection:** Start with critical pages:
- Homepage
- Key landing pages
- Checkout/conversion pages
- Contact forms

## Troubleshooting

### wkhtmltoimage not found
```bash
sudo apt-get install wkhtmltopdf
which wkhtmltoimage  # Should show: /usr/bin/wkhtmltoimage
```

### Images not generated
Check Scheduler logs in TYPO3 Backend → System → Scheduler

### Permission issues
```bash
chmod -R 755 var/
chown -R www-data:www-data var/  # Adjust user/group as needed
```

## Next Steps

📖 Read the full [README](README.md) for detailed information

📖 Check [Configuration Guide](Documentation/Configuration.md) for advanced options

📖 Browse [API Documentation](Documentation/API.md) for programmatic usage

## Need Help?

- Check the [README](README.md) troubleshooting section
- Review the [CHANGELOG](CHANGELOG.md) for known issues
- Open an issue on GitHub

---

**That's it!** You now have automated visual regression testing for your TYPO3 site. 🎉
