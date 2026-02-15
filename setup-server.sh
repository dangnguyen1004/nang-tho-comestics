#!/bin/bash

# Script tự động setup server cho Nang Tho Cosmetics
# Chạy script này trên VPS sau khi đã SSH vào

set -e  # Exit on error

echo "🚀 Bắt đầu setup server cho Nang Tho Cosmetics..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}❌ Không nên chạy script này với quyền root. Hãy chạy với user thường.${NC}"
   exit 1
fi

# 1. Tạo thư mục dự án
PROJECT_DIR="/opt/nang-tho-cosmetics"
echo -e "${YELLOW}📁 Tạo thư mục dự án tại $PROJECT_DIR...${NC}"

if [ ! -d "$PROJECT_DIR" ]; then
    sudo mkdir -p "$PROJECT_DIR"
    sudo chown $USER:$USER "$PROJECT_DIR"
    echo -e "${GREEN}✅ Đã tạo thư mục $PROJECT_DIR${NC}"
else
    echo -e "${YELLOW}⚠️  Thư mục $PROJECT_DIR đã tồn tại${NC}"
fi

# 2. Kiểm tra Docker
echo -e "${YELLOW}🐳 Kiểm tra Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}Docker chưa được cài đặt. Bắt đầu cài đặt...${NC}"
    
    # Update system
    sudo apt update && sudo apt upgrade -y
    
    # Install dependencies
    sudo apt install -y ca-certificates curl gnupg lsb-release
    
    # Add Docker's official GPG key
    sudo mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    # Set up Docker repository
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
      https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Install Docker
    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Add user to docker group
    sudo usermod -aG docker $USER
    echo -e "${GREEN}✅ Đã cài đặt Docker${NC}"
    echo -e "${YELLOW}⚠️  Bạn cần đăng xuất và đăng nhập lại để áp dụng thay đổi docker group${NC}"
else
    echo -e "${GREEN}✅ Docker đã được cài đặt${NC}"
fi

# 3. Kiểm tra Composer
echo -e "${YELLOW}📦 Kiểm tra Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer chưa được cài đặt. Bắt đầu cài đặt...${NC}"
    
    cd /tmp
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
    echo -e "${GREEN}✅ Đã cài đặt Composer${NC}"
else
    echo -e "${GREEN}✅ Composer đã được cài đặt${NC}"
fi

# 4. Hướng dẫn clone repo
echo ""
echo -e "${GREEN}✅ Setup cơ bản hoàn tất!${NC}"
echo ""
echo -e "${YELLOW}📋 Các bước tiếp theo:${NC}"
echo ""
echo "1. Clone repository vào $PROJECT_DIR:"
echo "   cd $PROJECT_DIR"
echo "   git clone <your-repo-url> ."
echo ""
echo "2. Cài đặt Composer dependencies:"
echo "   cd $PROJECT_DIR"
echo "   composer install"
echo ""
echo "3. Tạo file .env với mật khẩu bảo mật:"
echo "   cp .env.example .env"
echo "   nano .env  # Cập nhật mật khẩu"
echo ""
echo "4. Khởi động Docker Compose:"
echo "   docker compose up -d"
echo ""
echo "5. Kiểm tra logs:"
echo "   docker compose logs -f"
echo ""
echo -e "${GREEN}🎉 Hoàn tất!${NC}"
