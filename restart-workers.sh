#!/bin/bash

echo "📦 Stopping laravel-echo-server..."
sudo pkill -f laravel-echo-server

echo "🛠 Restarting laravel-echo-server..."
nohup laravel-echo-server start >> ~/laravel-echo.log 2>&1 &

echo "📦 Stopping queue:work..."
sudo pkill -f "php artisan queue:work"

echo "🚀 Restarting queue:work..."
nohup php artisan queue:work --tries=3 >> ~/queue.log 2>&1 &

echo "✅ Done! Both services restarted."
