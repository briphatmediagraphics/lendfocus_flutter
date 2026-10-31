# LandFocus Flutter app

The public LandFocus app for Android, web, and Windows. It uses the LandFocus
Laravel API and its project-local SQLite database. Flutter does not connect to
the database directly and
does not use Supabase.

## Requirements

- Flutter stable and Dart
- PHP with the PDO SQLite extension (included with the supplied XAMPP PHP build)
- The Laravel backend in `C:\xampp\htdocs\lendfocus_flutter\backend`

## Start the backend

1. In PowerShell, install and configure the Laravel backend:

   ```powershell
   cd C:\xampp\htdocs\lendfocus_flutter\backend
   composer install
   Copy-Item .env.example .env
   New-Item -ItemType File database\database.sqlite
   C:\xampp\php\php.exe artisan key:generate
   C:\xampp\php\php.exe artisan migrate --seed
   ```

2. Start the Laravel API:

   ```powershell
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```

3. Confirm the API is available at `http://localhost:8000/api/public/services`.

Laravel stores local data in `backend/database/database.sqlite`; no XAMPP
database service or credentials are needed. Keep `backend/.env` local and do
not add secrets to Flutter source.

## Run the app

```powershell
cd C:\xampp\htdocs\lendfocus_flutter
flutter pub get
flutter run
```

The default API base URL is `http://10.0.2.2:8000/api` on the Android emulator
and `http://localhost:8000/api` on Windows and web. Override it when the server
is hosted elsewhere:

```powershell
flutter run -d android --dart-define=LANDFOCUS_API_URL=http://10.0.2.2:8000/api
flutter run -d chrome --dart-define=LANDFOCUS_API_URL=http://localhost:8000/api
flutter run -d windows --dart-define=LANDFOCUS_API_URL=http://localhost:8000/api
```

For a physical phone, use `--dart-define=LANDFOCUS_API_URL=http://<PC-LAN-IP>:8000/api`,
allow port 8000 through the Windows firewall, and use HTTPS for production.
Flutter web calls the API from the browser; Laravel enables CORS for its API
routes. Configure a specific allowed origin before exposing a production API.

## Public API

- `GET /api/public/properties?type=land&limit=24`
- `GET /api/public/properties/{id}`
- `GET /api/public/services`
- `POST /api/public/inquiries`
- `POST /api/public/appointments`

Public submissions are validated and rate limited by Laravel. They are stored
in the Laravel-managed SQLite `inquiries` and `appointments` tables.
