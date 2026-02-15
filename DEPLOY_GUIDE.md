# Hướng dẫn Deploy lên VPS

## 1. Tạo thư mục dự án (KHÔNG clone vào root)

```bash
# Tạo thư mục cho dự án
sudo mkdir -p /opt/nang-tho-cosmetics
sudo chown $USER:$USER /opt/nang-tho-cosmetics
cd /opt/nang-tho-cosmetics

# Hoặc nếu muốn dùng thư mục home
mkdir -p ~/projects/nang-tho-cosmetics
cd ~/projects/nang-tho-cosmetics
```

## 2. Clone repository

```bash
# Clone repo vào thư mục vừa tạo
git clone <your-repo-url> .

# Hoặc nếu repo đã có sẵn
git clone <your-repo-url> /opt/nang-tho-cosmetics
cd /opt/nang-tho-cosmetics
```

## 3. Cài đặt Docker và Docker Compose

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Cài đặt dependencies
sudo apt install -y ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Set up Docker repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Add user to docker group (để chạy docker không cần sudo)
sudo usermod -aG docker $USER
newgrp docker

# Verify installation
docker --version
docker compose version
```

## 4. Cài đặt Composer (cho SePay SDK)

```bash
# Download Composer
cd /opt/nang-tho-cosmetics
curl -sS https://getcomposer.org/installer | php

# Move Composer to global location
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify
composer --version
```

## 5. Cài đặt SePay SDK dependencies

```bash
cd /opt/nang-tho-cosmetics
composer install
```

## 6. Cấu hình Docker Compose

### Cập nhật docker-compose.yml cho production:

```yaml
version: '3.8'

services:
  db:
    image: mysql:8.0
    container_name: nangtho_db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-your_secure_password}
      MYSQL_DATABASE: nangtho_cosmetics
      MYSQL_USER: ${DB_USER:-wp_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-your_secure_password}
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - nangtho_network

  wordpress:
    image: wordpress:latest
    container_name: nangtho_wp
    restart: always
    ports:
      - "80:80"  # Thay đổi từ 8080 sang 80 cho production
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: ${DB_USER:-wp_user}
      WORDPRESS_DB_PASSWORD: ${DB_PASSWORD:-your_secure_password}
      WORDPRESS_DB_NAME: nangtho_cosmetics
    volumes:
      - wp_data:/var/www/html
      - ./wp-content/themes/nang-tho-cosmetics:/var/www/html/wp-content/themes/nang-tho-cosmetics
      - ./vendor:/var/www/html/vendor
      - ./composer.json:/var/www/html/composer.json
    depends_on:
      - db
    networks:
      - nangtho_network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: nangtho_pma
    restart: always
    ports:
      - "8081:80"  # Giữ port 8081 cho phpMyAdmin
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-your_secure_password}
    depends_on:
      - db
    networks:
      - nangtho_network

volumes:
  db_data:
  wp_data:

networks:
  nangtho_network:
    driver: bridge
```

## 7. Tạo file .env cho bảo mật

```bash
cd /opt/nang-tho-cosmetics
cat > .env << EOF
# Database Configuration
DB_ROOT_PASSWORD=your_very_secure_root_password_here
DB_USER=wp_user
DB_PASSWORD=your_very_secure_db_password_here

# WordPress Configuration
WORDPRESS_DEBUG=false
EOF

chmod 600 .env
```

## 8. Khởi động Docker Compose

```bash
cd /opt/nang-tho-cosmetics

# Build và start containers
docker compose up -d

# Xem logs
docker compose logs -f

# Kiểm tra containers đang chạy
docker compose ps
```

## 9. Cấu hình Firewall (UFW)

```bash
# Cài đặt UFW nếu chưa có
sudo apt install -y ufw

# Cho phép SSH
sudo ufw allow 22/tcp

# Cho phép HTTP
sudo ufw allow 80/tcp

# Cho phép HTTPS (nếu có SSL)
sudo ufw allow 443/tcp

# Cho phép phpMyAdmin (chỉ từ IP cụ thể - tùy chọn)
# sudo ufw allow from YOUR_IP_ADDRESS to any port 8081

# Kích hoạt firewall
sudo ufw enable
sudo ufw status
```

## 10. Cấu hình Domain và SSL (tùy chọn)

Nếu bạn có domain:

```bash
# Cài đặt Nginx reverse proxy
sudo apt install -y nginx

# Cấu hình Nginx (sẽ cần tạo config file)
# Sau đó cài SSL với Let's Encrypt
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

## 11. Backup và Maintenance

### Backup database:
```bash
docker exec nangtho_db mysqldump -u wp_user -p nangtho_cosmetics > backup_$(date +%Y%m%d).sql
```

### Backup WordPress files:
```bash
docker exec nangtho_wp tar -czf /tmp/wp-backup.tar.gz /var/www/html
docker cp nangtho_wp:/tmp/wp-backup.tar.gz ./wp-backup-$(date +%Y%m%d).tar.gz
```

## 12. Monitoring

```bash
# Xem resource usage
docker stats

# Xem logs
docker compose logs wordpress
docker compose logs db

# Restart services
docker compose restart

# Stop services
docker compose stop

# Start services
docker compose start
```

## Troubleshooting

### Nếu port 80 đã được sử dụng:
```bash
# Kiểm tra process đang dùng port 80
sudo lsof -i :80

# Hoặc dùng netstat
sudo netstat -tulpn | grep :80
```

### Nếu gặp lỗi permission:
```bash
# Fix permissions cho Docker volumes
sudo chown -R www-data:www-data wp_data
```

### Nếu cần rebuild:
```bash
docker compose down
docker compose up -d --build
```
