# TANTRA GROUP OF INDUSTRIES — Corporate Ecosystem

## Overview

Corporate management system for TANTRA GROUP OF INDUSTRIES, including public-facing pages, company management, branding, media library, authentication, and dashboard.

## Tech Stack

- Next.js 16 (App Router, Turbopack)
- React 19
- TypeScript 5
- Tailwind CSS 4
- Prisma 7 with SQLite (local) / PostgreSQL (production)
- bcryptjs for password hashing
- NextAuth-ready session architecture

## Quick Start

```bash
# 1. Install dependencies
npm install

# 2. Start local database (SQLite is used by default)
# .env is preconfigured with: DATABASE_URL="file:./prisma/dev.db"

# 3. Run migrations and seed
npx prisma migrate dev --name init
npx prisma db seed

# 4. Start dev server
npm run dev
```

Open http://localhost:3000

## Scripts

- `npm run dev` — Start development server
- `npm run build` — Production build
- `npm run lint` — Run ESLint
- `npx prisma generate` — Generate Prisma Client
- `npx prisma migrate dev --name <name>` — Create/apply migration
- `npx prisma db seed` — Seed database

## Environment

See `.env.local` and `.env`.

Key variables:
- `DATABASE_URL` — SQLite file path for local development
- `NEXTAUTH_URL` — App URL
- `NEXTAUTH_SECRET` — Session secret
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD` — Email settings

## Documentation

- `LOCAL_SETUP.md` — Detailed setup instructions
- `prisma/schema.prisma` — Database schema

## Repository

https://github.com/tantragroupofcompany/PROJECTTANTRA