# Docker Deployment Guide for AI Secretary

This guide explains how to deploy the AI Secretary Laravel application using Docker on Render.

## 🐳 Docker Configuration

The application is configured to run in Docker with the following setup:

### Files Added:
- `Dockerfile` - Main Docker configuration
- `.docker/apache/000-default.conf` - Apache virtual host configuration
- `.dockerignore` - Files to exclude from Docker build
- `docker-compose.yml` - Local development setup
- `.render.yaml` - Render deployment configuration

## 🚀 Render Deployment

### 1. Connect to Render
1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click "New +" → "Web Service"
3. Connect your GitHub repository: `https://github.com/ponti93/secai.git`
4. Render will automatically detect the Docker configuration

### 2. Configure Environment Variables
Set these environment variables in Render:

```env
# App Configuration
APP_NAME="AI Secretary"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

# Database (will be auto-configured by Render)
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# AI Services
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-1.5-flash

# Google Services
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://your-app-name.onrender.com/auth/google/callback

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="AI Secretary"
```

### 3. Create PostgreSQL Database
1. In Render Dashboard, click "New +" → "PostgreSQL"
2. Choose "Starter" plan
3. Name it `ai-secretary-db`
4. Copy the connection details to your web service environment variables

### 4. Deploy
1. Click "Create Web Service"
2. Render will automatically build and deploy your Docker container
3. The application will be available at your Render URL

## 🛠️ Local Development

### Using Docker Compose
```bash
# Start the application
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Access the application
# http://localhost:8000
```

### Manual Docker Commands
```bash
# Build the image
docker build -t ai-secretary .

# Run the container
docker run -p 8000:80 \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=your-db-name \
  -e DB_USERNAME=your-db-user \
  -e DB_PASSWORD=your-db-password \
  ai-secretary
```

## 📁 Docker File Structure

```
├── Dockerfile                 # Main Docker configuration
├── .docker/
│   └── apache/
│       └── 000-default.conf  # Apache configuration
├── .dockerignore             # Files to exclude from Docker build
├── docker-compose.yml        # Local development setup
└── .render.yaml             # Render deployment config
```

## 🔧 Docker Features

- **PHP 8.2** with Apache web server
- **PostgreSQL** support with PDO extension
- **Composer** for dependency management
- **Laravel** optimizations (caching, etc.)
- **Automatic migrations** on startup
- **Health checks** for Render
- **Environment-based configuration**

## 🐛 Troubleshooting

### Common Issues:

1. **Database Connection Failed**
   - Check environment variables in Render
   - Ensure PostgreSQL service is running
   - Verify database credentials

2. **Permission Errors**
   - The Dockerfile sets proper permissions for Laravel
   - Storage and cache directories are writable

3. **Build Failures**
   - Check `.dockerignore` to ensure all necessary files are included
   - Verify `composer.json` and `composer.lock` are present

4. **Application Key Issues**
   - The startup script automatically generates an APP_KEY if missing
   - Check Render logs for key generation errors

## 📊 Monitoring

- Check Render logs for application status
- Monitor database connections
- Verify environment variables are set correctly
- Check health endpoint: `https://your-app.onrender.com/`

## 🔄 Updates

To update your application:
1. Push changes to GitHub
2. Render will automatically rebuild and redeploy
3. Database migrations will run automatically
4. Configuration will be cached for performance
