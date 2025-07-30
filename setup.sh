#!/bin/bash

# LoveNet Setup Script
# This script sets up the database and initializes sample data

echo "🚀 LoveNet Setup Script"
echo "========================"
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed. Please install MySQL first:"
    echo "   sudo apt update"
    echo "   sudo apt install mysql-server"
    echo ""
    exit 1
fi

echo "✅ MySQL is installed"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first:"
    echo "   sudo apt update"
    echo "   sudo apt install php php-mysql php-pdo"
    echo ""
    exit 1
fi

echo "✅ PHP is installed"

# Start MySQL service if not running
echo "🔧 Starting MySQL service..."
sudo systemctl start mysql

# Create database
echo "🗄️  Creating database..."
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS lovenet_db;"
if [ $? -eq 0 ]; then
    echo "✅ Database created successfully"
else
    echo "❌ Failed to create database. Please check your MySQL credentials."
    exit 1
fi

# Run the initialization script
echo "📊 Initializing database with sample data..."
php init-database.php

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 LoveNet setup completed successfully!"
    echo ""
    echo "📋 Summary:"
    echo "   ✅ Database created: lovenet_db"
    echo "   ✅ Tables created with sample data"
    echo "   ✅ Admin accounts created"
    echo "   ✅ User accounts created"
    echo "   ✅ Sample messages, reports, and verifications added"
    echo ""
    echo "🔑 Login Credentials:"
    echo "   👤 Super Admin: admin@lovenet.com / superadmin123"
    echo "   👤 Moderator: moderator1@lovenet.com / admin123"
    echo "   👤 Test User: sarah.j@email.com / password123"
    echo ""
    echo "🌐 Access URLs:"
    echo "   🏠 Main Site: http://localhost:8000"
    echo "   ⚙️  Admin Panel: http://localhost:8000/admin-dashboard.html"
    echo ""
    echo "🚀 To start the website:"
    echo "   python3 -m http.server 8000"
    echo ""
    echo "✨ LoveNet is ready to use!"
else
    echo "❌ Database initialization failed. Please check the error messages above."
    exit 1
fi 