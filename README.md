

<div align="center">
  <svg width="80" height="80" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
    <path clip-rule="evenodd" fill-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="#EEDC82" />
  </svg>
  <h1>MoviView</h1>
  <p><i>Platform Review Film Interaktif</i></p>
</div>

<br />

# MoviView Backend API 🎬

Backend REST API untuk aplikasi **MoviView**, dibangun menggunakan **Laravel 12**. API ini mengintegrasikan data film dari **The Movie Database (TMDB)** dan menyediakan fitur manajemen ulasan pengguna.

## ✨ Fitur Utama

- **Autentikasi Pengguna**: Register & Login menggunakan Laravel Sanctum.
- **Integrasi TMDB**: Mengambil data film populer, rating tertinggi, detail film, dan pencarian film.
- **Sistem Review**: Pengguna dapat memberikan rating dan ulasan pada film.
- **Review Saya**: Melihat daftar ulasan pribadi lengkap dengan detail film (poster, judul, rating).
- **Localization**: Mendukung Bahasa Indonesia (ID).

## 🚀 Teknologi

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (Default)
- **API External**: TMDB API

## 🛠️ Instalasi

1. **Clone repositori**

    ```bash
    git clone https://github.com/GindaAzahra/MoviView-Backend.git
    cd MoviView-Backend
    ```

2. **Install dependensi**

    ```bash
    composer install
    ```

3. **Setup environment**
   Salin `.env.example` menjadi `.env` dan konfigurasi key TMDB Anda.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Konfigurasi TMDB**
   Buka file `.env` dan tambahkan kredensial TMDB:

    ```env
    BASE_URL_TMDB=https://api.themoviedb.org/3
    API_KEY_TMDB=your_tmdb_api_key_here
    BASE_URL_IMAGE=https://image.tmdb.org/t/p/w500
    ```

5. **Migrasi Database**
   Pastikan file database (`database/database.sqlite`) sudah ada atau sesuaikan koneksi database Anda.

    ```bash
    php artisan migrate
    ```

6. **Jalankan Server**
    ```bash
    php artisan serve
    ```

## 📝 API Endpoints

### Autentikasi

| Method | Endpoint        | Deskripsi                      |
| :----- | :-------------- | :----------------------------- |
| POST   | `/api/register` | Pendaftaran pengguna baru      |
| POST   | `/api/login`    | Login dan mendapatkan token    |
| POST   | `/api/logout`   | Logout (Memerlukan Token)      |
| GET    | `/api/user`     | Profil user (Memerlukan Token) |

### Film (Movies)

| Method | Endpoint                   | Deskripsi                                         |
| :----- | :------------------------- | :------------------------------------------------ |
| GET    | `/api/movies/{type}`       | Ambil film berdasarkan `popular` atau `top_rated` |
| GET    | `/api/movie/{id}`          | Detail film berdasarkan ID TMDB                   |
| GET    | `/api/movies/search?q=...` | Mencari film berdasarkan judul                    |

### Ulasan (Reviews)

| Method | Endpoint                       | Deskripsi                                         |
| :----- | :----------------------------- | :------------------------------------------------ |
| GET    | `/api/reviews/movie/{movieId}` | Semua ulasan untuk satu film                      |
| GET    | `/api/reviews/{id}`            | Detail satu ulasan                                |
| POST   | `/api/reviews`                 | Tambah ulasan baru (Memerlukan Token)             |
| PUT    | `/api/reviews/{id}`            | Update ulasan (Milik sendiri)                     |
| DELETE | `/api/reviews/{id}`            | Hapus ulasan (Milik sendiri)                      |
| GET    | `/api/my-reviews`              | Daftar ulasan saya + data film (Memerlukan Token) |

## 🔗 Struktur Respons `my-reviews`

Respons ini sudah dioptimalkan untuk menyertakan detail film secara otomatis:

```json
{
    "status": "success",
    "data": [
        {
            "id_review": "uuid-string",
            "id_user": "user-uuid",
            "id_movie": "550",
            "rating": 9,
            "review": "Film yang sangat luar biasa!",
            "movie": {
                "original_title": "Fight Club",
                "poster_path": "https://image.tmdb.org/t/p/w500/...",
                "vote_average": 8.4
            }
        }
    ]
}
```