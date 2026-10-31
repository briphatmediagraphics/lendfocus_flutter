# Software Requirements Specification (SRS)
## LandFocus Laravel Project

### 1. Introduction
This document describes the software requirements for the LandFocus Laravel web application. The system is a property and land services platform for LandFocus, a Tanzanian real estate and land consultancy company. The project provides public-facing pages for marketing properties and services, as well as an admin dashboard for managing listings, inquiries, appointments, tasks, workers, contracts, and notifications.

### 2. Purpose
The purpose of the system is to:
- Present LandFocus services, history, and property listings to the public.
- Allow visitors to request property information or appointments.
- Enable administrators to manage business operations efficiently.
- Support multilingual access in English and Swahili.

### 3. Scope
The system covers:
- Public website pages such as home, properties, services, about, history, contact, terms, book, and assistant.
- Property listing and detail pages.
- Inquiry and appointment submission from visitors.
- Admin dashboard for managing properties, calendar events, inquiries, tasks, notifications, workers, and contracts.
- Rule-based AI assistant for common customer questions.

### 4. Product Perspective
The application is a Laravel-based web platform intended to replace or extend the previous React/TanStack frontend while preserving the same business workflow. It is designed to run on a PHP/Laravel server with a relational database and optionally integrate with Supabase for property data.

### 5. Product Functions
The system shall provide the following major functions:
1. Public website navigation and content display.
2. Property browsing by type and status.
3. Property detail view.
4. Inquiry submission and appointment booking.
5. Admin authentication and dashboard access.
6. CRUD management of properties, events, inquiries, tasks, workers, contracts, and notifications.
7. Multilingual content switching.
8. Rule-based assistant responses to frequently asked questions.

### 6. Users
The primary users are:
- Visitors / prospective clients
- Administrators / staff members
- Property managers / team members

### 7. Functional Requirements
#### 7.1 Public Website
The system shall:
- Display the homepage with featured properties, announcements, gallery items, and services.
- Show a list of properties with filtering by property type.
- Display individual property details.
- Provide pages for services, about, history, contact, terms, and booking.
- Support language switching between Swahili and English.

#### 7.2 Inquiry and Booking
The system shall:
- Allow visitors to submit inquiries through the contact or booking forms.
- Capture customer details including name, email, phone, message, payment preference, and callback request.
- Store appointment or inquiry requests for admin review.

#### 7.3 Admin Dashboard
The system shall:
- Require admin authentication before granting access to protected pages.
- Provide an administrative dashboard with summary statistics.
- Allow admins to view and manage properties, inquiries, tasks, calendar events, workers, contracts, and notifications.
- Support adding, updating, deleting, and listing records through API endpoints.

#### 7.4 Assistant
The system shall:
- Provide a rule-based assistant page.
- Respond to common questions about services, contact information, office location, property availability, and booking.

### 8. Non-Functional Requirements
- The system should be secure, with admin-only access to management features.
- The application should be responsive and usable on desktop and mobile browsers.
- The solution should be maintainable and follow Laravel MVC architecture.
- The system should support deployment on XAMPP/Apache and standard Laravel hosting.
- Performance should be acceptable for moderate traffic and data volumes.

### 9. Constraints
- The project is built with Laravel 12 and PHP 8.2+.
- The database may be SQLite, MySQL, or another Laravel-supported relational database.
- Some data may be synchronized with Supabase when configured.

### 10. Assumptions and Dependencies
- The application depends on Laravel framework components and Composer packages.
- Media storage is handled through Laravel storage and public asset directories.
- External services such as Supabase and SMS integration may be optional depending on configuration.

### 11. Acceptance Criteria
The system will be considered successful when:
- Visitors can browse public pages and view property details.
- Customers can submit inquiries and booking requests.
- Administrators can log in and manage core business records.
- The assistant can answer common user questions.
- The application runs correctly under the configured development and deployment environment.

### 12. Conclusion
This SRS defines the core requirements for the LandFocus Laravel platform. It provides a practical blueprint for development, testing, and deployment of the system.
