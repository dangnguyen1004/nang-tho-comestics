# Nàng Thơ Cosmetics - WordPress Theme

Theme WordPress/WooCommerce cho website bán mỹ phẩm Nàng Thơ Cosmetics.

## 🚀 Quick Start

```bash
# Clone repository
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git
cd nang-tho-comestics

# Start với Docker
docker-compose up -d

# Truy cập: http://localhost:8080
```

## 📚 Deployment Guides

| Platform | Chi phí | Độ khó | Hướng dẫn |
|----------|---------|--------|-----------|
| 🚂 **Railway** | $5-20/mo | ⭐ Dễ | [📖 Xem hướng dẫn](./deploy/DEPLOY-RAILWAY.md) |
| 🌊 **DigitalOcean** | $6-12/mo | ⭐⭐ Trung bình | [📖 Xem hướng dẫn](./deploy/DEPLOY-DIGITALOCEAN.md) |
| 🎨 **Render** | $7+/mo | ⭐ Dễ | [📖 Xem hướng dẫn](./deploy/DEPLOY-RENDER.md) |
| 🏠 **Local Docker** | Free | ⭐ Dễ | [📖 Xem bên dưới](#local-development) |

👉 **[Xem hướng dẫn deploy đầy đủ](./deploy/DEPLOY-GUIDE.md)**

---

## 🏠 Local Development

### Yêu cầu

- Docker & Docker Compose
- Git

### Cài đặt

1. **Clone repository**

```bash
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git
cd nang-tho-comestics
```

2. **Khởi động Docker containers**

```bash
docker-compose up -d
```

3. **Truy cập các services**

| Service | URL | Mô tả |
|---------|-----|-------|
| WordPress | http://localhost:8080 | Website chính |
| phpMyAdmin | http://localhost:8081 | Quản lý database |

4. **Cài đặt WordPress**

- Truy cập http://localhost:8080
- Hoàn thành wizard cài đặt WordPress
- Vào **Appearance → Themes** và activate `nang-tho-cosmetics`
- Cài đặt plugin **WooCommerce**

### Database Info

| Thông số | Giá trị |
|----------|---------|
| Host | db |
| Database | nangtho_cosmetics |
| User | user |
| Password | password |

---

## 📁 Project Structure

```
├── docker-compose.yml          # Development Docker config
├── docker-compose.prod.yml     # Production Docker config (with SSL)
├── .env.example                # Environment variables template
├── .github/workflows/
│   └── deploy.yml              # CI/CD pipeline
├── deploy/                     # Deployment guides
│   ├── DEPLOY-GUIDE.md
│   ├── DEPLOY-RAILWAY.md
│   ├── DEPLOY-DIGITALOCEAN.md
│   └── DEPLOY-RENDER.md
├── wp-content/
│   └── themes/
│       └── nang-tho-cosmetics/ # Theme chính
│           ├── assets/         # CSS, JS files
│           ├── includes/       # PHP classes
│           ├── template-parts/ # Template components
│           ├── woocommerce/    # WooCommerce templates
│           ├── functions.php   # Theme functions
│           ├── style.css       # Theme stylesheet
│           └── ...
└── _reference/                 # Design references
```

---

## 🎨 Theme Features

### Pages
- **Homepage**: Hero banner, Flash sale, Best sellers, Categories, Brands
- **Shop page**: Product grid với filters, sidebar
- **Product detail**: Image gallery, product info, reviews, related products
- **Cart & Checkout**: Vietnamese payment gateway integration

### Technical
- ✅ Responsive design (Mobile-first)
- ✅ WooCommerce integration
- ✅ Vietnamese payment gateways
- ✅ SEO optimized
- ✅ Performance optimized

---

## 🛠️ Development Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f wordpress

# Rebuild containers
docker-compose up -d --build

# Reset everything (delete all data)
docker-compose down -v
```

---

## 🚀 Production Deployment

### Using Production Docker Compose

```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env

# Start production stack
docker-compose -f docker-compose.prod.yml up -d
```

### CI/CD with GitHub Actions

Repository đã được setup với GitHub Actions workflow:
- Auto validate theme files khi push
- Auto build theme package
- Auto deploy to server via SSH

Xem `.github/workflows/deploy.yml` để biết thêm chi tiết.

### Required Secrets (for CI/CD)

Trong GitHub repository settings → Secrets:

```
SSH_HOST=your-server-ip
SSH_USER=root
SSH_PRIVATE_KEY=your-ssh-key
WP_PATH=/var/www/html
```

---

## 📝 Notes

- Theme tự động mount vào WordPress container
- Thay đổi code trong `wp-content/themes/nang-tho-cosmetics/` reflect ngay lập tức
- Database data persist trong Docker volume `db_data`

---

## 🆘 Troubleshooting

### Container không start
```bash
docker-compose logs db
docker-compose logs wordpress
```

### Permission errors
```bash
sudo chown -R www-data:www-data wp-content/themes/nang-tho-cosmetics
```

### Reset database
```bash
docker-compose down -v
docker-compose up -d
```

---

**Author**: Antigravity  
**License**: ISC
