<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# 🎯 Snappie Admin Panel

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-blue.svg)](https://postgresql.org)

> **Comprehensive admin panel for Snappie - A gamified location-based check-in application**

---

## 📋 **Table of Contents**

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Schema](#-database-schema)
- [Usage Guide](#-usage-guide)
- [API Documentation](#-api-documentation)
- [API Testing](#-api-testing)
- [Development](#-development)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [Troubleshooting](#-troubleshooting)

---

## 🎯 **Overview**

Snappie Admin Panel adalah sistem manajemen komprehensif untuk aplikasi check-in berbasis gamifikasi. Panel admin ini menyediakan kontrol penuh atas user management, place management, review moderation, dan check-in monitoring dengan interface yang modern dan user-friendly.

### **Key Highlights**
- 📊 **Real-time Dashboard** dengan analytics mendalam
- 👥 **User Management** dengan gamification system
- 🗺️ **Place Management** dengan GPS tracking
- ⭐ **Review Moderation** dengan approval workflow
- 📍 **Check-in Monitoring** dengan mission system
- 🎨 **Modern UI/UX** menggunakan Filament 3

---

## ✨ **Features**

### **📊 Dashboard & Analytics**
- [x] Real-time statistics overview (6 key metrics)
- [x] Recent activity monitoring (check-ins & reviews)
- [x] Pending review alerts with warning badges
- [x] Completion rate tracking
- [x] Custom widgets dengan pagination control

### **👥 User Management**
- [x] Complete CRUD operations
- [x] Advanced search & filtering
- [x] Gamification system (EXP, coins, levels)
- [x] Profile management dengan avatar upload
- [x] Activity tracking & analytics
- [x] Bulk operations support

### **🗺️ Place Management**
- [x] Comprehensive place database
- [x] 5 category system (Cafe, Traditional, Food Court, Street Food, Restaurant)
- [x] GPS coordinates dengan precision tracking
- [x] Image gallery management
- [x] Mission & reward system
- [x] Partnership program tracking
- [x] SEO-optimized dengan auto-generated slugs

### **⭐ Review Management**
- [x] 4-level moderation system (approved, pending, rejected, flagged)
- [x] Rating system dengan visual indicators
- [x] Bulk moderation tools
- [x] Image support (up to 5 images per review)
- [x] Content preview dengan tooltips
- [x] Advanced filtering by multiple criteria

### **📍 Check-in Management**
- [x] Dual status system (check-in + mission status)
- [x] GPS location tracking
- [x] Mission completion dengan image proof
- [x] Time tracking untuk activity monitoring
- [x] Location analytics
- [x] Performance metrics

---

## 🛠️ **Tech Stack**

### **Backend**
- **Framework**: Laravel 11.x
- **Admin Panel**: Filament 3.x
- **Database**: PostgreSQL 15+
- **Authentication**: Laravel Guards
- **File Storage**: Local/S3 compatible

### **Frontend**
- **UI Framework**: Filament Components
- **CSS Framework**: Tailwind CSS
- **Icons**: Heroicons
- **Charts**: Alpine.js + Chart.js ready

### **Development Tools**
- **PHP**: 8.2+
- **Composer**: 2.x
- **Node.js**: 18+ (untuk asset compilation)
- **Git**: Version control

---

## 🚀 **Installation**

### **Prerequisites**
```bash
- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Node.js 18+
- Git
```

### **Step 1: Clone Repository**
```bash
git clone https://github.com/your-username/snappie-admin.git
cd snappie-admin
```

### **Step 2: Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### **Step 3: Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### **Step 4: Database Setup**
```bash
# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=snappie_admin
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database dengan sample data
php artisan db:seed
```

### **Step 5: Storage Setup**
```bash
# Create storage link
php artisan storage:link
```

### **Step 6: Start Development Server**
```bash
# Start Laravel server
php artisan serve

# Access admin panel
# URL: http://127.0.0.1:8000/admin
# Email: gracieo@gmail.com
# Password: ecarg1234
```

---

## ⚙️ **Configuration**

### **Admin Panel Settings**
```php
// config/filament.php
'path' => 'admin',
'domain' => null,
'pages' => [
    'dashboard' => App\Filament\Pages\Dashboard::class,
],
'auth' => [
    'guard' => 'admin',
    'pages' => [
        'login' => Filament\Pages\Auth\Login::class,
    ],
],
```

### **Database Configuration**
```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'snappie_admin'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
],
```

### **File Storage Configuration**
```php
// config/filesystems.php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

---

## 🗄️ **Database Schema**

### **Users Table**
```sql
- id (bigint, primary key)
- name (varchar 255)
- email (varchar 255, unique)
- email_verified_at (timestamp, nullable)
- password (varchar 255)
- phone (varchar 20, nullable)
- avatar (text, nullable)
- bio (text, nullable)
- exp (integer, default 0)
- coin (integer, default 0)
- level (integer, default 1)
- additional_info (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### **Places Table**
```sql
- id (bigint, primary key)
- name (varchar 255)
- slug (varchar 255, unique)
- description (text, nullable)
- address (text, nullable)
- latitude (decimal 10,8, nullable)
- longitude (decimal 11,8, nullable)
- category (varchar 50)
- image_urls (json, nullable)
- status (boolean, default true)
- partnership_status (boolean, default false)
- clue_mission (text, nullable)
- exp_reward (integer, default 10)
- coin_reward (integer, default 10)
- additional_info (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### **Reviews Table**
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- place_id (bigint, foreign key)
- content (text, nullable)
- vote (integer, 1-5)
- image_urls (json, nullable)
- status (enum: pending, approved, rejected, flagged)
- created_at (timestamp)
- updated_at (timestamp)
```

### **Check-ins Table**
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- place_id (bigint, foreign key)
- time (timestamp)
- location (json, nullable)
- checkin_status (enum: done, pending, notdone)
- mission_status (enum: completed, pending, failed)
- mission_image (text, nullable)
- mission_completed_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 📖 **Usage Guide**

### **Accessing Admin Panel**
1. Navigate to `http://127.0.0.1:8000/admin`
2. Login dengan credentials:
   - Email: `gracieo@gmail.com`
   - Password: `ecarg1234`

### **Dashboard Overview**
```
📊 Dashboard
├── 📈 Stats Overview (6 metrics)
├── 📍 Recent Check-ins (5 newest)
└── ⭐ Recent Reviews (5 newest)

🗂️ Core Data
├── 👥 Users (4 total)
└── 🗺️ Places (4 total)

🎯 Activity
├── ⭐ Reviews (47 total, with pending badge)
└── 📍 Check-ins (83 total)
```

### **Common Tasks**

#### **User Management**
```bash
# Create new user
1. Navigate to Core Data > Users
2. Click "New User"
3. Fill required fields (name, email, password)
4. Set gamification values (EXP, coins, level)
5. Upload avatar (optional)
6. Save

# Bulk operations
1. Select multiple users
2. Choose bulk action (delete, export, etc.)
3. Confirm action
```

#### **Place Management**
```bash
# Add new place
1. Navigate to Core Data > Places
2. Click "New Place"
3. Fill basic info (name, category, description)
4. Set GPS coordinates
5. Upload images
6. Configure mission & rewards
7. Save

# Update place status
1. Open place detail
2. Toggle status/partnership
3. Save changes
```

#### **Review Moderation**
```bash
# Approve/reject reviews
1. Navigate to Activity > Reviews
2. Filter by status: "pending"
3. Select reviews to moderate
4. Use bulk actions: "Approve" or "Reject"
5. Confirm action

# Quick moderation
1. Click approve/reject icon in table
2. Status updates automatically
```

#### **Check-in Monitoring**
```bash
# Monitor recent check-ins
1. Navigate to Activity > Check-ins
2. Filter by date range
3. Review GPS locations
4. Check mission completion status
5. Update status if needed
```

---

## 📚 **API Documentation**

### **OpenAPI Specification**

We provide complete API documentation in **OpenAPI 3.0.3** format which can be used for:
- 📖 **Interactive Documentation** with Swagger UI
- 🔧 **Code Generation** for client SDKs
- 🧪 **Automated Testing** and contract validation
- 🚀 **API Gateway Integration**

#### **📁 Available Format**
- **YAML**: [`openapi.yaml`](./openapi.yaml) - Complete OpenAPI specification

### **🚀 Quick Start with OpenAPI**

#### **View Interactive Documentation**
```bash
# Using Swagger UI with Docker
docker run -p 8080:8080 -e SWAGGER_JSON=/app/openapi.yaml -v $(pwd):/app swaggerapi/swagger-ui

# Then open: http://localhost:8080
```

#### **Generate Client SDK**
```bash
# JavaScript/TypeScript client
openapi-generator-cli generate -i openapi.yaml -g typescript-axios -o ./clients/typescript

# PHP client
openapi-generator-cli generate -i openapi.yaml -g php -o ./clients/php

# Python client
openapi-generator-cli generate -i openapi.yaml -g python -o ./clients/python
```

---

### **Base URL**
```
Local Development: http://127.0.0.1:8000/api/v1
Production: https://your-domain.com/api/v1
```

### **Authentication**
API uses **Laravel Sanctum** with Bearer Token authentication.

#### **Headers Required**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your-token}  // For protected endpoints
```

### **API Endpoints Overview**

#### **🔑 Authentication**
- `POST /auth/register` - Create new account
- `POST /auth/login` - Email-based login (simplified for MVP)
- `POST /auth/logout` - Revoke current token

#### **👤 User Profile**
- `GET /user/profile` - View profile with stats
- `PUT /user/profile` - Update user information
- `POST /user/avatar` - Upload avatar image

#### **📍 Places**
- `GET /places` - Paginated list with filtering
- `GET /places/nearby` - GPS-based search
- `GET /places/{id}` - Detailed place info with reviews
- `GET /categories` - Available place categories

#### **✅ Check-ins**
- `POST /checkins` - GPS-validated check-in
- `GET /checkins/history` - User's check-in history

#### **⭐ Reviews**
- `POST /reviews` - Add rating and review
- `GET /reviews` - Get reviews with filtering

### **Example API Requests**

#### **Register User**
```http
POST /auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "total_coin": 0,
      "total_exp": 0,
      "level": 1,
      "exp_to_next_level": 100
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer"
  }
}
```

#### **Create Check-in**
```http
POST /checkins
Authorization: Bearer {token}
Content-Type: application/json

{
  "place_id": 1,
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "id": 1,
    "place": {
      "id": 1,
      "name": "Warung Makan Sederhana",
      "category": "restaurant"
    },
    "checkin_status": "approved",
    "mission_status": "pending",
    "rewards": {
      "base_exp": 10,
      "base_coin": 5
    },
    "user_stats": {
      "total_exp": 360,
      "total_coin": 155,
      "level": 4
    }
  }
}
```

### **cURL Testing Examples**

#### **Register User**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com"
  }'
```

#### **Get Places**
```bash
curl -X GET http://127.0.0.1:8000/api/v1/places \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### **Create Check-in**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/checkins \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "place_id": 1,
    "latitude": -6.2088,
    "longitude": 106.8456
  }'
```

### **Error Responses**

#### **401 Unauthenticated**
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error_code": "UNAUTHENTICATED"
}
```

#### **422 Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "latitude": ["The latitude field is required."]
  }
}
```

#### **404 Not Found**
```json
{
  "success": false,
  "message": "Endpoint not found",
  "error_code": "ENDPOINT_NOT_FOUND"
}
```

### **Rate Limits (per minute)**
- **Authentication**: 5 requests
- **User Profile**: 60 requests  
- **Places**: 100 requests
- **Check-ins**: 10 requests
- **Reviews**: 20 requests

### **MVP Features Completed ✅**

✅ **Authentication System**
- User registration with validation (email-based for MVP)
- Login/logout with JWT tokens 
- Profile management with avatar upload
- Simplified authentication for testing

✅ **Places Discovery**
- List places with pagination & filtering
- Nearby places with GPS calculation (Haversine formula)
- Detailed place information with reviews
- Category system

✅ **Check-in System**
- GPS-based check-in verification
- Distance validation using Haversine formula
- Basic reward system (EXP & coins)
- Check-in history with pagination

✅ **Review System**
- Create reviews with rating 1-5
- Image upload support
- Review moderation (pending status)
- Rating statistics and aggregation

✅ **Gamification**
- EXP & coin tracking
- Level calculation (every 100 EXP = 1 level)
- User statistics (total check-ins, reviews, places visited)

✅ **Security & Performance**
- Rate limiting (60 requests/minute)
- Complete input validation
- Consistent error handling
- CORS support
- Laravel Sanctum authentication

---

## 📮 **API Testing**

### **Postman Collection Setup**

Kami menyediakan Postman collection lengkap untuk testing semua API endpoints dengan automatic token management dan environment variables.

#### **Files Included**
- `Snappie_API.postman_collection.json` - Main collection dengan semua API endpoints
- `Snappie_API.postman_environment.json` - Local Development environment

#### **Quick Setup**
```bash
# 1. Import ke Postman
1. Buka Postman
2. Click "Import" button (top left)
3. Upload collection dan environment files

# 2. Select Environment
1. Pilih environment dropdown (top right)
2. Choose "Snappie API" untuk development testing

# 3. Start Testing
1. Pastikan Laravel server running: php artisan serve
2. Run requests sesuai urutan
```

### **Collection Features**

#### **✅ Automatic Token Management**
- Login/Register otomatis menyimpan auth token
- Token digunakan di semua subsequent requests
- Tidak perlu manual copying!

#### **✅ Smart Environment Variables**
- Pre-configured API URLs
- Test coordinates untuk GPS testing
- Automatic ID capture untuk related requests

#### **✅ Complete Test Coverage**
- Semua 15+ API endpoints included
- Request examples dengan proper data
- Response validation tests

#### **✅ GPS Testing Ready**
- Pre-configured test coordinates (Yogyakarta area)
- Nearby places search dengan distance calculation
- Check-in GPS validation testing

### **API Endpoints Structure**

#### **🔐 Authentication**
```
POST /auth/register     - Create new account (auto-saves token)
POST /auth/login        - Email-based login (auto-saves token)
POST /auth/logout       - Revoke current token
```

#### **👤 User Profile**
```
GET  /user/profile      - View profile dengan stats
PUT  /user/profile      - Modify user information
```

#### **📍 Places**
```
GET  /places            - Paginated list dengan filtering
GET  /places/category   - Filter by place type
GET  /places/search     - Search by name/address
GET  /places/nearby     - GPS-based search
GET  /places/{id}       - Detailed place info dengan reviews
GET  /categories        - Available place categories
```

#### **✅ Check-ins**
```
POST /checkins          - GPS-validated check-in
GET  /checkins/history  - User's check-in history
```

#### **⭐ Reviews**
```
POST /reviews           - Add rating dan review
GET  /reviews           - All reviews dengan pagination
GET  /places/{id}/reviews - Reviews untuk specific place
```

### **Testing Workflow**

#### **Quick Test Sequence:**
```bash
1. 🔐 Register/Login
   POST /auth/register  OR  POST /auth/login
   ✅ Token automatically saved

2. 📍 Test Places
   GET /categories          → List available categories
   GET /places             → Get places list  
   GET /places/nearby      → GPS-based search
   GET /places/{id}        → Place details

3. ✅ Test Check-in
   POST /checkins          → Create check-in
   GET /checkins/history   → View history

4. ⭐ Test Reviews
   POST /reviews           → Create review
   GET /reviews            → View reviews

5. 👤 Test Profile
   GET /user/profile       → View profile
   PUT /user/profile       → Update profile
```

### **Environment Configuration**

#### **Local Development**
```json
{
  "api_base": "http://127.0.0.1:8000/api",
  "base_url": "http://127.0.0.1:8000/api/v1",
  "test_latitude": "-7.7571",
  "test_longitude": "110.3789"
}
```

#### **Production**
```json
{
  "api_base": "https://your-domain.com/api",
  "base_url": "https://your-domain.com/api/v1",
  "test_latitude": "-6.2088",
  "test_longitude": "106.8456"
}
```

### **Troubleshooting API Testing**

#### **Common Issues:**

**"Unauthenticated" Error**
```bash
- Run Login/Register first untuk get token
- Check if token tersimpan di environment
```

**"Place not found" Error**
```bash
- Check if place_id variable set correctly
- Use valid place ID dari places list
```

**GPS Validation Failed**
```bash
- Ensure coordinates dekat dengan place
- Check test_latitude dan test_longitude values
```

**Server Connection Error**
```bash
- Make sure Laravel server running: php artisan serve
- Check base_url di environment matches server URL
```

### **Mobile App Integration**

Postman collection ini serves sebagai **reference implementation** untuk mobile app developers:

- **Request Format**: Exact JSON structure yang dibutuhkan
- **Response Format**: Expected response structure  
- **Error Handling**: Semua possible error responses
- **Authentication Flow**: Token-based auth implementation
- **GPS Integration**: Location-based features testing

---

## 🔧 **Development**

### **Project Structure**
```
snappie-admin/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # CRUD resources
│   │   ├── Widgets/           # Dashboard widgets
│   │   └── Pages/             # Custom pages
│   ├── Models/                # Eloquent models
│   └── Providers/             # Service providers
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/                # Blade templates
│   └── css/                  # Custom styles
└── config/                   # Configuration files
```

### **Adding New Features**

#### **Create New Resource**
```bash
# Generate resource
php artisan make:filament-resource ModelName

# Generate with pages
php artisan make:filament-resource ModelName --generate

# Generate widget
php artisan make:filament-widget WidgetName
```

#### **Custom Validation**
```php
// In Resource form
Forms\Components\TextInput::make('field')
    ->required()
    ->rules(['min:3', 'max:255'])
    ->validationMessages([
        'min' => 'Field must be at least 3 characters.',
    ])
```

#### **Custom Actions**
```php
// In Resource table
Tables\Actions\Action::make('custom_action')
    ->label('Custom Action')
    ->icon('heroicon-o-star')
    ->action(function ($record) {
        // Custom logic here
    })
```

### **Testing**
```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=UserTest

# Generate test coverage
php artisan test --coverage
```

---

## 🚀 **Deployment**

### **Production Environment**
```bash
# Set environment to production
APP_ENV=production
APP_DEBUG=false

# Configure database
DB_CONNECTION=pgsql
DB_HOST=your_production_host
DB_DATABASE=your_production_db

# Set app key
php artisan key:generate
```

### **Optimization**
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### **Server Requirements**
```
- PHP 8.2+
- PostgreSQL 15+
- Nginx/Apache
- SSL Certificate
- Minimum 512MB RAM
- 10GB Storage
```

---

## 🤝 **Contributing**

### **Development Workflow**
1. Fork repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Make changes
4. Run tests: `php artisan test`
5. Commit changes: `git commit -m "Add new feature"`
6. Push branch: `git push origin feature/new-feature`
7. Create Pull Request

### **Coding Standards**
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add PHPDoc comments
- Write tests for new features
- Follow Laravel best practices

---

**Made with ❤️ for Snappie Application**

*Last updated: June 2025*
