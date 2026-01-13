# 🚀 Hướng Dẫn Deploy Nàng Thơ Cosmetics

## So Sánh Các Platform

| Platform | Chi phí | Độ khó | SSL | MySQL | Recommend |
|----------|---------|--------|-----|-------|-----------|
| **Railway** | $5-20/mo | ⭐ Dễ | ✅ Auto | ✅ Native | ⭐⭐⭐ Best cho beginners |
| **DigitalOcean Droplet** | $6-12/mo | ⭐⭐ Trung bình | Manual | ✅ Native | ⭐⭐⭐ Best value |
| **DigitalOcean App** | $12+/mo | ⭐ Dễ | ✅ Auto | ✅ Managed | Good |
| **Render** | $7+/mo | ⭐ Dễ | ✅ Auto | ❌ External | Not ideal |
| **AWS Lightsail** | $5-10/mo | ⭐⭐ Trung bình | Manual | ✅ Native | Good |
| **Vercel/Netlify** | Free-$20 | ⭐ Dễ | ✅ Auto | ❌ No | ❌ Not for WP |

## 🏆 Recommended Options

### 1. Cho Beginners: Railway
- Setup nhanh trong 10 phút
- Không cần kiến thức server
- Auto SSL, auto deploy
- [📖 Xem hướng dẫn Railway](./DEPLOY-RAILWAY.md)

### 2. Cho Production: DigitalOcean Droplet
- Full control
- Chi phí thấp ($6/mo)
- Performance tốt
- [📖 Xem hướng dẫn DigitalOcean](./DEPLOY-DIGITALOCEAN.md)

### 3. Cho Enterprise: AWS/GCP
- Scalable
- High availability
- Cần kiến thức DevOps

---

## Quick Start Guide

### Option 1: Local Development

```bash
# Clone repo
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git
cd nang-tho-comestics

# Start with Docker
docker-compose up -d

# Access
open http://localhost:8080
```

### Option 2: Railway (Fastest)

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login & Init
railway login
railway init

# Add MySQL
railway add --database mysql

# Deploy
railway up
```

### Option 3: DigitalOcean Droplet

```bash
# SSH to your droplet
ssh root@your-droplet-ip

# Install Docker
curl -fsSL https://get.docker.com | sh

# Clone & Deploy
git clone https://github.com/dangnguyen1004/nang-tho-comestics.git
cd nang-tho-comestics
docker compose up -d
```

---

## 🔐 Production Checklist

### Security
- [ ] Change default passwords in `.env`
- [ ] Enable firewall (ufw)
- [ ] Setup SSL/HTTPS
- [ ] Disable WordPress debug mode
- [ ] Remove phpMyAdmin or restrict access

### Performance
- [ ] Enable Redis cache
- [ ] Setup CDN for static assets
- [ ] Enable GZIP compression
- [ ] Optimize images

### Backup
- [ ] Setup automated database backup
- [ ] Backup wp-content folder
- [ ] Test restore process

### Monitoring
- [ ] Setup uptime monitoring
- [ ] Enable error logging
- [ ] Setup alerts

---

## 📁 Files Structure

```
deploy/
├── DEPLOY-GUIDE.md          # This file
├── DEPLOY-RAILWAY.md        # Railway instructions
├── DEPLOY-DIGITALOCEAN.md   # DigitalOcean instructions
└── DEPLOY-RENDER.md         # Render instructions

Root/
├── docker-compose.yml       # Development setup
├── docker-compose.prod.yml  # Production setup with SSL
├── .env.example             # Environment template
└── .github/workflows/
    └── deploy.yml           # CI/CD pipeline
```

---

## 🆘 Need Help?

1. Check logs: `docker compose logs -f`
2. Restart services: `docker compose restart`
3. Reset everything: `docker compose down -v && docker compose up -d`

---

**Happy Deploying! 🎉**
