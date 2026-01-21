📦 SIATK: Sistem Informasi Manajemen ATK
SIATK
SIATK adalah platform manajemen inventaris aset lancar (ATK) yang dirancang untuk mendigitalisasi proses pengadaan, pendistribusian, dan monitoring stok di lingkungan organisasi. Sistem ini memastikan setiap pergerakan barang tercatat secara transparan dan akuntabel.

🌟 Fitur Utama
Otomatisasi Distribusi: Saat barang baru didaftarkan, sistem secara otomatis mengalokasikan slot stok ke setiap bagian/seksi (melalui logic firstOrCreate di level database).

Multi-Role Access Control:

Finance Admin: Mengelola data master barang, stok masuk, dan distribusi global.

Bagian/Seksi: Memantau stok yang tersedia khusus untuk bagiannya sendiri.

Manajemen Stok Real-Time: Perhitungan stok yang akurat menggunakan database transaction untuk mencegah duplikasi data.

Reporting: Export data inventaris untuk kebutuhan pelaporan internal instansi.

🏗️ Arsitektur Data
Sistem ini menggunakan relasi database yang dinormalisasi untuk menjaga integritas data:

Barangs: Master data alat tulis (Kode, Nama).

Bagians: Master data unit kerja di SIATK DIY (Survey and Mapping, Rights Determination, dll).

Gudangs: Tabel pivot yang menyimpan jumlah stok per barang per bagian.

🛠️ Tech Stack
Backend: Laravel 10

Admin Panel: Filament v3 (TALL Stack)

Database: MySQL 8.0

Language: PHP 8.4

UI Component: Blade, Livewire, & Tailwind CSS

⚙️ Instalasi Proyek
Ikuti langkah berikut untuk menjalankan SIATK di lingkungan lokal:

Clone Repository

Bash

git clone https://github.com/username/siatk.git
cd siatk
Install Dependencies

Bash

composer install
npm install && npm run build
Environment Setup

Bash

cp .env.example .env
php artisan key:generate
Atur konfigurasi database di file .env.

Database Migration & Seeding

Bash

php artisan migrate --seed
Run Server

Bash

php artisan serve
📖 Alur Kerja Sistem (Workflow)
Input Barang: Admin memasukkan barang baru di menu Barang.

Auto-Gudang: Sistem menjalankan logic di GudangResource untuk membuat record stok 0 di seluruh seksi yang terdaftar secara otomatis.

Update Stok: Admin dapat menambah atau memperbarui stok barang yang kemudian akan terakumulasi secara otomatis di tabel Gudang.

🏢 Instansi
SIATK

Mau saya bantu buatkan bagian "Technical Documentation" yang menjelaskan logic handleRecordCreation yang kita bahas tadi untuk dokumentasi pengembang?