#!/bin/bash

# Production Cache Clear & Build Script
# Run this after deployment or when updating configs

echo "🔄 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "🔄 Rebuilding optimized cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Cache rebuilt successfully!"
echo ""
echo "📝 Summary:"
echo "  ✓ Config cache cleared & rebuilt"
echo "  ✓ Route cache cleared & rebuilt" 
echo "  ✓ View cache cleared & rebuilt"
echo ""
echo "🚀 Your application is ready for production!"
