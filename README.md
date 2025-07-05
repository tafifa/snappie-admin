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

Snappie Admin Panel is a comprehensive management system for gamification-based check-in applications. This admin panel provides full control over user management, place management, review moderation, and check-in monitoring with a modern and user-friendly interface.

### **Key Highlights**
- 📊 **Real-time Dashboard** with deep analytics
- 👥 **User Management** with gamification system
- 🗺️ **Place Management** with GPS tracking
- ⭐ **Review Moderation** with approval workflow
- 📍 **Check-in Monitoring** with mission system
- 🎨 **Modern UI/UX** using Filament 3

---

## ✨ **Features**

### **📊 Dashboard & Analytics**
- [x] Real-time statistics overview (6 key metrics)
- [x] Recent activity monitoring (check-ins & reviews)
- [x] Pending review alerts with warning badges
- [x] Completion rate tracking
- [x] Custom widgets with pagination control

### **👥 User Management**
- [x] Complete CRUD operations
- [x] Advanced search & filtering
- [x] Gamification system (EXP, coins, levels)
- [x] Profile management with avatar upload
- [x] Activity tracking & analytics
- [x] Bulk operations support

### **🗺️ Place Management**
- [x] Comprehensive place database
- [x] 5 category system (Cafe, Traditional, Food Court, Street Food, Restaurant)
- [x] GPS coordinates with precision tracking
- [x] Image gallery management
- [x] Mission & reward system
- [x] Partnership program tracking
- [x] SEO-optimized with auto-generated slugs

### **⭐ Review Management**
- [x] 4-level moderation system (approved, pending, rejected, flagged)
- [x] Rating system with visual indicators
- [x] Bulk moderation tools
- [x] Image support (up to 5 images per review)
- [x] Content preview with tooltips
- [x] Advanced filtering by multiple criteria

### **📍 Check-in Management**
- [x] Dual status system (check-in + mission status)
- [x] GPS location tracking
- [x] Mission completion with image proof
- [x] Time tracking for activity monitoring
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
```

### **Additional OpenAPI Tools**

#### **Documentation Hosting**
```bash
# Serve interactive docs locally
npx swagger-ui-serve openapi.yaml

# Generate static HTML documentation
npx redoc-cli build openapi.yaml --output docs.html

# Host on GitHub Pages with Swagger UI
# Upload openapi.yaml to docs/ folder in your repository
```

#### **API Testing Tools**
- **Insomnia**: Import OpenAPI spec for visual API testing
- **Postman**: Import OpenAPI to auto-generate collection
- **Bruno**: Lightweight API client with OpenAPI support
- **HTTPie**: Command-line testing with OpenAPI integration

#### **Validation & Linting**
```bash
# Validate OpenAPI specification
npx swagger-parser validate openapi.yaml

# Lint for best practices
npx @apidevtools/swagger-parser validate openapi.yaml

# Check for breaking changes
npx oasdiff breaking openapi-old.yaml openapi.yaml
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
1. Navigate to `http://127.0.0.1:8000/admin` to Login

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

---

## 📮 **API Testing**

### **OpenAPI Specification**

We provide a comprehensive OpenAPI 3.0.3 specification for all API endpoints with detailed schemas, authentication, and examples.

#### **Files Included**
- `openapi.yaml` - Complete OpenAPI specification with all endpoints
- Interactive documentation with request/response examples
- Authentication schemas and security definitions

#### **Quick Setup**
```bash
# 1. View API Documentation
# Option A: Use Swagger UI (recommended)
npx swagger-ui-serve openapi.yaml

# Option B: Use online Swagger Editor
# Visit: https://editor.swagger.io/
# Copy content from openapi.yaml

# Option C: Use VS Code extension
# Install: "OpenAPI (Swagger) Editor" extension
# Open openapi.yaml file

# 2. Start Laravel Server
php artisan serve

# 3. Test Endpoints
# Use the interactive documentation to test API calls
```

### **API Endpoints Structure**

#### **🔐 Authentication**
```
POST /auth/register     - Create new account (auto-saves token)
POST /auth/login        - Email-based login (auto-saves token)
POST /auth/logout       - Revoke current token
```

#### **👤 User Profile**
```
GET  /user/profile      - View profile with stats
PUT  /user/profile      - Modify user information
```

#### **📍 Places**
```
GET  /places            - Paginated list with filtering
GET  /places/category   - Filter by place type
GET  /places/search     - Search by name/address
GET  /places/nearby     - GPS-based search
GET  /places/{id}       - Detailed place info with reviews
GET  /categories        - Available place categories
```

#### **✅ Check-ins**
```
POST /checkins          - GPS-validated check-in
GET  /checkins/history  - User's check-in history
```

#### **⭐ Reviews**
```
POST /reviews           - Add rating and review
GET  /reviews           - All reviews with pagination
GET  /places/{id}/reviews - Reviews for specific place
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
- Obtain token via /auth/login or /auth/register endpoint
- Add "Authorization: Bearer {token}" header to requests
- Check token validity and expiration
```

**"Place not found" Error**
```bash
- Verify place_id parameter is valid integer
- Use existing place ID from /places endpoint response
- Check database has seeded place data
```

**GPS Validation Failed**
```bash
- Ensure latitude/longitude are valid decimal numbers
- Check coordinates are within reasonable proximity to place
- Verify GPS parameters match OpenAPI schema requirements
```

**Server Connection Error**
```bash
- Ensure Laravel server is running: php artisan serve
- Verify server URL matches OpenAPI specification
- Check CORS settings for cross-origin requests
```

**OpenAPI Documentation Issues**
```bash
- Ensure openapi.yaml syntax is valid YAML
- Check OpenAPI 3.0.3 specification compliance
- Validate schema definitions match actual API responses
```

### **Mobile App Integration**

The OpenAPI specification serves as a **comprehensive reference** for mobile app developers:

- **API Contract**: Complete endpoint definitions with request/response schemas
- **Code Generation**: Generate client SDKs for iOS, Android, React Native, Flutter
- **Type Safety**: Strongly-typed models and interfaces
- **Authentication Flow**: Bearer token implementation with Laravel Sanctum
- **Error Handling**: Standardized error response formats
- **GPS Integration**: Location-based endpoint specifications
- **Validation Rules**: Parameter constraints and data validation

#### **SDK Generation Examples**
```bash
# Generate iOS Swift SDK
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g swift5 \
  -o ./ios-sdk

# Generate Android Kotlin SDK
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g kotlin \
  -o ./android-sdk

# Generate React Native TypeScript SDK
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g typescript-axios \
  -o ./react-native-sdk
```

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
