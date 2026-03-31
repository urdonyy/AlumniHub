<p align="center">
	<img src="public/images/alumnihub-logo.svg" width="140" alt="AlumniHub Logo">
</p>

<h1 align="center">AlumniHub</h1>

<p align="center">
	A social alumni platform for PUP-ITECH focused on meaningful connections, communities, and career growth.
</p>

## Overview

AlumniHub is a Laravel + Blade web application designed to connect alumni through profiles, communities, and post-driven engagement.

This project is built as a capstone-grade platform with clean architecture, readable code, and practical scalability in mind.

## Core Features

- Authentication and alumni profile management
- Alumni-specific registration fields (batch/year and program/course)
- Alumni verification flow using school documents (TOR or similar)
- Community/group participation
- Community post creation and discussion
- Flair/tag-based content categorization and filtering
- Extensible foundation for notifications, messaging, and admin tooling

## Tech Stack

- Backend: Laravel (PHP)
- Frontend: Blade templates + Tailwind CSS + Vite
- Database: MySQL (or any Laravel-supported SQL database)
- Testing: Pest / PHPUnit

## Project Structure

- `app/` - Controllers, Models, Requests, Providers
- `resources/views/` - Blade templates and UI components
- `routes/` - Web, auth, and console routes
- `database/migrations/` - Schema migration files
- `tests/` - Feature and unit tests

## Local Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials.

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Start development servers

```bash
php artisan serve
npm run dev
```

## Testing

Run the full test suite:

```bash
php artisan test
```

Run a specific test:

```bash
php artisan test --filter=RegistrationTest
```

## Branding Note

The README logo currently points to `public/images/alumnihub-logo.svg`.

If your logo file is in a different location, update the image path at the top of this README.

## License

This project is currently for academic/capstone use. Add your preferred license before public distribution.
