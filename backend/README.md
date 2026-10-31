# LandFocus Laravel Backend

This Laravel project is separated from the website and lives at `backend/` in
the Flutter workspace. It contains the API, database migrations, models, and
seed data. Local development uses a SQLite database file owned by this Laravel
project; MySQL/XAMPP is not required. The local `.env` file is not included.

## Requirements

- PHP 8.2 or newer
- Composer
- PDO SQLite PHP extension (included with the supplied XAMPP PHP build)

## Setup on Windows

From PowerShell:

```powershell
Set-Location C:\xampp\htdocs\lendfocus_flutter\backend
composer install
Copy-Item .env.example .env
```

Create the project-local SQLite file and run the migrations and seeders:

```powershell
New-Item -ItemType File database\database.sqlite
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
```

The SQLite file lives at `backend/database/database.sqlite` and is ignored by
Git. Keep `.env` local and do not commit application keys or payment provider
secrets. Production deployments should use a managed database and configure
its connection through environment variables.

## Current Public API

The existing Flutter app currently calls these routes:

- `GET /api/public/properties?type=land&limit=24`
- `GET /api/public/properties/{id}`
- `GET /api/public/services`
- `POST /api/public/inquiries`
- `POST /api/public/appointments`

The API host for the Android emulator is `http://10.0.2.2:8000/api`. For a
physical device, use the computer's LAN IP instead of `10.0.2.2` and allow
port 8000 through the Windows firewall. Web and Windows clients can use
`http://localhost:8000/api`.

Database migrations and seeders are in `database/migrations` and
`database/seeders`. The SQLite database is independent from any existing
XAMPP database; its tables and data are created by the Laravel migrations.
