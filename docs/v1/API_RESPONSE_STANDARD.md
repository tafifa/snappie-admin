# Standardized API Response - Snappie Admin

## 🚀 **Overview**
This document outlines the standardized API response structure for the Snappie Admin API. All endpoints will follow this format for consistency and predictability.

## 📝 **Response Structure**

### ✅ **Success Response**
```json
{
    "success": true,
    "message": "Descriptive success message",
    "data": {
        "key": "value"
    }
}
```

### ❌ **Error Response**
```json
{
    "success": false,
    "message": "Descriptive error message",
    "errors": {
        "field": ["Error details"]
    }
}
```

### 📄 **Paginated Response**
```json
{
    "success": true,
    "message": "Success message",
    "data": [
        { "item": 1 },
        { "item": 2 }
    ],
    "pagination": {
        "total": 100,
        "per_page": 10,
        "current_page": 1,
        "last_page": 10,
        "from": 1,
        "to": 10
    }
}
```

## 🛠️ **Implementation**

### 1. `ApiResponseTrait`
- **Location:** `app/Traits/ApiResponseTrait.php`
- **Purpose:** Provides standardized response methods for controllers.
- **Methods:**
  - `successResponse($data, $message, $code)`
  - `errorResponse($message, $code, $errors)`
  - `paginatedResponse($paginator, $message, $code)`

### 2. `ApiResponseHelper`
- **Location:** `app/Helpers/ApiResponseHelper.php`
- **Purpose:** Static helper class for consistent responses anywhere.
- **Methods:**
  - `ApiResponseHelper::success(...)`
  - `ApiResponseHelper::error(...)`
  - `ApiResponseHelper::paginated(...)`

### 3. `ApiResponse` Middleware
- **Location:** `app/Http/Middleware/ApiResponse.php`
- **Purpose:** Intercepts all API responses and formats them.
- **Features:**
  - Adds CORS and API version headers.
  - Automatically formats JSON responses to the standard structure.
  - Ensures consistency across all endpoints.

## 🔄 **How to Use**

### In Controllers (with Trait):
```php
use App\Traits\ApiResponseTrait;

class MyController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $data = MyModel::all();
        return $this->successResponse($data, 'Data retrieved successfully');
    }

    public function store(Request $request)
    {
        // ... validation ...
        return $this->errorResponse('Validation failed', 422, $validator->errors());
    }
}
```

### Anywhere (with Helper):
```php
use App\Helpers\ApiResponseHelper;

class MyService
{
    public function doSomething()
    {
        if ($success) {
            return ApiResponseHelper::success(['result' => 'done']);
        } else {
            return ApiResponseHelper::error('Something went wrong');
        }
    }
}
```

## ⚙️ **Configuration**
The `ApiResponse` middleware is automatically applied to all routes in the `api` group via `bootstrap/app.php`.

```php
// bootstrap/app.php
$middleware->group('api', [
    'api.response',
    // ... other middleware
]);
```

## 🚨 **Exception Handling**
Standard exceptions are automatically formatted into the standard JSON response structure in `bootstrap/app.php`.

- `AuthenticationException` -> 401
- `ValidationException` -> 422
- `NotFoundHttpException` -> 404
- `MethodNotAllowedHttpException` -> 405
- `TooManyRequestsHttpException` -> 429

This ensures that even unexpected errors return a consistent and predictable response.
---
*Last Updated: September 19, 2025*
*Status: Implemented & Active*