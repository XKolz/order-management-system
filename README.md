# Laravel Order Management System - API Testing Guide

## Setup Instructions

# 1. Create the project (if you haven't already)

composer create-project laravel/laravel order-management-system
cd order-management-system

2️⃣ Install Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

🗃️ Models & Migrations
Users (default with Laravel)
php artisan make:model User -m
php artisan make:model Product -m
php artisan make:model Order -m
php artisan make:model OrderItem -m

<!--  -->

# 1. Create Controllers Without Api/ Subdirectory

php artisan make:controller AuthController  
php artisan make:controller ProductController
php artisan make:controller OrderController

<!--  -->

php artisan make:middleware AdminMiddleware

<!-- Edit from here -->

1. Run migrations:

```bash
php artisan migrate
```

2. Seed the database:

```bash
php artisan db:seed
```

Alternative: Run Everything at Once
php artisan migrate:fresh --seed

# Verify routes are working

php artisan route:list
php artisan route:list --path=api

3. Start the server:

```bash
php artisan serve
```

## API Endpoints Testing

### Authentication

#### Register a new user

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Response:**

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "is_admin": false
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

#### Get authenticated user

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Product Management

#### Get all products (Public)

```bash
curl -X GET http://localhost:8000/api/products
```

#### Add a new product (Admin only)

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE" \
  -d '{
    "name": "Gaming Mouse",
    "price": 79.99,
    "stock": 50
  }'
```

#### Update a product (Admin only)

```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE" \
  -d '{
    "name": "Updated Product Name",
    "price": 89.99,
    "stock": 25
  }'
```

### 3️⃣ Order Management

#### Create a new order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ]
  }'
```

**Success Response:**

```json
{
    "message": "Order created successfully",
    "order": {
        "id": 1,
        "user_id": 2,
        "total_price": "2199.97",
        "status": "pending",
        "created_at": "2025-06-20 10:30:00",
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "quantity": 2,
                "price_at_purchase": "999.99",
                "subtotal": "1999.98",
                "product": {
                    "id": 1,
                    "name": "Laptop",
                    "price": "999.99",
                    "stock": 48
                }
            }
        ]
    }
}
```

#### Get all user orders

```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Get specific order

```bash
curl -X GET http://localhost:8000/api/orders/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Cancel an order

```bash
curl -X PATCH http://localhost:8000/api/orders/1/cancel \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Error Handling Examples

### Insufficient Stock Error

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1000
      }
    ]
  }'
```

**Error Response:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "items": [
            "Insufficient stock for product: Laptop. Available: 50, Requested: 1000"
        ]
    }
}
```

### Unauthorized Access Error

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer NON_ADMIN_TOKEN" \
  -d '{
    "name": "Test Product",
    "price": 99.99,
    "stock": 10
  }'
```

**Error Response:**

```json
{
    "message": "Unauthorized. Admin access required."
}
```

## 🔧 Advanced Testing Scenarios

### Test Race Condition (Concurrent Orders)

Create multiple orders simultaneously for the same product to test stock validation:

```bash
# Terminal 1
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN1" \
  -d '{"items":[{"product_id":1,"quantity":30}]}'

# Terminal 2 (run immediately)
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN2" \
  -d '{"items":[{"product_id":1,"quantity":30}]}'
```

### Test Order Cancellation and Stock Recovery

```bash
# 1. Create order
ORDER_ID=$(curl -s -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"items":[{"product_id":1,"quantity":5}]}' | jq -r '.order.id')

# 2. Check product stock (should be reduced)
curl -X GET http://localhost:8000/api/products/1

# 3. Cancel order
curl -X PATCH http://localhost:8000/api/orders/$ORDER_ID/cancel \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Check product stock again (should be restored)
curl -X GET http://localhost:8000/api/products/1
```

## Default Test Data

After running the seeder, you'll have:

**Admin User:**

-   Email: admin@example.com
-   Password: password

**Regular User:**

-   Email: john@example.com
-   Password: password

**Products:**

-   Laptop ($999.99, 50 in stock)
-   Smartphone ($699.99, 100 in stock)
-   Wireless Headphones ($199.99, 75 in stock)
-   And 7 more products...

## Security Features Implemented

1. **Sanctum Authentication** - Token-based API authentication
2. **Admin Authorization** - Only admins can manage products
3. **User Isolation** - Users can only see their own orders
4. **Stock Validation** - Prevents overselling
5. **Atomic Transactions** - Ensures data consistency
6. **Input Validation** - Comprehensive request validation
7. **Rate Limiting** - Built-in Laravel rate limiting (can be configured)
