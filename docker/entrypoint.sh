#!/bin/sh

# Wait for database to be ready
echo "Waiting for database connection..."
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    if php artisan db:show --quiet 2>/dev/null; then
        echo "Database connection established!"
        break
    else
        echo "Attempt $attempt/$max_attempts: Database not ready, waiting..."
        sleep 2
        attempt=$((attempt + 1))
    fi
done

if [ $attempt -gt $max_attempts ]; then
    echo "ERROR: Could not connect to database after $max_attempts attempts"
    exit 1
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run database seeding
echo "Running database seeders..."
php artisan db:seed --force

# Clear and cache config
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Create log directories
mkdir -p /var/log/nginx

echo "Laravel application is ready!"

# Execute the main command
exec "$@"
