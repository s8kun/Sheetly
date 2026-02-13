<<<<<<< HEAD
# 📚 Sheetly - Student Resource Platform

Sheetly is a modern web application built with Laravel 12 that allows students to upload, search, and download study materials (Sheets, Midterms, and Finals) for their academic subjects.

## 🚀 Features

- **Smart Search:** Autocomplete search for subjects by code or name.
- **Role-based Access:** Standard users can upload resources; Admins moderate content.
- **Cloud Storage:** Integrated with Cloudinary for secure and fast PDF hosting.
- **Automated Formatting:** Subject codes are automatically standardized (Uppercase).
- **Security:** Protected by Laravel Sanctum and custom Middleware.

## 🛠 Tech Stack

- **Backend:** Laravel 12 (PHP 8.4)
- **Database:** MySQL / SQLite
- **Authentication:** Laravel Sanctum
- **Storage:** Cloudinary (External CDN)
- **Testing:** Pest PHP

## ⚙️ Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/sheetly.git
   cd sheetly
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Make sure to configure your `CLOUDINARY_URL` and database settings in `.env`.*

4. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

5. **Start the server:**
   ```bash
   php artisan serve
   ```

## 📖 API Documentation

Detailed API documentation is available in the [api.md](./api.md) file.

## 🧪 Testing

Run the test suite using Pest:
```bash
php artisan test
```

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
=======
# Sheetly
>>>>>>> 7bd85ef18c69ed12068f07e1f3aea687f0af800d
