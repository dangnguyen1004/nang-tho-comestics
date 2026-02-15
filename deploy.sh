#!/bin/bash

# Script tự động deploy Nang Tho Cosmetics lên VPS
# Chạy script này sau khi đã SSH vào server

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 Bắt đầu deploy Nang Tho Cosmetics...${NC}"

# Configuration
PROJECT_DIR="/opt/nang-tho-cosmetics"
REPO_URL="${1:-}"  # Nhận repo URL từ tham số đầu tiên

# 1. Tạo thư mục dự án
echo -e "${YELLOW}📁 Tạo thư mục dự án...${NC}"
mkdir -p "$PROJECT_DIR"
cd "$PROJECT_DIR"
echo -e "${GREEN}✅ Thư mục: $PROJECT_DIR${NC}"

# 2. Kiểm tra và cài đặt Docker
echo -e "${YELLOW}🐳 Kiểm tra Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}Đang cài đặt Docker...${NC}"
    
    # Update system
    apt update && apt upgrade -y
    
    # Install dependencies
    apt install -y ca-certificates curl gnupg lsb-release
    
    # Add Docker's official GPG key
    mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    # Set up Docker repository
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
      https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Install Docker
    apt update
    apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    echo -e "${GREEN}✅ Đã cài đặt Docker${NC}"
else
    echo -e "${GREEN}✅ Docker đã được cài đặt${NC}"
fi

# 3. Kiểm tra và cài đặt Composer
echo -e "${YELLOW}📦 Kiểm tra Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Đang cài đặt Composer...${NC}"
    cd /tmp
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    cd "$PROJECT_DIR"
    echo -e "${GREEN}✅ Đã cài đặt Composer${NC}"
else
    echo -e "${GREEN}✅ Composer đã được cài đặt${NC}"
fi

# 4. Clone repository (nếu chưa có)
if [ -d "$PROJECT_DIR/.git" ]; then
    echo -e "${YELLOW}📥 Repository đã tồn tại, đang pull latest changes...${NC}"
    cd "$PROJECT_DIR"
    git pull
else
    if [ -z "$REPO_URL" ]; then
        echo -e "${RED}❌ Chưa có repository. Vui lòng cung cấp repo URL:${NC}"
        echo -e "${YELLOW}   ./deploy.sh <your-repo-url>${NC}"
        echo -e "${YELLOW}   Hoặc clone thủ công: git clone <repo-url> $PROJECT_DIR${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}📥 Đang clone repository...${NC}"
    git clone "$REPO_URL" "$PROJECT_DIR"
    cd "$PROJECT_DIR"
fi

# 5. Cài đặt Composer dependencies
echo -e "${YELLOW}📦 Đang cài đặt Composer dependencies...${NC}"
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✅ Đã cài đặt dependencies${NC}"
else
    echo -e "${YELLOW}⚠️  Không tìm thấy composer.json${NC}"
fi

# 6. Tạo file .env nếu chưa có
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚙️  Tạo file .env...${NC}"
    
    # Generate random passwords
    DB_ROOT_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    
    cat > .env << EOF
# Database Configuration
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
DB_USER=wp_user
DB_PASSWORD=${DB_PASSWORD}

# WordPress Configuration
WORDPRESS_PORT=80
WORDPRESS_DEBUG=false

# phpMyAdmin Configuration
PHPMYADMIN_PORT=8081
EOF
    
    chmod 600 .env
    echo -e "${GREEN}✅ Đã tạo file .env với mật khẩu ngẫu nhiên${NC}"
    echo -e "${YELLOW}⚠️  Lưu lại mật khẩu này:${NC}"
    echo -e "${BLUE}   DB Root Password: ${DB_ROOT_PASSWORD}${NC}"
    echo -e "${BLUE}   DB Password: ${DB_PASSWORD}${NC}"
else
    echo -e "${GREEN}✅ File .env đã tồn tại${NC}"
fi

# 7. Dừng containers cũ (nếu có)
echo -e "${YELLOW}🛑 Dừng containers cũ (nếu có)...${NC}"
docker compose down 2>/dev/null || true

# 8. Khởi động Docker Compose
echo -e "${YELLOW}🚀 Khởi động Docker Compose...${NC}"

# Sử dụng docker-compose.prod.yml nếu có, nếu không dùng docker-compose.yml
if [ -f "docker-compose.prod.yml" ]; then
    docker compose -f docker-compose.prod.yml up -d
else
    docker compose up -d
fi

# 9. Đợi containers khởi động
echo -e "${YELLOW}⏳ Đợi containers khởi động...${NC}"
sleep 10

# 10. Kiểm tra trạng thái
echo -e "${YELLOW}📊 Kiểm tra trạng thái containers...${NC}"
docker compose ps

# 11. Hiển thị logs
echo -e "${YELLOW}📋 Logs gần đây:${NC}"
docker compose logs --tail=20

# 12. Cấu hình firewall (nếu cần)
echo -e "${YELLOW}🔥 Kiểm tra firewall...${NC}"
if command -v ufw &> /dev/null; then
    ufw allow 80/tcp 2>/dev/null || true
    ufw allow 443/tcp 2>/dev/null || true
    echo -e "${GREEN}✅ Firewall đã được cấu hình${NC}"
fi

# 13. Hiển thị thông tin
echo ""
echo -e "${GREEN}✅ Deploy hoàn tất!${NC}"
echo ""
echo -e "${BLUE}📌 Thông tin truy cập:${NC}"
echo -e "   WordPress: http://94.237.68.240"
echo -e "   phpMyAdmin: http://94.237.68.240:8081"
echo ""
echo -e "${YELLOW}📝 Các lệnh hữu ích:${NC}"
echo -e "   Xem logs: docker compose logs -f"
echo -e "   Xem trạng thái: docker compose ps"
echo -e "   Restart: docker compose restart"
echo -e "   Stop: docker compose stop"
echo -e "   Start: docker compose start"
echo ""
echo -e "${YELLOW}🔐 Mật khẩu database đã được lưu trong file .env${NC}"
echo -e "${YELLOW}   Xem mật khẩu: cat $PROJECT_DIR/.env${NC}"
