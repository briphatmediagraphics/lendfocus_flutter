# LandFocus Project Plan and Feature Overview

## 1. Project Overview
LandFocus is a Laravel-based real-estate and land sales management system focused on property marketing, customer booking, payment tracking, support ticket handling, and admin operations. The system is designed to support a full operational workflow for land/property sales, including project maps, plot availability, booking holds, payments, customer support, and admin dashboards.

The system combines:
- Public property listing and project details
- Interactive map-driven plot visualization
- Customer booking and reservation flow
- Payment and receipt tracking
- Admin management dashboard
- Support ticket communication
- Notifications and operational reporting

## 2. Business Goal
The core business objective is to digitize and streamline land/property sales operations so that:
- Customers can view projects and plots online
- Admin staff can manage availability, bookings, payments, and communication
- Plot status is visible and controlled centrally
- Sales teams can operate with a clear booking workflow
- Support requests are tracked and resolved within the system
- Data is stored in a MySQL/Laravel environment instead of depending on third-party-only services

## 3. Main Users
### Public / Customer
- Browse properties and projects
- View available plots and pricing
- Reserve or hold a plot
- Make or track payments
- Submit support requests
- See notifications and booking progress

### Admin / Staff
- Manage projects and plot data
- View map and draw plot shapes
- Review bookings and reserve status
- Approve or reject payments
- Handle support tickets and replies
- Export reports and data
- Manage settings and operational configuration

### Sales / Operational Roles
- Manage customer booking workflow
- Verify payments and customer documentation
- Coordinate site visits and meetings
- Track tasks and staff responsibilities

## 4. Core Functional Modules

### 4.1 Public Website
Features:
- Home page
- Properties listing
- Services and about pages
- Contact page
- Testimonials and gallery
- Project and property detail pages
- Public support ticket form

### 4.2 Projects and Plots Management
Features:
- Create and manage projects
- Add project details: title, code, location, type, status, price
- Upload cover image and project metadata
- Manage plots under each project
- Track plot status: available, held, sold, reserved
- Map-based project overview with Leaflet integration
- Support for plot drawing and map shapes
- Plot export as CSV

### 4.3 Booking and Reservation Flow
Features:
- Reserve plot by customer action
- Hold bookings for a fixed period (e.g. 5 days)
- Set `held_until` timestamp for reservation expiry
- Mark plots as held during the reserved period
- Auto-release expired bookings via scheduled command
- Maintain customer and control information in booking records

### 4.4 Payment Workflow
Features:
- Create payment records against bookings
- Support multiple payment methods
- Stripe checkout integration
- Webhook-based payment confirmation
- Receipts and payment tracking
- Approval and verification workflow for payments

### 4.5 Support Tickets
Features:
- Public support ticket creation
- Customer ticket list and detail pages
- Ticket replies and threaded communication
- Admin support dashboard
- Notifications when tickets are created or replied to
- Tracking of support lifecycle and statuses

### 4.6 Admin Dashboard
Features:
- Dashboard overview statistics
- Bookings management
- Ticket management
- Payment management
- Notification center
- Projects and map management
- User/staff management
- Settings and configuration management
- Export data to CSV

### 4.7 Map Module
Features:
- Draw and save map shapes
- View project plots on an interactive map
- Zoom and select project layout
- Display plot markers and statuses
- Delete map shapes through AJAX-safe admin actions
- Map layers and shape persistence in project data

### 4.8 Notifications and Tasks
Features:
- In-app notifications for different events
- Task management for operations and staff
- Audit logs for critical actions
- Admin and sales communication tracking

## 5. Technical Architecture
### Stack
- Laravel (PHP framework)
- MySQL database
- Blade templates
- Tailwind CSS + Flowbite UI components
- Leaflet.js for maps
- Vite for frontend build pipeline
- Artisan scheduler for automated release/notification jobs

### Main Layers
- Route layer: web and API endpoints
- Controller layer: public, admin, booking, payment, support, map logic
- Model layer: User, Project, Plot, Booking, Payment, Ticket, Reply, Notification
- Migration layer: database schema management
- View layer: public pages and admin dashboard UI
- Scheduler: recurring release and cleanup logic

## 6. Key Data Models
### User
- Basic authentication data
- Role / admin flags
- Related profile, staff, bookings, payments, tickets

### Project
- id
- code
- title
- slug
- location
- type
- status
- base_price_tzs
- geojson / map shapes
- cover image metadata
- created_by

### Plot
- id
- project_id
- number
- size_sqm
- price_tzs
- status
- coordinates
- position_x / position_y
- notes

### Booking
- id
- plot_id
- customer_name
- customer_phone
- customer_id_no
- control_number
- price_tzs
- status
- held_until
- sales_officer_id

### Payment
- id
- booking_id
- method
- amount_tzs
- status
- reference
- verified_by
- paid_at

### Support Ticket
- id
- user_id
- subject / issue details
- status
- priority
- assigned_to
- ticket_number

### Support Ticket Reply
- id
- ticket_id
- user_id
- message
- created_at

## 7. Booking Lifecycle
1. Customer views property/project and plot
2. Customer reserves a plot
3. System creates booking with status `held`
4. Plot is marked as `held`
5. `held_until` is set to a future time
6. Customer pays or proceeds to checkout
7. Payment is verified via Stripe or manual flow
8. Booking becomes `confirmed` or `paid`
9. If expired without payment, system releases the hold and resets plot status

## 8. Auto-Release Logic
The system includes a scheduled job to automatically release expired held bookings.

Typical behavior:
- Find all bookings with status `held`
- Check if `held_until` is in the past
- Set booking status to `expired` or `released`
- Update plot status back to available/open
- Trigger notification to the relevant user/admin

## 9. Admin Workflow
The admin workflow supports:
- Login and admin-only access
- Dashboard overview
- Booking review and management
- Ticket review and customer communication
- Payment verification
- Project and plot management
- Map updates and shape control
- System settings and notifications

## 10. Deployment and Environment Strategy
### Local Development
- Use local Laravel + MySQL environment
- Configure `.env` with local DB credentials
- Use `php artisan migrate` and `php artisan db:seed`
- Run frontend compile via Vite
- Use `php artisan schedule:work` for local task testing

### Live Deployment
- Use production env values
- Keep `.env` and secrets out of source control
- Configure live DB and Stripe webhooks
- Ensure scheduled jobs run in hosting environment
- Keep backups and versioned deployment process

## 11. Immediate Roadmap
### Phase 1: Stability and Setup
- Fix local DB environment
- Ensure migrations run cleanly on fresh database
- Verify environment config and app key
- Validate routes and login flows

### Phase 2: Core Sales Flow
- Confirm project, plot, and booking statuses
- Validate customer reserve flow and 5-day hold
- Confirm property-to-project linking for public detail pages

### Phase 3: Payment and Support
- Verify Stripe integration and webhook flow
- Test ticket creation and reply workflow
- Confirm admin ticket inbox operations

### Phase 4: Admin UX and Reporting
- Review admin dashboard workflows
- Add or refine CSV exports and operational reporting
- Improve dashboards, filters, and notifications

### Phase 5: Production Hardening
- Secure admin access and roles
- Add user role enforcement
- Configure cron/scheduler in live environment
- Test failure handling and notification automation

## 12. Risks and Considerations
- Database migrations must be run in correct order
- FKs with mismatched UUID vs BigInt types can break MySQL schema creation
- Live secrets must never be committed to source control
- Stripe webhooks require correct live endpoint configuration
- Scheduler must be active in production for hold expiry automation
- Admin checks should eventually be moved to proper role-based authorization instead of session-only checks

## 13. Summary
This project is a complete property sales and operations platform with a strong admin workflow, customer-facing site, map-based plot visibility, booking hold logic, payment processing, and support ticket management. Its goal is to provide a modern land/property business system that can manage the entire customer journey from viewing a project to reserving a plot, confirming payment, and resolving issues through admin operations.

## 14. Suggested Next Actions
- Complete migration verification on local DB
- Test a full reserve → hold → payment → payment verification flow
- Confirm admin login and admin routes work correctly
- Review public property pages with linked project plots
- Validate notifications, ticket replies, and scheduler behavior
- Prepare staging or production deployment checklist
