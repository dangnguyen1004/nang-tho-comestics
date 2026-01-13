# 🎨 Deploy lên Render

Render là platform tương tự Railway, dễ dùng và có free tier.

## Bước 1: Tạo tài khoản

1. Truy cập [render.com](https://render.com)
2. Đăng ký bằng GitHub account

## Bước 2: Tạo PostgreSQL Database

> Note: Render không hỗ trợ MySQL, nhưng WordPress có thể dùng plugin để work với PostgreSQL, hoặc dùng external MySQL.

### Option A: Dùng External MySQL (PlanetScale/TiDB)

1. Tạo free MySQL tại [planetscale.com](https://planetscale.com)
2. Lấy connection string

### Option B: Dùng Render's managed database service

## Bước 3: Tạo Web Service

1. Dashboard → **"New" → "Web Service"**
2. Connect GitHub repository
3. Cấu hình:
   - **Name**: nang-tho-cosmetics
   - **Environment**: Docker
   - **Region**: Singapore
   - **Plan**: Free (limited) hoặc Starter ($7/mo)

## Bước 4: Environment Variables

Trong service settings, add:

```env
WORDPRESS_DB_HOST=your-mysql-host
WORDPRESS_DB_USER=your-user
WORDPRESS_DB_PASSWORD=your-password
WORDPRESS_DB_NAME=your-database
```

## Bước 5: Deploy

- Render tự động deploy khi push code
- Truy cập URL: `your-service.onrender.com`

## ⚠️ Limitations

- Free tier có cold start (spin down sau 15 phút inactive)
- Không có MySQL native (cần external service)
- Disk storage không persistent trên free tier

## 💰 Chi phí

- **Free**: Limited, với cold starts
- **Starter**: $7/tháng per service

---

**Verdict**: Railway tốt hơn cho WordPress vì có MySQL native.
