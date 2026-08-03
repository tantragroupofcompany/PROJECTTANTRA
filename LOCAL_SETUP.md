# TANTRA GROUP OF INDUSTRIES — Local Setup Guide

## Prerequisites

- Node.js 20+ (v24 recommended)
- npm or pnpm
- PostgreSQL (or use Prisma Postgres local server)

## Installation

```bash
# 1. Install dependencies
npm install

# 2. Start Prisma Postgres local server (in a separate terminal)
npx prisma dev

# 3. Configure environment variables
# Copy the DATABASE_URL from the prisma dev output into .env
# Example:
# DATABASE_URL="postgres://postgres:postgres@localhost:51214/template1?sslmode=disable&connection_limit=10&connect_timeout=0&max_idle_connection_lifetime=0&pool_timeout=0&socket_timeout=0"

# 4. Run database migration
npx prisma migrate dev --name init

# 5. Generate Prisma client
npx prisma generate

# 6. Seed the database (creates founder user + leadership profiles)
npx tsx prisma/seed.ts

# 7. Start the development server
npm run dev
```

## Access the Application

Open http://localhost:3000

### Public Pages
- `/` — Home
- `/about` — About Us
- `/companies` — Companies
- `/contact` — Contact

### Corporate Portal
- `/corporate/login` — Email OTP Login
- `/corporate/dashboard` — Dashboard Overview
- `/corporate/companies` — Company Management
- `/corporate/website-builder` — Website Builder
- `/corporate/leadership` — Leadership Management
- `/corporate/employees` — Employee Management
- `/corporate/media` — Media Library
- `/corporate/branding` — Branding Settings
- `/corporate/reports` — Reports Center
- `/corporate/analytics` — Analytics Center
- `/corporate/security` — Security Center
- `/corporate/profile` — User Profile
- `/corporate/audit-logs` — Audit Logs

## Authentication (Email OTP)

1. Enter your registered corporate email
2. System sends a 6-digit OTP to your email
3. OTP expires in 5 minutes
4. Enter OTP to access the dashboard

### Founder Account
- **Email**: jadavnileshbhai2006@gmail.com
- **Role**: Founder

### Authorized Roles
- Founder
- Chairman
- CEO & Managing Director

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DATABASE_URL` | PostgreSQL connection string | Required |
| `SMTP_HOST` | SMTP server for OTP emails | smtp.gmail.com |
| `SMTP_PORT` | SMTP port | 587 |
| `SMTP_USER` | SMTP username | - |
| `SMTP_PASS` | SMTP password | - |
| `SMTP_SECURE` | Use TLS | false |
| `SMTP_FROM` | Sender email | no-reply@tantragroup.com |

## Database Tables

- `users` — Corporate users with roles
- `companies` — Portfolio companies
- `leadership_profiles` — Founder/CEO/Chairman profiles
- `media_library` — Uploaded media assets
- `branding_settings` — Global branding configuration
- `audit_logs` — Immutable audit trail
- `sessions` — Server-side sessions
- `otp_verifications` — Email OTP codes
- `websites` — Subsidiary websites
- `pages` — Website pages
- `departments` — Company departments
- `employees` — Employee records
- `approvals` — Approval workflow
- `notifications` — User notifications
- `reports` — Generated reports
- `assets` — Company assets
- `documents` — Corporate documents
- `compliance_records` — Compliance tracking
- `ai_insights` — AI-generated insights
- `search_index` — Global search index
- `projects` — ERP projects
- `tasks` — Project tasks

## Troubleshooting

### Prisma Client Initialization Error
If you see "PrismaClient was instantiated without any options", ensure:
1. `@prisma/adapter-pg` is installed
2. `DATABASE_URL` is set in `.env`
3. Run `npx prisma generate`

### Database Connection Error (P1001)
1. Ensure Prisma Postgres is running: `npx prisma dev`
2. Verify the port in `DATABASE_URL` matches the prisma dev output

### OTP Email Not Received
1. Check the terminal console — in dev mode, OTPs are logged
2. Configure SMTP credentials in `.env` for production email delivery

### Build Errors
```bash
npm run build
```
Fix any TypeScript errors, then rebuild.

## Production Build

```bash
npm run build
npm start
```

## Git Repository

https://github.com/tantragroupofcompany/PROJECTTANTRA