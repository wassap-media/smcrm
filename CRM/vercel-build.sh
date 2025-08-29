#!/bin/bash
# Vercel Build Script for RISE CRM

echo "🚀 Starting Vercel build for RISE CRM..."

# Create necessary directories if they don't exist
mkdir -p writable/cache
mkdir -p writable/logs
mkdir -p writable/session
mkdir -p writable/debugbar
mkdir -p writable/uploads

# Set permissions (Vercel will handle this automatically)
echo "📁 Created writable directories"

# Copy environment file if it exists
if [ -f .env ]; then
    echo "📋 Environment file found"
else
    echo "⚠️  No .env file found - using default configuration"
fi

# Check PHP version
php --version

echo "✅ Build completed successfully!"
