# City Market

Online supermarket application for City Market in Tyre, Lebanon.

## Live Application

- Store: https://city-market-bi70.onrender.com/
- Products: https://city-market-bi70.onrender.com/products.php
- Admin login: https://city-market-bi70.onrender.com/admin/login.php
- Health check: https://city-market-bi70.onrender.com/health.php

## Authentication

The application currently uses username and password authentication for customers and administrators.

SSO is currently **not enabled**. Google, Microsoft, and GitHub OAuth are not configured in this version.

## Technology

- PHP 8.3+
- Apache
- MySQL-compatible database
- Railway MySQL database
- Render Docker Web Service

## Local Development

1. Start Apache and MySQL with XAMPP.
2. Create or import the `city_market_db` database.
3. Copy `.env.example` to `.env` and configure local database values.
4. Open `http://localhost/City-Market/`.

## Production Configuration

The Render service uses these environment variables:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
```

These values must point to the Railway MySQL public connection. Never commit `.env` or production database credentials.

## Database Migration

The migration utility copies the local XAMPP database to Railway:

```bash
php scripts/migrate_mysql.php --check
php scripts/migrate_mysql.php --confirm
```

`--check` tests both connections. `--confirm` replaces matching Railway tables and copies the local schema and data.

## Deployment

The project is configured for Render with:

- `Dockerfile`
- `render.yaml`
- `health.php`
- `DEPLOYMENT.md`

Push changes to the GitHub repository and trigger a Render deployment. After deployment, verify the health endpoint and test customer and admin workflows.

## Security Notes

- Store admin passwords using PHP `password_hash()`.
- Rotate database credentials if they are exposed.
- Keep production secrets only in Render environment variables or local `.env` files.
- Test checkout, inventory, sessions, and administrator actions before accepting live orders.
