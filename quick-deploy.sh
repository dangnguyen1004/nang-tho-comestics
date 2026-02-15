#!/bin/bash

# Script deploy nhanh - chạy sau khi đã clone repo và có sẵn Docker

set -e

PROJECT_DIR="/opt/nang-tho-cosmetics"
cd "$PROJECT_DIR"

echo "🚀 Quick deploy..."

# Cài dependencies
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Tạo .env nếu chưa có
if [ ! -f ".env" ]; then
    echo "⚙️ Creating .env file..."
    DB_ROOT_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    
    cat > .env << EOF
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
DB_USER=wp_user
DB_PASSWORD=${DB_PASSWORD}
WORDPRESS_PORT=80
WORDPRESS_DEBUG=false
PHPMYADMIN_PORT=8081
EOF
    
    chmod 600 .env
    echo "✅ Created .env with random passwords"
    echo "DB Root: ${DB_ROOT_PASSWORD}"
    echo "DB Pass: ${DB_PASSWORD}"
fi

# Deploy
echo "🐳 Starting Docker containers..."
docker compose down 2>/dev/null || true

if [ -f "docker-compose.prod.yml" ]; then
    docker compose -f docker-compose.prod.yml up -d
else
    docker compose up -d
fi

sleep 5
docker compose ps

echo ""
echo "✅ Deploy complete!"
echo "WordPress: http://94.237.68.240"
echo "phpMyAdmin: http://94.237.68.240:8081"
