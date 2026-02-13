# 📚 Sheetly API Documentation

Welcome to the Sheetly API. This project provides a platform for students to share and access study materials (Sheets, Midterms, Finals).

[النسخة العربية (Arabic Version)](api.ar.md)

## 🔐 Authentication
The API uses **Laravel Sanctum** for authentication.
- All protected routes require a `Bearer {token}` in the `Authorization` header.
- Roles: `user` (default), `admin`.

---

## 🔑 Authentication Endpoints

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Public | Register a new user (`name`, `email`, `password`, `password_confirmation`) |
| `POST` | `/api/login` | Public | Login and receive a token (`email`, `password`) |
| `POST` | `/api/logout` | User | Revoke the current token |

> **Note:** Registration is restricted to emails ending with `@uob.edu.ly`.

---

## 🏛 Subjects
Endpoints for managing and listing academic subjects.

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/subjects` | Public | List all subjects (supports `?search=` for name or code) |
| `GET` | `/api/subjects/{code}` | Public | Get subject details (chapters, midterms, finals) |
| `GET` | `/api/subjects/{code}/chapters/{num}` | Public | Get sheets for a specific chapter number |
| `POST` | `/api/admin/subjects` | **Admin** | Create a new subject (`name`, `code`) |
| `PATCH/PUT` | `/api/admin/subjects/{code}` | **Admin** | Update subject details |
| `DELETE` | `/api/admin/subjects/{code}` | **Admin** | Delete a subject |

---

## 📄 Sheets
Endpoints for uploading, managing, and downloading sheets.

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/sheets/{id}` | Public* | Get sheet details |
| `GET` | `/api/sheets/{id}/download` | Public* | Get download URL and increment counter |
| `POST` | `/api/sheets/upload` | User | Upload a new sheet (PDF, max 20MB) |
| `GET` | `/api/my-sheets` | User | List sheets uploaded by the current user |
| `DELETE` | `/api/sheets/{id}` | Owner/Admin | Delete a sheet |

### 📤 Upload Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `title` | `string` | Yes | Title of the sheet |
| `subject_id` | `integer` | Yes | ID of the subject |
| `type` | `enum` | Yes | `chapter`, `midterm`, `final` |
| `chapter_number` | `integer` | Conditional | Required if `type` is `chapter` |
| `file` | `file` | Yes | PDF file (max 20480 KB) |

> \* Public access is limited to `approved` sheets. Owners and Admins can access `pending` sheets.

---

## 🛠 Admin Moderation
Exclusive endpoints for administrators.

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/admin/sheets/pending` | **Admin** | List all sheets waiting for approval |
| `PATCH` | `/api/admin/sheets/{id}/approve` | **Admin** | Approve a pending sheet |
| `PATCH` | `/api/admin/sheets/{id}/reject` | **Admin** | Reject a pending sheet |

---

## 💡 Notes
- **Subject Codes:** All subject codes are automatically converted to **UPPERCASE** (e.g., `se311` becomes `SE311`).
- **File Storage:** PDFs are stored on **Cloudinary**.
- **Sheet Status:**
  - `pending`: Waiting for admin review.
  - `approved`: Visible to everyone.
  - `rejected`: Not visible to the public.
