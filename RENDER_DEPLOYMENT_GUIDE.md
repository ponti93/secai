# 🚀 Deploy AI Secretary to Render

## Prerequisites
- GitHub account
- Render account (free tier available)
- Your project code in a GitHub repository

## Step 1: Prepare Your Repository

### 1.1 Push to GitHub
```bash
git init
git add .
git commit -m "Initial commit for Render deployment"
git branch -M main
git remote add origin https://github.com/yourusername/ai-secretary.git
git push -u origin main
```

### 1.2 Update Google OAuth Settings
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Update your OAuth redirect URI to: `https://ai-secretary.onrender.com/auth/google/callback`
3. Add your Render domain to authorized domains

## Step 2: Deploy to Render

### 2.1 Create New Web Service
1. Go to [render.com](https://render.com)
2. Click "New +" → "Web Service"
3. Connect your GitHub repository
4. Select your `ai-secretary` repository

### 2.2 Configure Service Settings
- **Name**: `ai-secretary`
- **Environment**: `PHP`
- **Region**: Choose closest to your users
- **Branch**: `main`
- **Root Directory**: Leave empty (root)
- **Build Command**: `composer install --no-dev --optimize-autoloader`
- **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`

### 2.3 Environment Variables
Add these environment variables in Render dashboard:

#### Required Variables:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=(will be generated automatically)
APP_URL=https://ai-secretary.onrender.com
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=(will be set automatically by Render)
DB_PORT=(will be set automatically by Render)
DB_DATABASE=(will be set automatically by Render)
DB_USERNAME=(will be set automatically by Render)
DB_PASSWORD=(will be set automatically by Render)

GEMINI_API_KEY=AIzaSyCzJd-xLQ-9auGxihe23JHrz0IOJ7W_3BY
GEMINI_MODEL=gemini-1.5-flash

GOOGLE_CLIENT_ID=4013995185-j1jjkia8v93a7dvnr4ugnc9amnhh5qag.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT_URI=https://ai-secretary.onrender.com/auth/google/callback

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=dailygoldtrades25@gmail.com
MAIL_PASSWORD=your_16_character_app_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=dailygoldtrades25@gmail.com
MAIL_FROM_NAME=AI Secretary
```

## Step 3: Create PostgreSQL Database

### 3.1 Add Database
1. In Render dashboard, click "New +" → "PostgreSQL"
2. Name: `ai-secretary-db`
3. Plan: `Starter` (free tier)
4. Region: Same as your web service

### 3.2 Connect Database to Service
1. Go to your web service settings
2. Add environment variables that reference the database:
   - `DB_HOST` → `ai-secretary-db` (from database)
   - `DB_PORT` → `ai-secretary-db` (from database)
   - `DB_DATABASE` → `ai-secretary-db` (from database)
   - `DB_USERNAME` → `ai-secretary-db` (from database)
   - `DB_PASSWORD` → `ai-secretary-db` (from database)

## Step 4: Deploy and Configure

### 4.1 Deploy
1. Click "Create Web Service"
2. Wait for build to complete (5-10 minutes)
3. Your app will be available at `https://ai-secretary.onrender.com`

### 4.2 Run Database Migrations
1. Go to your service dashboard
2. Click "Shell" tab
3. Run these commands:
```bash
php artisan migrate --force
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 5: Test Your Deployment

### 5.1 Basic Tests
- Visit your app URL
- Test user registration/login
- Test AI features
- Check database connectivity

### 5.2 AI Features Test
- Test email AI features
- Test document AI features
- Test calendar AI features
- Test inventory AI features
- Test expense AI features
- Test dashboard AI analytics

## Step 6: Custom Domain (Optional)

### 6.1 Add Custom Domain
1. Go to your service settings
2. Click "Custom Domains"
3. Add your domain
4. Update DNS records as instructed

### 6.2 Update Environment Variables
- Update `APP_URL` to your custom domain
- Update `GOOGLE_REDIRECT_URI` to your custom domain
- Update Google OAuth settings

## Troubleshooting

### Common Issues:
1. **Build Fails**: Check PHP version (8.1+ required)
2. **Database Connection**: Verify environment variables
3. **AI Features Not Working**: Check Gemini API key
4. **Google OAuth**: Verify redirect URI matches

### Logs:
- Check Render service logs for errors
- Check database logs if needed
- Monitor application performance

## Performance Optimization

### 6.1 Enable Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6.2 Optimize Composer
```bash
composer install --no-dev --optimize-autoloader
```

### 6.3 Database Optimization
- Use database indexes
- Optimize queries
- Monitor database performance

## Security Considerations

1. **Environment Variables**: Never commit sensitive data
2. **API Keys**: Use Render's environment variables
3. **Database**: Use Render's managed PostgreSQL
4. **HTTPS**: Automatically provided by Render
5. **Headers**: Security headers in .htaccess

## Monitoring

1. **Uptime**: Monitor service availability
2. **Performance**: Check response times
3. **Errors**: Monitor error logs
4. **Database**: Monitor database performance
5. **AI API**: Monitor Gemini API usage

## Cost Management

- **Free Tier**: 750 hours/month for web service
- **Database**: Free tier available
- **Bandwidth**: Included in free tier
- **Upgrade**: When you need more resources

## Support

- **Render Docs**: [render.com/docs](https://render.com/docs)
- **Laravel Docs**: [laravel.com/docs](https://laravel.com/docs)
- **Community**: Render Discord, Laravel Discord

---

🎉 **Your AI Secretary application is now live on Render!**
