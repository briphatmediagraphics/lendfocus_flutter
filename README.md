# LandFocus Flutter app

The public LandFocus app for Android, web, and Windows. It uses the LandFocus
Laravel API and MySQL database. Flutter does not connect directly to MySQL and
does not use Supabase.

## Requirements

- Flutter stable and Dart
- XAMPP MySQL with the `landfocus_local` schema
- The Laravel project in `C:\xampp\htdocs\lendfocus\laravel`

## Start the backend

1. Start MySQL in the XAMPP Control Panel.
2. In PowerShell, run the Laravel API using the existing XAMPP PHP:

   ```powershell
   cd C:\xampp\htdocs\lendfocus\laravel
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```

3. Confirm the API is available at `http://localhost:8000/api/public/services`.

The Laravel API uses the `.env` database connection. Keep database credentials
in Laravel's environment file; never add them to this Flutter project.

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

For a physical phone, replace `10.0.2.2` with the computer's LAN IP address,
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
in the existing MySQL `inquiries` and `appointments` tables.
