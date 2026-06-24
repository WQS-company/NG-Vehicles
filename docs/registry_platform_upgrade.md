# Nigeria National Vehicle Registry Upgrade

## Summary
This upgrade extends the existing vehicle registry into a more complete national platform by adding advanced identity fields, history modules, and search capabilities.

## What changed
- Updated `vehicles` schema to support:
  - RFID tag
  - QR code
  - national registry vehicle status
  - variant, production date, assembly plant
  - origin and manufacture country
  - body-type and technical specifications (engine capacity, horsepower, torque, seating, dimensions)
  - soft-delete and active-state management
- Added advanced history modules:
  - `vehicle_accidents`
  - `service_records`
  - `insurance_policies`
  - `theft_reports`
  - `recall_notifications`
  - `inspection_records`
  - `modification_history`
  - `gps_track_points`
  - `import_history`
  - `valuation_records`
  - `vehicle_documents`
  - `vehicle_photos`
- Improved global search support for:
  - RFID tag
  - QR code
  - VIN, Engine, Chassis, Plate
  - Owner name, Phone, NIN, BVN
- Updated migration runner to execute all files in `migrations/` sequentially.

## Files changed
- `database.sql`
- `migrations/002_add_registry_extensions.sql`
- `scripts/run_migrations.php`
- `App/Models/Vehicle.php`
- `App/Controllers/VehicleController.php`
- `App/views/search/index.php`
- `App/views/vehicles/view.php`

## Deployment notes
1. Backup your database.
2. Run `php scripts/run_migrations.php` from the project root.
3. Verify new columns are present in `vehicles` and extended registry tables exist.
4. If you use a fresh database, load `database.sql` to create the new schema.

## Next implementation steps
- Add UI pages for service history, insurance, inspections, theft and recall management.
- Add driver authentication and role-based access rules for registry officers.
- Add API endpoints for vehicle trace, document upload, and GPS tracking ingestion.
- Add dashboard analytics for stolen vehicles, inspection status, and recall compliance.
