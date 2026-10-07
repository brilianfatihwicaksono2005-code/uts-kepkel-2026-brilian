Markdown
# UTS Konstruksi dan Evolusi Perangkat Lunak 2026

Repositori ini berisi source code dan konfigurasi deployment untuk ujian tengah semester (UTS) mata kuliah Konstruksi dan Evolusi Perangkat Lunak. Proyek ini merupakan aplikasi full-stack untuk Sistem Manajemen Unit Rumah yang dibangun dengan pendekatan arsitektur modern berbasis container dan pipeline CI/CD.

## Informasi Mahasiswa

- Nama: Brilian Fatih Wicaksono
- NIM: 24/535871/SV/24320
- Program Studi: D-IV Teknologi Rekayasa Perangkat Lunak
- Institusi: Sekolah Vokasi, Universitas Gadjah Mada

## Teknologi yang Digunakan

Proyek ini dibangun menggunakan stack berikut:

- Frontend: Vue.js, di-serve melalui Nginx ringan
- Backend: Laravel RESTful API, dijalankan di atas FrankenPHP
- Database: MySQL 8.0
- Orkestrasi & Containerization: Docker dan Docker Compose dengan multi-stage build
- CI/CD & Manajemen Repositori: GitHub Actions, branch protection, dan GitHub Container Registry (GHCR)

## Struktur Proyek Utama

```text
.
├── .github/
│   └── workflows/          # Konfigurasi CI/CD pipeline (GitHub Actions)
├── backend/                # Source code Laravel + Dockerfile multi-stage FrankenPHP
├── frontend/               # Source code Vue.js + Dockerfile multi-stage Nginx
├── docker-compose.yml      # Orkestrasi multi-container
├── .dockerignore           # File yang dikecualikan dari proses build Docker
├── README.md               # Dokumentasi proyek
└── .gitignore              # File konfigurasi Git
```

## Cara Menjalankan Secara Lokal

Proyek ini sudah dikonfigurasi sepenuhnya menggunakan Docker Compose, sehingga Anda tidak perlu menginstal PHP, Node.js, atau MySQL secara manual di mesin lokal.

### Prasyarat

- Git
- Docker Desktop atau Docker Engine yang sudah aktif

### Langkah Instalasi

1. Clone repositori ini:

```bash
git clone https://github.com/brilianfatihwicaksono2005-code/uts-kepkel-2026-brilian.git
cd uts-kepkel-2026-brilian
```

2. Jalankan layanan menggunakan Docker Compose:

```bash
docker compose up -d --build
```

3. Tunggu beberapa saat hingga proses unduhan image dan build selesai.

4. Jalankan migrasi database:

```bash
docker exec -it uts_backend php artisan migrate
```

## Akses Endpoint Aplikasi

Setelah semua container berstatus `Up`, aplikasi dapat diakses melalui:

- Frontend (UI): http://localhost:8080
- Backend API: http://localhost:8000/api
- Database MySQL: localhost:3306

## CI/CD Pipeline dan GHCR

Repositori ini mengimplementasikan pipeline CI/CD otomatis menggunakan GitHub Actions. Setiap kali ada perubahan yang digabungkan ke branch `main`, workflow akan:

- menjalankan proses linting dan testing (jika tersedia)
- membangun image Docker terbaru dengan multi-stage build
- mempublikasikan image ke GitHub Container Registry (GHCR)

Image yang dipublikasikan dapat ditarik secara publik dengan perintah berikut:

```bash
docker pull ghcr.io/brilianfatihwicaksono2005-code/uts-kepkel-2026-brilian:latest
```

## Catatan

Dokumentasi ini dibuat untuk mempermudah pengelolaan, deployment, dan evaluasi proyek UTS secara konsisten di lingkungan lokal maupun CI/CD.