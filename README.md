# 📦 Product Catalog RESTful API with Bearer Authentication

> **Portfolio Project for Junior PHP Developer Applications**  
> A professional RESTful API written in **pure modern PHP 8+** with SQLite via PDO, dynamic URL parameter routing, object-oriented payload validation, Bearer Token authorization with Middleware, and a built-in interactive browser test console.

---

## 🚀 Key Technical Competencies

- **RESTful Standards & Semantic HTTP**: Correct use of HTTP verbs (`GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`) and status codes (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `404 Not Found`, `409 Conflict`, `422 Unprocessable Entity`).
- **Standardized JSON Envelope**: Consistent payload structure (`status`, `message`, `data`, `meta`, `errors`).
- **Middleware Architecture**: Request interception validating `Authorization: Bearer <token>` headers before reaching controllers.
- **Dynamic Parameter Routing**: Regular expression URI matching with named parameters (e.g. `/api/v1/products/{id}`).
- **Data Validation**: Custom OOP `Validator` with rules for type checking, presence, length, and email formatting.
- **Interactive Documentation (Built-in Swagger/Postman alternative)**: Native web UI accessible at `/docs` allowing direct testing in the browser.

---

## 📁 Project Structure

```text
02-product-catalog-api/
├── database/
│   └── catalog.sqlite          # SQLite database with automatic sample seeds
├── public/
│   ├── docs.html              # Interactive documentation & test console
│   └── index.php              # API entry point & dispatching
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php      # Token generation and user registration
│   │   └── ProductController.php   # Products CRUD, search, and pagination
│   ├── Middleware/
│   │   └── AuthMiddleware.php     # Bearer Token verification guard
│   ├── Models/
│   │   ├── Product.php            # Product persistence, queries, and filters
│   │   └── User.php               # User credentials and token storage
│   ├── Utils/
│   │   ├── Response.php           # JSON response wrapper and CORS headers
│   │   └── Validator.php          # Request payload assertion helper
│   ├── Database.php               # PDO SQLite Singleton
│   └── Router.php                 # RESTful HTTP router with middleware support
└── README.md
```

---

## 🛠️ How to Run Locally

Launch PHP's built-in web server pointing to `public`:

```bash
cd 02-product-catalog-api
php -S localhost:8001 -t public
```

Open the interactive documentation and testing console:
👉 **[http://localhost:8001/docs](http://localhost:8001/docs)**

---

## 📡 API Endpoints Specification

| Method | Endpoint | Authorization | Description |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Public | Authenticate user and issue Bearer Token |
| `POST` | `/api/v1/auth/register` | Public | Register new API user |
| `GET` | `/api/v1/products` | Public | Retrieve paginated products with optional search |
| `GET` | `/api/v1/products/{id}` | Public | Retrieve single product details by ID |
| `POST` | `/api/v1/products` | 🔒 Bearer Token | Create new product record |
| `PUT` | `/api/v1/products/{id}` | 🔒 Bearer Token | Partial or full product update |
| `DELETE` | `/api/v1/products/{id}` | 🔒 Bearer Token | Remove product by ID |

---

## 💻 Sample Requests via cURL

### 1. List Products (with search filter and pagination)
```bash
curl -X GET "http://localhost:8001/api/v1/products?search=keyboard&page=1&per_page=5"
```

### 2. Create Product (Requires Bearer Header)
```bash
curl -X POST "http://localhost:8001/api/v1/products" \
  -H "Authorization: Bearer demo_bearer_token_super_secret_123456" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Full HD 1080p Webcam",
    "sku": "CAM-FHD-07",
    "description": "Streaming camera with built-in stereo microphone",
    "price": 69.90,
    "stock_quantity": 30,
    "category": "Peripherals"
  }'
```
