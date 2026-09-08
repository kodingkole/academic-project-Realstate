# Intern Estate — Project Documentation

## Overview

Intern Estate is a Laravel-based real-estate management system with three portals:

| Role | Main functions |
|---|---|
| Admin | Construction ERP, projects, legal/JV workflow, investors, documents, payments and audit log |
| Investor | Registration, unit reservation, payment ledger, documents and notifications |
| Landowner | Registration, land submission and approved-project/JV status |

## Technology

- Laravel 12 / PHP 8.2+
- MySQL
- Blade templates
- Vite for frontend assets
- PHPUnit feature tests

## Installation

```bash
composer install
npm install
```

Create `.env` from `.env.example`, then set the MySQL connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=intern_realstate
DB_USERNAME=root
DB_PASSWORD=
```

Generate the application key and database schema:

```bash
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Database import

The repository contains [database/intern_realstate.sql](database/intern_realstate.sql), a MySQL dump with schema and demo data.

To use it with phpMyAdmin:

1. Open phpMyAdmin.
2. Choose **Import**.
3. Select `database/intern_realstate.sql`.
4. Run the import.

Use either the SQL dump or a fresh `php artisan migrate --seed` setup; do not use `migrate:fresh` unless you intentionally want to erase local data.

## Main workflows

### Admin ERP

Admin-only ERP routes are under `/erp/*`.

- **Projects:** create projects, add milestones/tasks and update progress.
- **Inventory:** create materials and make IN/OUT/TRANSFER stock transactions.
- **Auto-reorder:** creates one pending purchase request for each material below its reorder level.
- **Procurement:** suppliers, purchase requests and purchase orders.
- **Workforce:** QR attendance and monthly payroll generation.
- **Finance:** project expenses.
- **Site operations:** daily reports, quality inspections, documents and equipment.

Inventory OUT transactions use a database transaction and a row lock. The application rejects the request if the requested quantity exceeds available stock.

### Landowner and legal/JV process

1. A landowner submits land information.
2. Admin assigns a lawyer; the submission becomes `under_review`.
3. Admin approves or rejects the submission.
4. Approval creates/reuses a project and creates/updates a draft JV agreement in one database transaction.

### Investor process

1. Investor registers with email, phone, NID, TIN and utility-bill information.
2. Investor selects an active/planned project and reserves an available unit.
3. Investor submits a payment request and may attach KYC documents.
4. Admin approves or rejects pending payments.
5. The investor sees payment status, ledger records, documents and notifications.

The current bKash, Nagad and bank-transfer endpoints record a simulated payment workflow. They validate input and persist transactions but do not call a live external payment gateway.

## Roles and access control

| Route area | Required access |
|---|---|
| `/portal/admin/*` | Authenticated `admin` |
| `/erp/*` | Authenticated `admin` |
| `/portal/investor/*` | Authenticated `investor` |
| `/portal/landowner/*` | Authenticated `landowner` |

The middleware files are:

- `app/Http/Middleware/EnsureUserRole.php`
- `app/Http/Middleware/EnsureErpModuleAccess.php`

## Important database tables

| Area | Tables |
|---|---|
| Users | `users`, `sessions`, `password_reset_tokens` |
| Construction | `projects`, `milestones`, `tasks`, `materials`, `inventory_transactions`, `expenses` |
| Procurement | `suppliers`, `purchase_requests`, `purchase_orders` |
| Workforce | `attendances`, `payrolls` |
| Legal/JV | `land_submissions`, `lawyers`, `jv_agreements` |
| Investor | `investor_bookings`, `investor_payments`, `investor_documents`, `investor_notifications` |
| Audit | `activity_logs` |

Uploaded PDF/image files are saved in Laravel storage. Their metadata and file paths are stored in MySQL.

## Key source files

| Feature | File |
|---|---|
| Routes | `routes/web.php` |
| ERP logic | `app/Http/Controllers/ErpController.php` |
| Project risk / auto-reorder | `app/Services/ErpIntelligenceService.php` |
| Landowner/legal workflow | `app/Http/Controllers/PortalController.php` |
| Investor dashboard | `app/Http/Controllers/InvestorDashboardController.php` |
| Investor registration | `app/Http/Controllers/InvestorAuthController.php` |
| Payment endpoints | `app/Http/Controllers/PaymentApiController.php` |
| Investor documents | `app/Http/Controllers/InvestorDocumentController.php` |
| Admin payment review | `app/Http/Controllers/AdminInvestorController.php` |
| Database schema | `database/migrations/` |

## JSON endpoints

All payment endpoints require authentication.

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/payments/bkash` | Record bKash-style payment |
| POST | `/api/payments/nagad` | Record Nagad-style payment |
| POST | `/api/payments/bank` | Record bank-transfer payment |
| GET | `/api/payments/verify/{transactionId}` | Check a transaction record |
| GET | `/api/notifications` | List notifications |
| POST | `/api/notifications/read-all` | Mark all notifications as read |
| POST | `/api/notifications/{id}/read` | Mark one notification as read |

## Testing

Run all feature tests:

```bash
php artisan test
```

Build production assets:

```bash
npm.cmd run build
```

## Notes for submission

- Submit the source code and `database/intern_realstate.sql` together.
- Import the SQL file before demonstrating the system on a new machine.
- Do not commit `.env`, uploaded personal KYC files or production credentials.
