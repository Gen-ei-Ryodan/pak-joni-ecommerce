# CODING_STANDARDS.md

## Standar Pengkodean Umum

### 1. PHP
*   **Versi PHP:** Gunakan PHP 8.3 atau lebih tinggi (production 8.4.23).
*   **Strict Types:** Selalu gunakan `declare(strict_types=1);` di awal file PHP.
*   **Type Declarations:** Gunakan type declarations untuk parameter dan return type.
*   **PSR-12:** Ikuti standar PSR-12 untuk formatting kode.
*   **Namespace:** Gunakan namespace yang sesuai dengan struktur folder.

### 2. Laravel Spesifik
*   **Eloquent Models:** Gunakan singular naming (User, Product, Order).
*   **Database Tables:** Gunakan plural snake_case naming (users, products, orders).
*   **Controllers:** Gunakan suffix `Controller` (UserController, ProductController).
*   **Migrations:** Gunakan timestamp prefix dan descriptive naming (`2024_01_01_000000_create_users_table.php`).
*   **Seeders:** Gunakan suffix `Seeder` (DatabaseSeeder, UserSeeder).

### 3. Naming Convention
*   **Variables:** camelCase (`$userName`, `$productPrice`).
*   **Functions/Methods:** camelCase (`getUserById()`, `calculateTotal()`).
*   **Classes:** PascalCase (`UserController`, `ProductService`).
*   **Constants:** UPPER_SNAKE_CASE (`MAX_QUANTITY`, `DEFAULT_STATUS`).
*   **Database Columns:** snake_case (`user_name`, `created_at`).

### 4. Code Structure
*   **Controller Methods:** Maksimal 10-15 baris per method. Pindahkan logika bisnis ke Service.
*   **Service Layer:** Gunakan `app/Services/` untuk logika bisnis kompleks (StockService, OrderService, PaymentService, BiteshipService, ImageService).
*   **Bukan Repository Pattern:** project ini memakai Service Layer, tidak memakai repository.
*   **Dependency Injection:** Gunakan dependency injection di Controller dan Service.

### 5. Error Handling
*   **Exceptions:** Gunakan custom exceptions untuk error bisnis.
*   **Try-Catch:** Gunakan try-catch hanya di layer terluar (Controller).
*   **Logging:** Log error yang signifikan dengan context yang jelas.

### 6. Security
*   **SQL Injection:** Gunakan Eloquent ORM atau parameterized queries.
*   **XSS Protection:** Gunakan Blade escaping `{{ $variable }}`.
*   **CSRF Protection:** Selalu gunakan CSRF token untuk form POST.
*   **Input Validation:** Validasi semua input user dengan Laravel Validation.

### 7. Testing
*   **Unit Tests:** Test individual methods dan classes.
*   **Feature Tests:** Test endpoint dan flow aplikasi.
*   **Test Coverage:** Target minimal 70% test coverage.
*   **Test Naming:** Gunakan descriptive test names (`test_user_can_login_with_valid_credentials`).

### 8. Documentation
*   **PHPDoc:** Gunakan PHPDoc untuk classes dan methods publik.
*   **Inline Comments:** Gunakan komentar untuk logika kompleks.
*   **README:** Update README.md untuk perubahan signifikan.

### 9. Git & Version Control
*   **Commit Messages:** Gunakan conventional commits format (`feat:`, `fix:`, `chore:`).
*   **Branch Naming:** Gunakan descriptive branch names (`feature/user-auth`, `bugfix/login-issue`).
*   **Pull Requests:** Sertakan deskripsi yang jelas dan link ke issue.

### 10. Performance
*   **Eager Loading:** Gunakan eager loading untuk menghindari N+1 query problem.
*   **Caching:** Gunakan caching untuk data yang sering diakses.
*   **Database Indexes:** Tambahkan indexes untuk kolom yang sering di-query.

### 11. Filament v4 (khusus proyek ini)
*   **Header action namespace:** gunakan `\Filament\Actions\Action` untuk header actions. **JANGAN** pakai `Filament\Tables\Actions\Action` untuk action header (causes `Class not found`).
*   **Stok per varian:** stok dikelola per `ItemColor`/`PartVariant`, bukan pada Item/Part utama.
*   **TIDAK menggunakan Repository Pattern** — ikuti Service Layer (`app/Services/`) yang sudah ada.
