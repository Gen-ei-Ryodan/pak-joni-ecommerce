1. Brand

Endpoint

GET /api/brands

Data:

✅ id
✅ name
✅ slug
✅ description
✅ categories[]
✅ cover_url
✅ logo_url
✅ theme_bg
✅ theme_accent
✅ theme_text
✅ theme_text2
✅ contacts[]
✅ show_in_menu
✅ created_at
2. Detail Brand

Endpoint

GET /api/brands/slug/{slug}

Contoh

/api/brands/slug/wmoto

Data:

✅ Nama
✅ Banner
✅ Theme
✅ Logo
✅ Description

3. Daftar Kategori

Endpoint

GET /api/brands/{slug}/products/facets

Contoh

/api/brands/wmoto/products/facets

Data

✅ categories[]

4. Daftar Produk

Endpoint

GET /api/brands/{slug}/products

Parameter

per_page
page
category
series
vehicle_class
sort

Data

✅ id
✅ slug
✅ name
✅ price
✅ discount
✅ category
✅ series
✅ cover_url
✅ OTR
✅ specs
✅ variants[]

5. Detail Produk

Endpoint pastinya belum terlihat pada potongan file, tetapi halaman detail jelas membutuhkan object Product yang berisi:

Informasi Dasar
✅ id
✅ name
✅ slug
✅ brand_name
✅ brand_slug
✅ short_description
✅ description
✅ cover_url
✅ price
✅ discount
✅ otr

6. Gallery
gallery[]

Berisi

✅ Gallery biasa
✅ Viewer 360

Field

type
url
sort_number

Jenis

gallery
viewer_360

7. Viewer 360

Per Variant

viewer360[]

Berisi

✅ frame1
✅ frame2
✅ frame3
...

Digunakan langsung oleh komponen 360°.

8. Variant

Setiap motor memiliki

✅ id
✅ name
✅ color
✅ image_path
✅ viewer360[]
✅ sort_number

9. Warna

Setiap variant

✅ Nama warna
✅ Hex Color

Contoh

Magic Grey
Freedom Yellow
10. Spesifikasi
Engine
✅ Engine Type
✅ Capacity
✅ Power
✅ Torque
✅ Compression
✅ Bore Stroke
✅ Clutch
Dimension
✅ Panjang
✅ Lebar
✅ Tinggi
✅ Wheelbase
✅ Ground Clearance
✅ Weight
✅ Fuel Capacity
Chassis
✅ Front Suspension
✅ Rear Suspension
✅ Front Brake
✅ Rear Brake
✅ Wheel
✅ Front Tire
✅ Rear Tire
✅ ABS/CBS
✅ TCS

Struktur keseluruhan
Brand
│
├── Categories
│
├── Products
│     │
│     ├── Name
│     ├── Slug
│     ├── Price
│     ├── Discount
│     ├── OTR
│     ├── Description
│     ├── Cover
│     │
│     ├── Gallery
│     │      ├── Gallery Images
│     │      └── Viewer360 Images
│     │
│     ├── Variants
│     │      ├── Name
│     │      ├── Color
│     │      ├── Image
│     │      └── Viewer360
│     │
│     │
│     └── Specs
│            ├── Engine
│            ├── Dimensions
│            └── Chassis