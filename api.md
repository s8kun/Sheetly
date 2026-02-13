# 📚 Sheetly API Documentation

Welcome to the Sheetly API. This project provides a platform for students to share and access study materials (Sheets, Midterms, Finals).

## 🔐 Authentication
The API uses **Laravel Sanctum** for authentication.
- All protected routes require a `Bearer {token}` in the `Authorization` header.
- Roles: `user` (default), `admin`.

---

## 🏛 Subjects
Endpoints for managing and listing academic subjects.

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/subjects` | Public | List all subjects (supports `?search=`) |
| `GET` | `/api/subjects/{code}` | Public | Get subject details (chapters & exams) |
| `GET` | `/api/subjects/{code}/chapters/{num}` | Public | Get sheets for a specific chapter |
| `POST` | `/api/subjects` | **Admin** | Create a new subject |
| `PATCH/PUT` | `/api/subjects/{code}` | **Admin** | Update subject details |
| `DELETE` | `/api/subjects/{code}` | **Admin** | Delete a subject |

---

## 📄 Sheets
Endpoints for uploading, managing, and downloading sheets.

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/sheets/{id}` | Public* | Get sheet details |
| `GET` | `/api/sheets/{id}/download` | Public* | Get download URL and increment counter |
| `POST` | `/api/sheets/upload` | User | Upload a new sheet (PDF) |
| `GET` | `/api/my-sheets` | User | List sheets uploaded by the current user |
| `DELETE` | `/api/sheets/{id}` | Owner/Admin | Delete a sheet |

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

## 🔑 Authentication Endpoints

| Method | Endpoint | Access | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Public | Register a new user |
| `POST` | `/api/login` | Public | Login and receive a token |
| `POST` | `/api/logout` | User | Revoke the current token |

---

## 💡 Notes
- **Subject Codes:** All subject codes are automatically converted to **UPPERCASE** (e.g., `se311` becomes `SE311`).
- **File Storage:** PDFs are stored on **Cloudinary**.
- **Sheet Status:**
  - `pending`: Waiting for admin review.
  - `approved`: Visible to everyone.
  - `rejected`: Not visible to the public.
