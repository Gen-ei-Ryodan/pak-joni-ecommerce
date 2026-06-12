# ARCHITECTURE.md

## Arsitektur Umum
Proyek ini menggunakan arsitektur **MVC (Model-View-Controller)** dengan framework Laravel. Arsitektur ini memisahkan logika bisnis (Model), presentasi (View), dan kontrol alur aplikasi (Controller).

## Frontend
*   **Blade Templates:** Template engine bawaan Laravel untuk rendering HTML.
*   **JavaScript/Vue.js:** Digunakan untuk interaktivitas pada komponen tertentu.
*   **CSS:** Styling dengan kemungkinan framework seperti Tailwind CSS atau Bootstrap.

## Backend
*   **Laravel Framework:** Framework PHP yang menyediakan struktur MVC, routing, ORM (Eloquent), dan banyak fitur lainnya.
*   **Eloquent ORM:** Object-Relational Mapping untuk interaksi dengan database.
*   **Service Pattern:** Logika bisnis kompleks dipisahkan ke dalam kelas Service untuk menjaga Controller tetap ringan.
*   **Repository Pattern (Opsional):** Digunakan untuk mengabstraksi logika akses data.

## Database
*   **MySQL/PostgreSQL:** Database relasional.
*   **Eloquent Migrations:** Untuk versioning skema database.
*   **Eloquent Seeders:** Untuk mengisi data awal (seeding).

## Struktur Folder
*   `app/`: Inti aplikasi
    *   `Models/`: Entitas data (User, Product, Order, dll.)
    *   `Http/Controllers/`: Controller untuk menangani request
    *   `Services/`: Kelas Service untuk logika bisnis kompleks
    *   `Providers/`: Service Providers Laravel
*   `resources/`: Aset dan view
    *   `views/`: Blade templates
    *   `js/`: File JavaScript
    *   `css/`: File CSS
*   `database/`: Konfigurasi database
    *   `migrations/`: File migrasi
    *   `seeders/`: File seeder
*   `routes/`: Definisi rute aplikasi (web.php, api.php)
*   `config/`: File konfigurasi Laravel
*   `public/`: File yang dapat diakses publik (assets, index.php)
*   `storage/`: File yang diunggah, cache, log
*   `tests/`: Unit dan fitur tes

## Manajemen State
*   **Session:** Untuk data pengguna yang bersifat sementara (keranjang, login).
*   **Database:** Untuk data persisten (produk, pesanan, pengguna).

## Autentikasi & Otorisasi
*   **Laravel Sanctum/Passport:** Untuk autentikasi API (jika ada).
*   **Middleware Role/Permission:** Untuk kontrol akses berdasarkan peran pengguna (Admin, Customer).

## Pola Desain yang Digunakan
1.  **MVC:** Arsitektur utama.
2.  **Service Pattern:** Memisahkan logika bisnis dari Controller.
3.  **Repository Pattern (Opsional):** Abstraksi akses data.
4.  **Dependency Injection:** Untuk injeksi dependensi di Service dan Controller.

## Alur Request
1.  Request masuk ke `routes/web.php` atau `routes/api.php`.
2.  Router mengarahkan request ke Controller yang sesuai.
3.  Controller memanggil Service atau Model untuk logika bisnis.
4.  Controller mengembalikan response (View untuk web, JSON untuk API).

## Deployment
*   **Server:** Nginx/Apache
*   **Environment:** `.env` file untuk konfigurasi environment-specific.
*   **Docker (Opsional):** Untuk containerization dan deployment yang konsisten.
