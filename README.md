# WebDev Assignment - Upgraded Frontend & Fixes

This version includes:
- Clean professional design with gradients, animations, and improved UX.
- Fixed login/signup fetches to include credentials (cookies) so PHP sessions work reliably when served from the same origin.
- Slight UI tweaks for Blog, PDF and Scraper pages.

Important: Serve both `frontend/` and `backend/` from the same local webserver root (e.g., place the project folder inside `htdocs` for XAMPP). Then open `http://localhost/<project-folder>/frontend/index.html`.

If you still see 'Network error' messages:
- Ensure PHP is running and backend files are accessible (visit `http://localhost/<project-folder>/backend/auth/login.php` directly to test).
- Make sure `backend/config.php` database credentials are set and DB imported.

