SaveEat (MVP)

Overview
SaveEat is a PHP 8 MVC web application that helps reduce food waste by allowing vendors to sell surplus food to consumers at discounted prices. This MVP includes role-based authentication (admin/vendor/consumer), item listings, cart/checkout (simulated payment), vendor approval, and reporting basics.

Prerequisites
- XAMPP (Apache + MySQL)
- PHP 8+
- Composer (optional, not required for MVP)

Quick Start
1) Move this folder into your XAMPP htdocs directory (or configure a virtual host):
   - Example: C:\xampp\htdocs\saveeat

2) Create a MySQL database and import migrations:
   - Create DB `saveeat`
   - Import file: scripts/db_migrations.sql

3) Configure environment:
   - Copy config/app.example.php to config/app.php and adjust settings (baseUrl, appName)
   - Copy config/config.example.php to config/config.php and set DB credentials

4) Set Apache DocumentRoot to `public` or access via http://localhost/saveeat/public
   - Ensure `public/.htaccess` is enabled (AllowOverride All in Apache config)

5) Login as Admin:
   - After migrations, an admin user is seeded:
     email: admin@saveeat.local
     password: Admin@123

Folders
- public/          Front controller, assets, .htaccess
- app/Core/        Core framework classes (Router, Controller, View, Model, DB, Auth, Session, CSRF, Validator, Uploader, Helpers)
- app/Controllers/ Application controllers
- app/Models/      Data models
- app/Views/       Templates and views
- config/          App and DB configs
- storage/         Logs and uploads (writeable by web server)
- scripts/         SQL migrations and seeds

Notes
- This MVP uses a simple in-memory route table and basic MVC. No external packages required.
- All forms include CSRF tokens and server-side validation.
- Images are stored in /public/uploads by default for easy serving. Validate MIME and size.

License
MIT