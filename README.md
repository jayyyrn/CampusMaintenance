# 🔧 Campus Maintenance Tracking and Inventory System

A centralized, web-based system for campus maintenance requests, equipment tracking, and inventory management — built with **Laravel 11**, **Tailwind CSS**, and **Alpine.js**.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📖 Overview

The **Campus Maintenance Tracking and Inventory System** replaces manual, paper-based maintenance workflows with a unified digital platform.

Teachers submit maintenance requests, supervisors assign technicians, technicians record diagnoses and repairs, and the inventory officer controls the release of materials — all tracked in one connected system with full accountability.

### The Problem
- Manual reporting (phone calls, walk-ins, paper tickets)
- No centralized record of maintenance history
- Difficult to track request status and material usage
- Previous repair history is hard to find
- Risk of unauthorized part replacement

### The Solution
A single system where **every maintenance request and material transaction can be tracked from beginning to end**.

---

## ✨ Features

### 🔐 Authentication & Roles
- Secure login with username + password
- Six distinct roles, each with scoped permissions:
  - **Teacher** — Submits and tracks requests
  - **Department Coordinator** — Monitors department requests
  - **Technician** — Inspects, diagnoses, and repairs equipment
  - **Lead Technician / Supervisor** — Verifies major/questionable diagnoses
  - **Inventory Officer** — Manages and releases maintenance materials
  - **System Administrator** — Manages users, roles, and system settings

### 📋 Maintenance Request Management
- Submit requests with category, priority, location, photo evidence
- Real-time status tracking: `Pending → Review → Assigned → In Progress → For Verification → Completed`
- Filter by status, category, priority
- Full request history per equipment

### 🛠️ Task Board (Project-Management Style)
- Kanban columns: **Pending · In Progress · For Review · Completed**
- Supervisors can assign **any** technician (not limited to skill)
- Technicians update status — teachers see it instantly
- Priority sorting (urgent → low)

### ⏱️ Public Queue
- Anyone logged in can see active requests in order
- Grouped by category (electrical, carpentry, aircon, plumbing, etc.)
- Prevents duplicate requests for the same problem
- Shows queue position (#1, #2, ... #10) so requesters know their turn

### 🩺 Diagnosis & Verification
- Technicians record findings, recommended actions, materials needed, step-by-step solution
- Supervisor verifies major or questionable diagnoses
- All diagnoses become documentation for future reference

### 🤖 AI Knowledge Assistant
- Learns from every saved diagnosis
- Ask questions like *"How to fix aircon not cooling?"*
- Returns matched findings, materials, and step-by-step solution
- Reduces training time for new technicians
- Answers based on real past repairs, not generic advice

### 📦 Inventory Management (LGU-based Flow)
- Track all materials, spare parts, and consumables
- Stock-in, stock-out, returns, and adjustments
- Material request → **Supervisor approval** → **Inventory Officer release**
- Low-stock monitoring with minimum threshold alerts
- Complete transaction log per item

### 🔔 Notifications
- In-app alerts for:
  - New request submissions
  - Technician assignments
  - Status changes
  - Material approvals and releases
  - Low-stock warnings

### 📊 Dashboards
- **Teacher:** My requests, pending, completed
- **Technician:** Task counts, active tasks list
- **Inventory Officer:** Total items, low stock, pending requests
- **Admin:** Total requests, active, completed, low-stock summary

### 📜 Audit Trail
- Every important action logged: who did what, when, from which IP
- Viewable by admin under **Admin → Audit Logs**

### 🔒 Security
- Password hashing (bcrypt)
- Role-based middleware on every route
- CSRF protection on all forms
- Session management with regenerate on login
- Separation of duties (e.g., technicians can't release materials themselves)

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 11 (PHP 8.2) |
| **Frontend** | Blade Templates, Tailwind CSS 3, Alpine.js |
| **Database** | MySQL 8.0 |
| **Auth** | Laravel Breeze |
| **Build Tool** | Vite |
| **Version Control** | Git / GitHub |

---

## 📂 Project Structure

```
campus-maintenance/
├── app/
│   ├── Helpers/
│   │   └── helpers.php               # notify(), audit(), generate_request_code()
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── RequestController.php
│   │   │   ├── TaskController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── AiAssistantController.php
│   │   │   ├── QueueController.php
│   │   │   ├── NotificationController.php
│   │   │   └── AdminController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Department.php
│       ├── Equipment.php
│       ├── MaintenanceRequest.php
│       ├── TaskAssignment.php
│       ├── Diagnosis.php
│       ├── Inventory.php
│       ├── MaterialRequest.php
│       ├── StockTransaction.php
│       ├── Notification.php
│       └── AuditLog.php
├── database/
│   ├── migrations/                   # 12 migration files
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── auth/login.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── requests/
│   │   ├── tasks/
│   │   ├── inventory/
│   │   ├── assistant/
│   │   ├── queue/
│   │   ├── notifications/
│   │   └── admin/
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   └── web.php
└── README.md
```

---

## 🚀 Installation

### Prerequisites

- **PHP** 8.2 or higher ([Download](https://windows.php.net/download))
- **Composer** ([Download](https://getcomposer.org/download))
- **Node.js** 18+ ([Download](https://nodejs.org))
- **MySQL** 8.0+ ([Download](https://dev.mysql.com/downloads/installer/)) or **XAMPP** ([Download](https://www.apachefriends.org/))
- **Git** ([Download](https://git-scm.com/download/win))

### Setup Steps

**1. Clone the repository**
```bash
git clone https://github.com/YOUR-USERNAME/campus-maintenance.git
cd campus-maintenance
```

**2. Install dependencies**
```bash
composer install
npm install
```

**3. Set up environment**
```bash
copy .env.example .env
php artisan key:generate
```

**4. Create the database**

Using XAMPP Shell or MySQL:
```sql
CREATE DATABASE campus_maintenance
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

**5. Configure `.env`**

Open `.env` and set:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_maintenance
DB_USERNAME=root
DB_PASSWORD=
```

**6. Run migrations and seed demo data**
```bash
php artisan migrate --seed
```

**7. Create storage symlink**
```bash
php artisan storage:link
```

**8. Build frontend assets**
```bash
npm run build
```

**9. Start the server**
```bash
php artisan serve
```

**10. Open in browser**

👉 **http://127.0.0.1:8000**

---

## 👤 Demo Accounts

All demo accounts use password: **`password123`**

| Username | Role | Access |
|----------|------|--------|
| `admin` | System Administrator | Full access, users, audit logs |
| `teacher1` | Teacher | Submit and track requests |
| `teacher2` | Teacher | Submit and track requests |
| `coordinator1` | Department Coordinator | Monitor and assign department requests |
| `tech1` | Technician (Electrical) | Task board, diagnoses, material requests |
| `tech2` | Technician (Carpentry) | Task board, diagnoses, material requests |
| `lead1` | Lead Technician / Supervisor | Verify diagnoses, assign tasks |
| `inventory1` | Inventory Officer | Stock control, material release |

---

## 🔄 System Workflow

```
Report → Review → Assign → Inspect → Diagnose
   ↓
Verify When Needed → Repair → Verify → Close
```

Each stage is tracked with timestamps, actors, and status changes — providing full transparency from problem report to resolution.

---

## 📡 API Endpoints (Highlights)

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Authenticate user |
| POST | `/logout` | Log out |

### Requests
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | `/requests` | All roles |
| POST | `/requests` | Teacher only |
| GET | `/requests/{id}` | All roles |
| POST | `/requests/{id}/assign` | Supervisor, Coordinator, Admin |
| POST | `/requests/{id}/status` | Supervisor, Technician |

### Tasks
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | `/tasks` | Technician, Lead Technician |
| POST | `/tasks/{id}` | Technician, Lead Technician |
| POST | `/tasks/{id}/diagnosis` | Technician, Lead Technician |

### Inventory
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | `/inventory` | All roles |
| POST | `/inventory/request` | Technician |
| POST | `/inventory/{id}/approve` | Supervisor, Inventory Officer |
| POST | `/inventory/{id}/release` | Inventory Officer |
| POST | `/inventory/stock-in` | Inventory Officer |

### AI Assistant
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/assistant` | Chat interface + knowledge base |
| POST | `/assistant/ask` | Ask a question |

---

## 🗺️ Roadmap (Future Enhancements)

- [ ] Native mobile application
- [ ] Push notifications
- [ ] QR code scanning for equipment
- [ ] Inventory forecasting with ML
- [ ] Advanced analytics and dashboards
- [ ] Cloud backup
- [ ] Equipment location tracking
- [ ] Offline mode
- [ ] Integration with other campus systems

---

## 🤝 Contributing

This is an academic project for **Integrative Programming and Technologies**.

### Team Workflow

1. **Pull before you start:**
   ```bash
   git pull
   ```

2. **Create a feature branch:**
   ```bash
   git checkout -b feature/your-feature
   ```

3. **Make changes, commit, and push:**
   ```bash
   git add .
   git commit -m "Add: your feature"
   git push origin feature/your-feature
   ```

4. **Open a Pull Request** on GitHub.

---

## 👥 Team

- **Arabes, Kher Justine G.**
- **Antesco, Arnie Jay O.**
- **Copat, Shaira C.**
- **Cagalawan, Jenny A.**
- **Genon, Giana Michaela B.**
- **Waminal, Yurrick Vann A.**

---

## 📄 License

This project is released under the **MIT License**.

---

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Alpine.js
- Laravel Breeze
- Our instructors and interviewees who provided valuable requirements input

---

## 📞 Contact

For questions, bug reports, or feature requests:
- Open an **Issue** on GitHub
- Or contact any team member listed above