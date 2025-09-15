<?php
/**
 * Laravel cPanel Deployment Script
 * Run this script to prepare your Laravel project for cPanel deployment
 */

echo "🚀 Preparing Laravel project for cPanel deployment...\n\n";

// 1. Create public_html structure
echo "📁 Creating public_html structure...\n";
if (!is_dir('public_html')) {
    mkdir('public_html', 0755, true);
}

// 2. Copy public files to public_html
echo "📋 Copying public files...\n";
$publicFiles = [
    'public/index.php',
    'public/.htaccess',
    'public/assets',
    'public/css',
    'public/js',
    'public/images',
    'public/favicon.ico'
];

foreach ($publicFiles as $file) {
    if (file_exists($file)) {
        $dest = 'public_html/' . basename($file);
        if (is_dir($file)) {
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            copyDirectory($file, $dest);
        } else {
            copy($file, $dest);
        }
        echo "✅ Copied: $file\n";
    }
}

// 3. Update index.php for cPanel
echo "🔧 Updating index.php for cPanel...\n";
$indexContent = file_get_contents('public/index.php');
$indexContent = str_replace(
    "require __DIR__.'/../vendor/autoload.php';",
    "require __DIR__.'/../vendor/autoload.php';",
    $indexContent
);
$indexContent = str_replace(
    "\$app = require_once __DIR__.'/../bootstrap/app.php';",
    "\$app = require_once __DIR__.'/../bootstrap/app.php';",
    $indexContent
);
file_put_contents('public_html/index.php', $indexContent);

// 4. Create .htaccess for cPanel
echo "📝 Creating .htaccess for cPanel...\n";
$htaccessContent = 'RewriteEngine On

# Handle Angular and other SPA routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>';

file_put_contents('public_html/.htaccess', $htaccessContent);

// 5. Create deployment instructions
echo "📋 Creating deployment instructions...\n";
$instructions = '# cPanel Deployment Instructions

## Files to Upload to cPanel

### 1. Upload to public_html/
- public_html/index.php
- public_html/.htaccess
- public_html/assets/ (entire folder)
- public_html/css/ (entire folder)
- public_html/js/ (entire folder)
- public_html/images/ (entire folder)
- public_html/favicon.ico

### 2. Upload to root directory (outside public_html)
- app/ (entire folder)
- bootstrap/ (entire folder)
- config/ (entire folder)
- database/ (entire folder)
- resources/ (entire folder)
- routes/ (entire folder)
- storage/ (entire folder)
- vendor/ (entire folder)
- .env.production (rename to .env)
- artisan
- composer.json
- composer.lock

### 3. Set Permissions
- storage/ → 755
- bootstrap/cache/ → 755
- public_html/ → 755

### 4. Update .env
- Change APP_URL to your domain
- Update database credentials for Supabase
- Set APP_ENV=production
- Set APP_DEBUG=false

### 5. Run Database Migrations
- Use cPanel\'s Terminal or PHPMyAdmin
- Run: php artisan migrate --force

### 6. Generate Application Key
- Run: php artisan key:generate

### 7. Clear Caches
- Run: php artisan config:clear
- Run: php artisan cache:clear
- Run: php artisan view:clear

## Important Notes
- Make sure PHP version is 8.1 or higher
- Enable PostgreSQL extension in cPanel
- Set memory_limit to at least 256M
- Set max_execution_time to at least 300 seconds
';

file_put_contents('CPANEL_DEPLOYMENT_INSTRUCTIONS.txt', $instructions);

echo "\n✅ Deployment preparation complete!\n";
echo "📁 Check the 'public_html' folder for files to upload to cPanel\n";
echo "📋 Read 'CPANEL_DEPLOYMENT_INSTRUCTIONS.txt' for detailed steps\n";

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
