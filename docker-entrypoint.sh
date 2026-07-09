#!/bin/bash
set -e

echo "🍛 Sego Sambelan — Starting deployment..."

# Ensure SQLite database exists
mkdir -p /app/database
touch /app/database/database.sqlite
chmod 664 /app/database/database.sqlite

# Ensure storage directories exist
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/app/public
mkdir -p /app/storage/logs

# Set open permissions for storage & sqlite database
chmod -R 777 /app/storage /app/bootstrap/cache /app/database

# Create production .env if it does not exist
if [ ! -f /app/.env ]; then
    echo "📝 Creating /app/.env with defaults for SQLite..."
    cat << 'EOF' > /app/.env
APP_NAME="Sego Sambelan"
APP_ENV=production
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stderr
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_STORE=file
FILESYSTEM_DISK=public
EOF
fi

# Ensure APP_KEY is generated and exported
ENV_KEY=$(grep -E '^APP_KEY=base64:' /app/.env | cut -d '=' -f2- || true)
if [ -z "$ENV_KEY" ]; then
    echo "🔑 Generating fresh APP_KEY..."
    KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" /app/.env
    export APP_KEY="$KEY"
else
    export APP_KEY="$ENV_KEY"
fi

# Clear & rebuild caches for production
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Seed database
echo "🌱 Seeding database..."
php artisan db:seed --force

# Create storage symlink
php artisan storage:link || true

echo "✅ Ready! Starting server on port ${PORT:-8080}..."

# Start Laravel's built-in server
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
