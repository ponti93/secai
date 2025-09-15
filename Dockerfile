# Use nginx-php-fpm base image (Render's recommended approach)
FROM nginx:alpine

# Install PHP and required extensions
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-pdo \
    php82-pdo_pgsql \
    php82-mbstring \
    php82-tokenizer \
    php82-xml \
    php82-ctype \
    php82-json \
    php82-bcmath \
    php82-gd \
    php82-zip \
    php82-curl \
    php82-openssl \
    php82-pcntl \
    php82-posix \
    php82-fileinfo \
    php82-dom \
    php82-simplexml \
    php82-xmlreader \
    php82-xmlwriter \
    php82-phar \
    php82-opcache \
    php82-session \
    php82-filter \
    php82-hash \
    php82-iconv \
    php82-intl \
    php82-json \
    php82-mysqli \
    php82-pdo_mysql \
    php82-pdo_sqlite \
    php82-sqlite3 \
    php82-zlib \
    composer \
    netcat-openbsd

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configure NGINX
RUN echo 'server {\n\
    listen 80;\n\
    server_name localhost;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
\n\
    # Security headers\n\
    add_header X-Frame-Options "SAMEORIGIN" always;\n\
    add_header X-XSS-Protection "1; mode=block" always;\n\
    add_header X-Content-Type-Options "nosniff" always;\n\
    add_header Referrer-Policy "no-referrer-when-downgrade" always;\n\
    add_header Content-Security-Policy "default-src '\''self'\'' http: https: data: blob: '\''unsafe-inline'\''" always;\n\
\n\
    # Gzip compression\n\
    gzip on;\n\
    gzip_vary on;\n\
    gzip_min_length 1024;\n\
    gzip_proxied expired no-cache no-store private must-revalidate auth;\n\
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;\n\
\n\
    # Handle Laravel routes\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    # Handle PHP files\n\
    location ~ \.php$ {\n\
        fastcgi_pass unix:/var/run/php-fpm.sock;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
        fastcgi_hide_header X-Powered-By;\n\
    }\n\
\n\
    # Deny access to hidden files\n\
    location ~ /\. {\n\
        deny all;\n\
    }\n\
\n\
    # Cache static assets\n\
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {\n\
        expires 1y;\n\
        add_header Cache-Control "public, immutable";\n\
    }\n\
\n\
    # Security: deny access to sensitive files\n\
    location ~ /\.(htaccess|htpasswd|env) {\n\
        deny all;\n\
    }\n\
\n\
    # Deny access to storage and bootstrap cache\n\
    location ~ ^/(storage|bootstrap/cache) {\n\
        deny all;\n\
    }\n\
}' > /etc/nginx/conf.d/default.conf

# Copy application code
COPY . .

# Set permissions
RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure PHP-FPM
RUN echo '[www]\n\
user = nginx\n\
group = nginx\n\
listen = /var/run/php-fpm.sock\n\
listen.owner = nginx\n\
listen.group = nginx\n\
listen.mode = 0660\n\
pm = dynamic\n\
pm.max_children = 5\n\
pm.start_servers = 2\n\
pm.min_spare_servers = 1\n\
pm.max_spare_servers = 3' > /etc/php82/php-fpm.d/www.conf

# Create startup script
RUN echo '#!/bin/bash\n\
# Start PHP-FPM in background\n\
php-fpm82 -D\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database..."\n\
while ! nc -z $DB_HOST $DB_PORT; do\n\
  sleep 1\n\
done\n\
echo "Database is ready!"\n\
\n\
# Create .env file from .env.example if it doesn\'t exist\n\
if [ ! -f .env ]; then\n\
  cp .env.example .env\n\
fi\n\
\n\
# Generate application key if not set\n\
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then\n\
  php82 artisan key:generate --force\n\
fi\n\
\n\
# Run database migrations\n\
php82 artisan migrate --force\n\
\n\
# Cache configuration\n\
php82 artisan config:cache\n\
php82 artisan route:cache\n\
php82 artisan view:cache\n\
\n\
# Start NGINX\n\
nginx -g "daemon off;"' > /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Expose port
EXPOSE 80

# Start the application
CMD ["/usr/local/bin/start.sh"]
