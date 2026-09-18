# City Market deployment

## Render

This application is packaged as a Docker web service and expects a MySQL-compatible database. Render's native PostgreSQL service is not compatible with the current `mysqli` data layer.

1. Create a managed MySQL-compatible database, such as an external PlanetScale, Aiven, or Railway MySQL service.
2. Create a Render Web Service from this repository. Render will use `render.yaml` and the `Dockerfile`.
3. Add `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` as Render environment variables.
4. Import the existing City Market schema and seed categories/products/users/admins before accepting orders.
5. Confirm `/health.php` reports `{"status":"ok"}` and test login, cart, checkout, and admin workflows in a staging service first.

## Operational notes

- Keep the service on one instance until sessions are moved to a shared store. The current cart and login sessions use PHP's local session storage.
- Set `APP_ENV=production` in the hosting environment and keep PHP errors in server logs, never in browser responses.
- Store admin passwords with PHP `password_hash($password, PASSWORD_DEFAULT)`. The admin login expects the resulting hash in the `admins.password` column.
- Back up the database and configure a real email/payment provider before launch in Tyre.
