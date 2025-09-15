# 🚀 Quick Deploy to Render with PostgreSQL

## Prerequisites
- GitHub account
- Render account (free tier available)
- Your project code in a GitHub repository

## Step 1: Push to GitHub

```bash
git init
git add .
git commit -m "Ready for Render deployment"
git branch -M main
git remote add origin https://github.com/yourusername/ai-secretary.git
git push -u origin main
```

## Step 2: Deploy to Render

### 2.1 Create PostgreSQL Database First
1. Go to [render.com](https://render.com)
2. Click "New +" → "PostgreSQL"
3. **Name**: `ai-secretary-db`
4. **Plan**: `Starter` (free tier)
5. **Region**: Choose closest to your users
6. Click "Create Database"
7. Wait for database to be ready (2-3 minutes)

### 2.2 Create Web Service
1. Click "New +" → "Web Service"
2. Connect your GitHub repository
3. Select your `ai-secretary` repository

### 2.3 Configure Service
- **Name**: `ai-secretary`
- **Environment**: `PHP`
- **Region**: Same as database
- **Branch**: `main`
- **Root Directory**: (leave empty)
- **Build Command**: 
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
- **Start Command**: 
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

### 2.4 Environment Variables
Add these in the Render dashboard:

#### Database (from your PostgreSQL service):
- `DB_HOST` → Select `ai-secretary-db` → `host`
- `DB_PORT` → Select `ai-secretary-db` → `port`
- `DB_DATABASE` → Select `ai-secretary-db` → `database`
- `DB_USERNAME` → Select `ai-secretary-db` → `user`
- `DB_PASSWORD` → Select `ai-secretary-db` → `password`

#### Application:
- `APP_ENV` → `production`
- `APP_DEBUG` → `false`
- `APP_URL` → `https://ai-secretary.onrender.com`
- `LOG_LEVEL` → `error`

#### AI Configuration:
- `GEMINI_API_KEY` → `AIzaSyCzJd-xLQ-9auGxihe23JHrz0IOJ7W_3BY`
- `GEMINI_MODEL` → `gemini-1.5-flash`

#### Google OAuth:
- `GOOGLE_CLIENT_ID` → `4013995185-j1jjkia8v93a7dvnr4ugnc9amnhh5qag.apps.googleusercontent.com`
- `GOOGLE_CLIENT_SECRET` → `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`
- `GOOGLE_REDIRECT_URI` → `https://ai-secretary.onrender.com/auth/google/callback`

#### Email Configuration:
- `MAIL_MAILER` → `smtp`
- `MAIL_HOST` → `smtp.gmail.com`
- `MAIL_PORT` → `465`
- `MAIL_USERNAME` → `dailygoldtrades25@gmail.com`
- `MAIL_PASSWORD` → `your_16_character_app_password_here`
- `MAIL_ENCRYPTION` → `ssl`
- `MAIL_FROM_ADDRESS` → `dailygoldtrades25@gmail.com`
- `MAIL_FROM_NAME` → `AI Secretary`

## Step 3: Deploy

1. Click "Create Web Service"
2. Wait for build to complete (5-10 minutes)
3. Your app will be available at `https://ai-secretary.onrender.com`

## Step 4: Update Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Update OAuth redirect URI to: `https://ai-secretary.onrender.com/auth/google/callback`
3. Add `ai-secretary.onrender.com` to authorized domains

## Step 5: Test Your App

### 5.1 Basic Functionality
- Visit your app URL
- Test user registration/login
- Test all modules (emails, documents, meetings, calendar, expenses, inventory)

### 5.2 AI Features
- Test email AI features
- Test document AI features
- Test calendar AI features
- Test inventory AI features
- Test expense AI features
- Test dashboard AI analytics

## Troubleshooting

### Common Issues:
1. **Build Fails**: Check PHP version (8.1+ required)
2. **Database Connection**: Verify environment variables are connected to database
3. **AI Features**: Check Gemini API key is correct
4. **Google OAuth**: Verify redirect URI matches exactly

### Check Logs:
- Go to your service dashboard
- Click "Logs" tab
- Look for any error messages

## Performance Tips

1. **Enable Caching**: Already included in build command
2. **Monitor Usage**: Check Render dashboard for resource usage
3. **Database Performance**: Monitor database metrics in Render

## Cost

- **Free Tier**: 750 hours/month for web service
- **Database**: Free tier available
- **Total Cost**: $0/month on free tier

---

🎉 **Your AI Secretary is now live on Render!**

## Next Steps

1. **Custom Domain**: Add your own domain in Render settings
2. **Monitoring**: Set up monitoring and alerts
3. **Backups**: Enable database backups
4. **Scaling**: Upgrade plans as needed
