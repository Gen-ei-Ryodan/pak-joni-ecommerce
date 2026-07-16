# API_REFERENCE.md

## Daftar Endpoint API

### Autentikasi

#### POST /api/login
*   **Deskripsi:** Login pengguna.
*   **Request Body:** `{"email": "user@example.com", "password": "password"}`
*   **Response:** `{"token": "jwt_token", "user": {...}}`

#### POST /api/register
*   **Deskripsi:** Registrasi pengguna baru.
*   **Request Body:** `{"name": "John Doe", "email": "user@example.com", "password": "password", "password_confirmation": "password"}`
*   **Response:** `{"token": "jwt_token", "user": {...}}`

### Produk

#### GET /api/products
*   **Deskripsi:** Mendapatkan daftar produk.
*   **Query Parameters:** `?category=id&search=keyword&page=1`
*   **Response:** `{"data": [...], "meta": {...}}`

#### GET /api/products/{id}
*   **Deskripsi:** Mendapatkan detail produk.
*   **Response:** `{"id": 1, "name": "Product Name", ...}`

### Keranjang

#### GET /api/cart
*   **Deskripsi:** Mendapatkan item keranjang pengguna.
*   **Headers:** `Authorization: Bearer {token}`
*   **Response:** `{"items": [...], "total": 100}`

#### POST /api/cart
*   **Deskripsi:** Menambahkan produk ke keranjang.
*   **Headers:** `Authorization: Bearer {token}`
*   **Request Body:** `{"product_id": 1, "quantity": 2}`
*   **Response:** `{"message": "Product added to cart"}`

### Pesanan

#### POST /api/orders
*   **Deskripsi:** Membuat pesanan baru.
*   **Headers:** `Authorization: Bearer {token}`
*   **Request Body:** `{"shipping_address_id": 1, "payment_method": "credit_card"}`
*   **Response:** `{"order_id": 123, "status": "pending"}`

#### GET /api/orders
*   **Deskripsi:** Mendapatkan daftar pesanan pengguna.
*   **Headers:** `Authorization: Bearer {token}`
*   **Response:** `{"orders": [...]}`

#### GET /api/orders/{id}
*   **Deskripsi:** Mendapatkan detail pesanan.
*   **Headers:** `Authorization: Bearer {token}`
*   **Response:** `{"id": 123, "items": [...], "status": "processing"}`
