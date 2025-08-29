# RISE CRM + MariaDB on Render - Deployment Guide

## Overview
This setup deploys RISE CRM with a local MariaDB database on Render, eliminating cross-region database latency for maximum performance.

## Architecture
- **MariaDB Service**: Private service with persistent disk storage
- **PHP Backend**: Web service running CodeIgniter 4
- **Database**: Local to the app region (no network latency)

## Step 1: Deploy to Render

### Option A: Using Render Blueprint (Recommended)
1. Push this code to your GitHub repository
2. In Render Dashboard, click "New +" → "Blueprint"
3. Connect your GitHub repo
4. Select the `render.yaml` file
5. Click "Apply"

### Option B: Manual Deployment
1. Create MariaDB service first:
   - Type: Private Service
   - Name: `smcrm-mariadb`
   - Environment: Docker
   - Image: `mariadb:11`
   - Plan: Free

2. Create PHP Backend service:
   - Type: Web Service
   - Name: `smcrm-backend`
   - Environment: Docker
   - Dockerfile Path: `./Dockerfile`
   - Plan: Free

## Step 2: Set Environment Variables

### MariaDB Service (`smcrm-mariadb`)
```
MYSQL_ROOT_PASSWORD=<your-secure-root-password>
MYSQL_DATABASE=risecrm
MYSQL_USER=rise_user
MYSQL_PASSWORD=<your-app-password>
MYSQL_ROOT_HOST=%
MYSQL_INITDB_SKIP_TZINFO=1
```

### PHP Backend Service (`smcrm-backend`)
```
DB_HOST=smcrm-mariadb
DB_USERNAME=rise_user
DB_PASSWORD=<same-as-MYSQL_PASSWORD>
DB_NAME=risecrm
DB_PREFIX=app_
DB_PORT=3306
CI_ENVIRONMENT=production
APP_ENV=production
APACHE_DOCUMENT_ROOT=/var/www/html/CRM
```

## Step 3: Import Database Schema

After MariaDB starts successfully:

1. **Option A: Using Render Shell**
   ```bash
   # Connect to your backend service shell
   # Navigate to the CRM directory
   cd /var/www/html/CRM
   
   # Import the database schema
   mysql -h smcrm-mariadb -u rise_user -p risecrm < install/database.sql
   ```

2. **Option B: Using External MySQL Client**
   ```bash
   # Get the external connection details from Render Dashboard
   mysql -h <external-host> -P <external-port> -u rise_user -p risecrm < database.sql
   ```

## Step 4: Update Vercel Configuration

Update your `vercel.json` to point to the new backend:
```json
{
  "version": 2,
  "routes": [
    { "src": "/(.*)", "dest": "https://smcrm-backend.onrender.com/$1" }
  ]
}
```

## Step 5: Test the Application

1. Visit your Vercel URL
2. The app should now load much faster (no cross-region DB calls)
3. Complete the web installer if needed
4. Test login/signup functionality

## Performance Benefits

- **Database Latency**: Reduced from 100-300ms to <5ms
- **Connection Pooling**: Persistent connections within the same region
- **Cold Start**: Faster database initialization
- **Overall TTFB**: Should improve by 2-5 seconds per page

## Troubleshooting

### MariaDB Won't Start
- Check environment variables are set correctly
- Ensure disk has sufficient space
- Check logs in Render Dashboard

### Database Connection Failed
- Verify `DB_HOST=smcrm-mariadb` (internal hostname)
- Check user credentials match between services
- Ensure database `risecrm` exists

### Import Errors
- Verify `database.sql` is present in the container
- Check file permissions
- Ensure MariaDB is fully started before importing

## Monitoring

- **Render Dashboard**: Monitor service health and logs
- **Database Metrics**: Check connection count and query performance
- **Application Logs**: Monitor PHP/Apache logs for errors

## Cost Optimization

- **Free Tier**: Both services run on free plans
- **Disk Storage**: 1GB included, monitor usage
- **Bandwidth**: Free tier includes generous limits
- **Auto-sleep**: Free services sleep after 15 minutes of inactivity

## Next Steps

After successful deployment:
1. Set up monitoring and alerts
2. Configure backup strategy for the database
3. Implement caching layers (OPcache, Redis)
4. Set up CI/CD pipeline for automated deployments
