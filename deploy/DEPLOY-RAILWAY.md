# 🚂 Deploy lên Railway

Railway là platform dễ sử dụng nhất cho WordPress với Docker.

## Bước 1: Tạo tài khoản Railway

1. Truy cập [railway.app](https://railway.app)
2. Đăng ký bằng GitHub account

## Bước 2: Tạo Project mới

### Option A: Deploy từ GitHub (Recommended)

1. Click **"New Project"**
2. Chọn **"Deploy from GitHub repo"**
3. Chọn repository `nang-tho-comestics`
4. Railway sẽ tự detect `docker-compose.yml`

### Option B: Deploy bằng Railway CLI

```bash
# Cài đặt Railway CLI
npm install -g @railway/cli

# Login
railway login

# Tạo project mới
railway init

# Deploy
railway up
```

## Bước 3: Thêm MySQL Database

1. Trong Railway dashboard, click **"+ New"**
2. Chọn **"Database" → "MySQL"**
3. Railway sẽ tự động tạo database

## Bước 4: Cấu hình Environment Variables

Trong Railway dashboard → Service Settings → Variables:

```env
WORDPRESS_DB_HOST=${{MySQL.MYSQL_HOST}}
WORDPRESS_DB_USER=${{MySQL.MYSQL_USER}}
WORDPRESS_DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
WORDPRESS_DB_NAME=${{MySQL.MYSQL_DATABASE}}
```

## Bước 5: Expose Port

1. Vào **Settings** của WordPress service
2. Trong **Networking**, click **"Generate Domain"**
3. Railway sẽ cấp domain dạng: `your-app.up.railway.app`

## Bước 6: Hoàn thành cài đặt

1. Truy cập domain được cấp
2. Hoàn thành WordPress setup wizard
3. Vào **Appearance → Themes** và activate `nang-tho-cosmetics`
4. Cài đặt WooCommerce plugin

## 💰 Chi phí

- **Free tier**: $5 credit/tháng (đủ để test)
- **Pro**: $20/tháng (unlimited usage)

## 🔧 Troubleshooting

### Database connection error
- Kiểm tra environment variables đã đúng chưa
- Đảm bảo MySQL service đang chạy

### Theme không hiển thị
- Kiểm tra volume mount trong docker-compose
- Restart WordPress service

## 📝 Notes

- Railway tự động SSL/HTTPS
- Tự động deploy khi push code mới
- Có thể add custom domain

---

**Estimated setup time**: 10-15 phút
