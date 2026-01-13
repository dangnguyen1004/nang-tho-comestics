# Nàng Thơ Cosmetics - WordPress Theme

Theme WordPress/WooCommerce cho website bán mỹ phẩm Nàng Thơ Cosmetics.

## 🚀 Cài đặt & Triển khai

### Yêu cầu hệ thống

- Docker & Docker Compose
- Git

### Cách deploy với Docker

1. **Clone repository**

```bash
git clone <repository-url>
cd workspace
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
- Chọn theme `nang-tho-cosmetics` trong Appearance > Themes
- Cài đặt và kích hoạt plugin WooCommerce

### Cấu hình Database

| Thông số | Giá trị |
|----------|---------|
| Host | db |
| Database | nangtho_cosmetics |
| User | user |
| Password | password |

## 📁 Cấu trúc Project

```
├── docker-compose.yml          # Docker configuration
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

## 🎨 Theme Features

- **Homepage**: Hero banner, Flash sale, Best sellers, Categories, Brands
- **Shop page**: Product grid với filters, sidebar
- **Product detail**: Image gallery, product info, reviews, related products
- **Cart & Checkout**: Vietnamese payment gateway integration
- **Responsive design**: Mobile-first approach

## 🛠️ Development

### Dừng containers

```bash
docker-compose down
```

### Xem logs

```bash
docker-compose logs -f wordpress
```

### Rebuild containers

```bash
docker-compose up -d --build
```

### Xóa tất cả data (reset)

```bash
docker-compose down -v
```

## 📝 Notes

- Theme tự động mount vào WordPress container
- Thay đổi code trong `wp-content/themes/nang-tho-cosmetics/` sẽ reflect ngay lập tức
- Database data được persist trong Docker volume `db_data`

## 🔗 External Deployment

### Deploy lên hosting

1. Export database từ phpMyAdmin
2. Upload theme folder lên hosting
3. Import database và update `wp_options` table với domain mới
4. Activate theme và configure settings

### Deploy lên VPS với Docker

```bash
# SSH vào VPS
ssh user@your-vps-ip

# Clone và deploy
git clone <repository-url>
cd workspace
docker-compose up -d
```

---

**Author**: Antigravity  
**License**: ISC
