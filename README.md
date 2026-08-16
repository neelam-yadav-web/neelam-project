# Neelam's Fast Food — Demo Website

This repository now includes a simple fast-food website using PHP, HTML, CSS, and JavaScript, with a MySQL backend.

Quick setup:

1. Create a MySQL database (example name: fastfood_db).
2. Run the SQL script: `mysql -u root -p fastfood_db < sql/create_tables.sql`
3. Edit `includes/db.php` and set DB_HOST, DB_USER, DB_PASS, DB_NAME (or use environment variables in production).
4. Serve the site with a PHP-enabled server. Example (from repo root):
   php -S 0.0.0.0:8000

Pages:
- `index.php` — main menu and cart UI
- `order.php` — receives checkout POST and writes orders + items to DB
- `includes/db.php` — DB connection helper
- `assets/` — CSS and JS

Notes:
- Replace placeholder DB credentials before deploying.
- Images are loaded from Unsplash using topic queries (no stored assets).
- This is a demo starter. For production, harden inputs, use prepared statements everywhere, CSRF protections, and move secrets to environment variables.

