#!/bin/bash
# Deploy latest code to production
# Usage: bash /opt/nang-tho-cosmetics/scripts/deploy.sh

set -e
cd /opt/nang-tho-cosmetics

echo "==> Pulling latest code..."
git pull origin main

echo "==> Installing composer dependencies..."
docker run --rm -v "$(pwd)":/app composer:latest install --no-dev --no-interaction --quiet

echo "==> Restarting Nginx to pick up any config changes..."
docker exec nangtho_nginx nginx -s reload 2>/dev/null || true

echo "==> Flushing WP cache..."
docker exec nangtho_wp wp cache flush --allow-root 2>/dev/null || true

echo "==> Verifying site..."
HTTP_CODE=$(curl -s -o /dev/null -w '%{http_code}' https://nangthocomestic.io.vn/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "Deploy successful! Site returning HTTP $HTTP_CODE"
else
    echo "WARNING: Site returning HTTP $HTTP_CODE — check logs with: docker logs nangtho_wp --tail 20"
    exit 1
fi
