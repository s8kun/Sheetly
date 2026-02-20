<div align="center">
  <img src="https://laravel.com/img/logomark.min.svg" alt="Sheetly Logo" width="100">
  <h1>📚 Sheetly</h1>
  <p><strong>The Ultimate Student Resource Platform for University of Benghazi</strong></p>

[![Laravel](https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
<br>
<a href="README.ar.md"><strong>النسخة العربية (Arabic Version)</strong></a> |
<a href="api.md"><strong>API Documentation</strong></a>

</div>

---

## 📖 Introduction

**Sheetly** is a high-performance resource-sharing platform designed specifically for students at the University of Benghazi. It streamlines the process of accessing academic materials like lecture sheets, midterm exams, and final papers.

This repository contains the **Laravel API Backend**. The user interface is built as a separate [Next.js Frontend Client](https://github.com/s8kun/sheetly).

## ✨ Core Features

- 🔍 **Intelligent Search:** Real-time subject lookup by code (e.g., CS101) or name.
- 🛡️ **Moderation Workflow:** All student uploads are held in a `pending` state until verified by an Admin.
- ☁️ **Cloud Infrastructure:** Secure PDF hosting powered by Cloudinary CDN.
- 🔒 **Secure Auth:** Enterprise-grade security using Laravel Sanctum (SPA/Mobile ready).
- 📊 **Download Tracking:** Live counters for resource popularity.

## 🏗 System Architecture & Workflow

1. **Upload:** Students upload PDFs via the `/sheets/upload` endpoint.
2. **Processing:** Files are stored on Cloudinary; a `pending` record is created in MySQL.
3. **Moderation:** Admins review the content via a dedicated moderation queue.
4. **Delivery:** Once approved, resources become visible to all students via the subject-specific chapters.

---

## 🛠 Technical Stack

| Category         | Technology                           |
| :--------------- | :----------------------------------- |
| **Framework**    | [Laravel 12](https://laravel.com)    |
| **Language**     | [PHP 8.4](https://php.net)           |
| **Database**     | MySQL                                |
| **File Storage** | [Cloudinary](https://cloudinary.com) |
| **Testing**      | [Pest PHP](https://pestphp.com)      |
| **Auth**         | Laravel Sanctum                      |

---

## ⚙️ Quick Start

### 1. Requirements

- PHP 8.4+
- Composer
- Node.js & NPM

### 2. Installation

```bash
# Clone the repository
git clone https://github.com/s8kun/Sheetly.git
cd Sheetly

# Install dependencies
composer install && npm install

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 3. Database & Seeding

```bash
# Run migrations and seed the database with professional dummy data
php artisan migrate:fresh --seed
```

### 4. Docker (Alternative)

If you prefer using Docker, you can start the entire stack with a single command:

```bash
docker-compose up -d --build
```

The application will be available at `http://localhost:8080`.

### 5. Running the Application

```bash
# Start the backend (Local)
php artisan serve
```

---

## 🧪 Testing & Quality

We maintain high standards through rigorous testing.

```bash
# Run all tests
php artisan test --compact

# Run specific feature tests
php artisan test --filter=SheetTest
```

## 🤝 Contribution

Contributions are welcome! Please feel free to submit a Pull Request.

---

<div align="center">
  Made with ❤️ for UOB Students
</div>
