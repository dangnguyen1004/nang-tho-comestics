# 🌊 Deploy lên DigitalOcean

DigitalOcean có nhiều options: Droplet (VPS), App Platform, hoặc Managed WordPress.

---

## Option 1: DigitalOcean Droplet (VPS) - Recommended

### Chi phí: $6-12/tháng

### Bước 1: Tạo Droplet

1. Đăng nhập [cloud.digitalocean.com](https://cloud.digitalocean.com)
2. Click **"Create" → "Droplets"**
3. Chọn cấu hình:
   - **Image**: Ubuntu 22.04 LTS
   - **Plan**: Basic $6/mo (1GB RAM) hoặc $12/mo (2GB RAM)
   - **Region**: Singapore (gần Việt Nam nhất)
   - **Authentication**: SSH Key (recommended)

### Bước 2: SSH vào Droplet

```bash
ssh root@your-droplet-ip
```

### Bước 3: Cài đặt Docker

```bash
# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version
```

### Bước 4: Clone và Deploy

```bash
# Clone repository
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git
cd nang-tho-comestics

# Start containers
docker compose up -d

# Check status
docker compose ps
```

### Bước 5: Cấu hình Firewall

```bash
# Allow HTTP và HTTPS
ufw allow 80
ufw allow 443
ufw allow 22
ufw enable
```

### Bước 6: Setup Domain (Optional)

1. Trong DigitalOcean, vào **Networking → Domains**
2. Add domain của bạn
3. Tạo A record trỏ về Droplet IP

### Bước 7: Setup SSL với Let's Encrypt

```bash
# Cài đặt Certbot
apt install certbot python3-certbot-nginx -y

# Hoặc dùng Docker với nginx-proxy (xem file docker-compose.prod.yml)
```

---

## Option 2: DigitalOcean App Platform

### Chi phí: $12/tháng

### Bước 1: Tạo App

1. Vào **Apps → Create App**
2. Chọn **GitHub** và repository của bạn
3. App Platform sẽ detect Docker

### Bước 2: Add Database

1. Click **"Add Resource"**
2. Chọn **"Database" → "Dev Database"** (free) hoặc **Managed Database**

### Bước 3: Configure Environment

```yaml
WORDPRESS_DB_HOST: ${db.HOSTNAME}
WORDPRESS_DB_USER: ${db.USERNAME}
WORDPRESS_DB_PASSWORD: ${db.PASSWORD}
WORDPRESS_DB_NAME: ${db.DATABASE}
```

### Bước 4: Deploy

- Click **"Deploy"**
- Chờ build và deploy hoàn thành

---

## Option 3: DigitalOcean 1-Click WordPress

### Nếu không cần Docker

1. **Create → Droplets → Marketplace**
2. Search **"WordPress"**
3. Chọn **"WordPress on Ubuntu"**
4. Deploy

Sau đó:
```bash
# SSH vào server
ssh root@your-ip

# Copy theme vào
cd /var/www/html/wp-content/themes/
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git temp
mv temp/wp-content/themes/nang-tho-cosmetics ./
rm -rf temp

# Set permissions
chown -R www-data:www-data nang-tho-cosmetics
```

---

## 🔧 Production Docker Compose

Xem file `docker-compose.prod.yml` để deploy với:
- Nginx reverse proxy
- SSL/HTTPS tự động
- Production optimizations

---

## 💡 Tips

1. **Backup**: Setup DigitalOcean Spaces để backup database
2. **Monitoring**: Enable Droplet monitoring trong dashboard
3. **Scaling**: Có thể resize Droplet khi cần
4. **CDN**: Dùng DigitalOcean Spaces CDN cho static assets

---

**Estimated setup time**: 20-30 phút
