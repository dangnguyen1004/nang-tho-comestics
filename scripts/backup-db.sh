#!/bin/bash
# Daily database backup script for Nang Tho Cosmetics
# Usage: Run via cron or manually: bash /opt/nang-tho-cosmetics/scripts/backup-db.sh

BACKUP_DIR="/opt/nang-tho-cosmetics/backups"
KEEP_DAYS=7
DATE=$(date +%Y-%m-%d_%H%M)
BACKUP_FILE="$BACKUP_DIR/nangtho_db_$DATE.sql.gz"

mkdir -p "$BACKUP_DIR"

# Dump and compress
docker exec nangtho_db mariadb-dump -u root -p"$DB_ROOT_PASSWORD" nangtho_cosmetics 2>/dev/null | gzip > "$BACKUP_FILE"

if [ -s "$BACKUP_FILE" ]; then
    echo "Backup created: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
else
    echo "ERROR: Backup failed or empty" >&2
    rm -f "$BACKUP_FILE"
    exit 1
fi

# Remove backups older than KEEP_DAYS
find "$BACKUP_DIR" -name "nangtho_db_*.sql.gz" -mtime +$KEEP_DAYS -delete
echo "Cleaned up backups older than $KEEP_DAYS days"
