# 🚀 Sheetly API Documentation

Welcome to the official API documentation for **Sheetly**, a specialized platform for managing and sharing academic resources at the University of Benghazi.

---

## 🔐 Authentication & Security

The API uses **Laravel Sanctum** for secure authentication.

| Feature | Details |
| :--- | :--- |
| **Auth Type** | Bearer Token (Sanctum) |
| **Header** | `Authorization: Bearer {your_token}` |
| **Email Domain** | Restricted to `@uob.edu.ly` |
| **Base URL** | `https://your-domain.com/api` |

---

## 📌 Public Endpoints

### 🔑 Authentication Flow
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/register` | Register with student name, university email, and password. |
| `POST` | `/register/verify` | Verify email using the 4-digit OTP code sent to your inbox. |
| `POST` | `/resend-otp` | Resend the verification OTP to your university email. |
| `POST` | `/login` | Authenticate and receive a Bearer token. |
| `POST` | `/forgot-password` | Request a password reset link (sent to email). |
| `POST` | `/reset-password` | Submit new password using the token received via email. |

### 🏛 Academic Catalog
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/subjects` | List all subjects. Supports searching by `search` query parameter. |
| `GET` | `/subjects/{code}` | Get subject profile (organized into Chapters, Midterms, and Finals). |
| `GET` | `/subjects/{code}/chapters/{num}` | Get all approved sheets for a specific chapter number. |
| `GET` | `/sheets/{id}` | View metadata for a specific sheet. |
| `GET` | `/sheets/{id}/download` | Increment download counter and receive the secure file URL. |

---

## 👤 User Operations (Requires Auth)

All requests below must include the `Authorization` header.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/logout` | Revoke the current access token. |
| `GET` | `/my-sheets` | List all resources uploaded by the authenticated user (including pending). |
| `POST` | `/sheets/upload` | Upload a new PDF resource. |
| `DELETE` | `/sheets/{id}` | Delete a resource (Owner only, or Admin). |

---

## 🛠 Administration (Admin Only)

These endpoints are protected by the `admin` middleware.

### 📄 Moderation
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/admin/sheets/pending` | View all resources awaiting approval. |
| `PATCH` | `/admin/sheets/{id}/approve` | Approve a sheet to make it public. |
| `PATCH` | `/admin/sheets/{id}/reject` | Mark a sheet as rejected. |

### 🏛 Subject Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/admin/subjects` | Create a new academic subject. |
| `PATCH` | `/admin/subjects/{id}` | Update subject details (Name/Code). |
| `DELETE` | `/admin/subjects/{id}` | Delete a subject. |

### 👥 User Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/admin/users` | List all registered users (Paginated). |
| `POST` | `/admin/users` | Create a new user (Verified by default). |
| `PATCH` | `/admin/users/{id}` | Update user details or change roles. |
| `DELETE` | `/admin/users/{id}` | Remove a user account. |

---

## 📤 Request Specifications

### Resource Upload (`POST /sheets/upload`)
**Content-Type:** `multipart/form-data`

| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `title` | `string` | Yes | Max 255 characters. |
| `subject_id` | `integer` | Yes | Valid ID from the subjects table. |
| `type` | `enum` | Yes | `chapter`, `midterm`, `final`. |
| `chapter_number` | `integer` | No | If omitted for `chapter` type, it increments automatically. |
| `file` | `file` | Yes | PDF only, Max 10MB. |

---

## 🌐 HTTP Status Codes

| Code | Meaning |
| :--- | :--- |
| `200 OK` | Request successful. |
| `201 Created` | Resource created successfully. |
| `401 Unauthorized` | Invalid or missing Bearer token. |
| `403 Forbidden` | Insufficient permissions (Admin required) or unverified email. |
| `422 Unprocessable` | Validation error (check fields and error messages). |
| `429 Too Many Requests` | Rate limit exceeded (OTP/Login attempts). |

---
<div align="center">
  Built with Laravel 12 & ❤️ for UOB Students
</div>
