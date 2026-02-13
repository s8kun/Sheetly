<div align="center">
  <h1>🚀 Sheetly API Specification</h1>
  <p>Official API documentation for the Sheetly Resource Platform</p>
  
  [![Version](https://img.shields.io/badge/API-v1.0-blue?style=flat-square)](#)
  [![Auth](https://img.shields.io/badge/Auth-Sanctum-orange?style=flat-square)](#)
  <br>
  <a href="api.ar.md"><strong>النسخة العربية (Arabic Version)</strong></a>
</div>

---

## 🔐 Authentication
The API utilizes **Laravel Sanctum** for secure access. 
- **Header:** `Authorization: Bearer {token}`
- **Email Restriction:** Only `@uob.edu.ly` domains are permitted for registration.

---

## 📌 Endpoints Summary

### 🔑 Identity & Access
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Create a new student account |
| `POST` | `/api/login` | Authenticate and retrieve Bearer token |
| `POST` | `/api/logout` | Invalidate current session |

### 🏛 Subject Catalog
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/subjects` | List all subjects (Searchable) |
| `GET` | `/api/subjects/{code}` | Get subject profile (Chapters/Exams) |
| `GET` | `/api/subjects/{code}/chapters/{num}` | Get specific chapter sheets |

### 📄 Resource Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/sheets/{id}` | Detailed resource view |
| `GET` | `/api/sheets/{id}/download` | Secure download link generation |
| `POST` | `/api/sheets/upload` | Upload new PDF resource |
| `GET` | `/api/my-sheets` | Current user's upload history |
| `DELETE` | `/api/sheets/{id}` | Remove a resource |

---

## 🛠 Administration (Restricted)
These endpoints require an **Admin** role.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/admin/subjects` | Register a new academic subject |
| `PATCH` | `/api/admin/subjects/{code}` | Modify subject metadata |
| `GET` | `/api/admin/sheets/pending` | View moderation queue |
| `PATCH` | `/api/admin/sheets/{id}/approve` | Approve a resource for public view |
| `PATCH` | `/api/admin/sheets/{id}/reject` | Reject and hide a resource |

---

## 📤 Upload Specification
To upload a resource, send a `multipart/form-data` request to `/api/sheets/upload`:

| Field | Type | Required | Notes |
| :--- | :--- | :--- | :--- |
| `title` | `string` | Yes | Max 255 chars |
| `subject_id` | `int` | Yes | Valid Subject ID |
| `type` | `enum` | Yes | `chapter`, `midterm`, `final` |
| `chapter_number` | `int` | Conditional | Required if type is `chapter` |
| `file` | `file` | Yes | PDF only, Max 20MB |

---

## 🌐 Error Codes
| Code | Meaning |
| :--- | :--- |
| `200/201` | Success / Created |
| `401` | Unauthenticated (Missing/Invalid Token) |
| `403` | Forbidden (Insufficient Permissions) |
| `422` | Validation Error (Check request parameters) |

---
<div align="center">
  Built with Laravel 12 & ❤️
</div>
