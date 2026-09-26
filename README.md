# Ceylon Vacation Venues

A modern, mobile-first tourism and travel services platform developed for **Ceylon Vacation Venues**, Sri Lanka.

The platform provides tourism services including tour packages, villas and houses, vehicle rentals, visa extension assistance, baggage transport services, travel content, and customer inquiries through a fully manageable Laravel administration panel.

## Website

**Production Domain:**  
https://ceylonvacationvenues.com

---

## Project Overview

Ceylon Vacation Venues is designed as a complete tourism management and travel services website.

The platform consists of two main areas:

### Public Website

Visitors can:

- Explore tour packages
- Browse villas and houses
- Browse rental vehicles
- View visa extension services
- View baggage transport services
- Browse the gallery
- Read travel blog articles
- Submit service inquiries
- Contact the business through WhatsApp
- View business and contact information

### Administration Panel

Authorized administrators can manage:

- Tour Packages
- Package Itineraries
- Package Images
- Villas & Houses
- Property Types
- Amenities
- Property Images
- Vehicles
- Vehicle Categories
- Vehicle Images
- Customer Inquiries
- Visa Extension Inquiries
- Baggage Transport Inquiries
- Property Inquiries
- Gallery
- Blog Posts
- Blog Categories
- Website Pages
- SEO Metadata
- Redirects
- Website Settings
- Users
- Roles / Permissions
- Audit Logs

---

## Technology Stack

### Backend

- Laravel
- PHP 8.3+
- Laravel Eloquent ORM
- Laravel Blade

### Frontend

- Blade Templates
- Tailwind CSS
- Alpine.js
- Vite

### Database

- MySQL 8+

### Infrastructure

- Cloudflare compatible
- Apache / Nginx compatible
- Laravel public storage
- Git version control

---

## Main Modules

### Tour Packages

Administrators can manage:

- Package name
- Description
- Destination
- Duration
- Pricing
- Availability
- Itineraries
- Featured images
- Image galleries
- Featured packages
- Publishing status
- SEO information

Public URLs follow an SEO-friendly structure:

```text
/tour-packages
/tour-packages/{slug}
```

### Villas & Houses

Accommodation management for properties such as:

- Villas
- Houses
- Other configurable property types

Property information can include:

- Location
- Description
- Pricing
- Bedrooms
- Bathrooms
- Guest capacity
- Amenities
- Check-in / check-out
- Availability
- Featured image
- Gallery
- SEO information

Public URLs:

```text
/villas-houses
/villas-houses/{slug}
```

### Vehicle Rental

Supports configurable vehicle categories including:

- Cars
- Tuk Tuks
- Bikes

Vehicle information includes pricing, capacity, availability, images and other relevant specifications.

Public URLs:

```text
/vehicle-rental
/vehicle-rental/{slug}
```

### Visa Extension

Provides information about visa extension assistance and allows customers to contact or submit inquiries.

### Baggage Transport

Provides information and inquiry options for islandwide tourist baggage transportation services.

### Gallery

Administrators can manage tourism and business images including:

- Title
- Caption
- Alt text
- Category
- Display order
- Publishing status

### Blog

SEO-focused travel blog supporting:

- Categories
- Featured images
- Excerpts
- Articles
- Publishing dates
- Featured posts
- SEO metadata

Public URLs:

```text
/blog
/blog/{slug}
```

### Inquiry Management

The platform supports multiple inquiry types:

- Tour Package Inquiries
- Vehicle Rental Inquiries
- Villa / House Inquiries
- Visa Extension Inquiries
- Baggage Transport Inquiries
- General Contact Inquiries

Administrators can track and manage customer inquiries through the admin panel.

---

## User Management

The application includes role-based administrative access.

Current application roles include:

```text
Owner
Administrator
Editor
Inquiry Agent
```

Authorization is enforced server-side using Laravel authentication and authorization mechanisms.

---

## SEO

SEO is a core requirement of the platform.

The application architecture supports:

- SEO-friendly URLs
- Custom slugs
- Meta titles
- Meta descriptions
- Canonical URLs
- Open Graph metadata
- Social sharing images
- Robots directives
- XML sitemap
- robots.txt
- Breadcrumbs
- JSON-LD structured data
- Image alt text
- Semantic HTML
- 301 redirects
- Blog SEO
- Package SEO
- Property SEO
- Service-page SEO

Only published and indexable content should be included in search-engine-facing resources such as the XML sitemap.

---

## Performance

The application is designed with mobile performance and Core Web Vitals in mind.

Performance considerations include:

- Server-side rendering
- Optimized database queries
- Eager loading
- Database indexes
- Pagination
- Application caching
- Optimized images
- WebP support where applicable
- Responsive images
- Lazy loading
- Vite production assets
- Minimal client-side JavaScript

---

## Mobile Responsive Design

The entire application follows a mobile-first approach.

Layouts are designed for:

```text
320px+  Small Mobile
375px+  Mobile
390px+  Mobile
768px+  Tablet
1024px+ Laptop
Large Desktop
```

Both the public website and administration panel are responsive.

---

## Security

Security controls include:

- Laravel Authentication
- Role-based authorization
- Policies and middleware
- CSRF protection
- Server-side validation
- Form Request validation
- Login throttling
- Rate limiting
- Secure password hashing
- Mass-assignment protection
- IDOR protection
- XSS protection
- Safe database queries
- Secure file upload validation
- MIME type validation
- Randomized uploaded filenames
- Session security
- Audit logging
- Production-safe error handling

Sensitive credentials must never be committed to the repository.

---

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd ceylon-vacation-venues
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Create Environment File

```bash
cp .env.example .env
```

Configure your local database and other required services inside `.env`.

**Never commit `.env` to Git.**

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Build Frontend

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 9. Start Development Server

```bash
php artisan serve
```

Default local URL:

```text
http://127.0.0.1:8000
```

---

## Development Commands

Clear Laravel caches:

```bash
php artisan optimize:clear
```

Run migrations:

```bash
php artisan migrate
```

Run tests:

```bash
php artisan test
```

Run frontend development server:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

---

## Production Deployment

Production deployment should preserve:

- MySQL database
- `.env`
- Production secrets
- `storage/app/public` uploaded files

Never use the following command on a production database:

```bash
php artisan migrate:fresh
```

Apply production migrations using:

```bash
php artisan migrate --force
```

Production optimization:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Build frontend assets:

```bash
npm ci
npm run build
```

---

## Uploaded Media

Uploaded media is persistent application data.

Production deployments must **not delete**:

```text
storage/app/public
```

Laravel's public storage link should be configured using:

```bash
php artisan storage:link
```

Database records should store portable relative media paths rather than machine-specific absolute paths.

---

## Testing

Before production deployment, test:

- Authentication
- Authorization
- User roles
- Tour package CRUD
- Villa & house CRUD
- Vehicle CRUD
- Gallery
- Blog
- Inquiries
- File uploads
- SEO metadata
- Sitemap
- Redirects
- Validation
- Mobile responsiveness
- Security controls

Run:

```bash
php artisan test
```

---

## Project Structure

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
└── Services/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/
    ├── admin/
    ├── components/
    └── public/

routes/
├── web.php
└── console.php

storage/
└── app/
    └── public/
```

The actual structure may vary as the application evolves.

---

## Brand

**Business Name:** Ceylon Vacation Venues

**Website:** https://ceylonvacationvenues.com

The website follows a premium Sri Lankan tourism visual identity with a blue-focused brand palette and a clean, tropical, mobile-first design.

---

## Development

Developed as a custom Laravel tourism management platform.

### Developer

**WebX Tech Solutions**

---

## License

This is a custom commercial project developed for **Ceylon Vacation Venues**.

The application source code and project-specific assets are not intended for public redistribution unless explicitly authorized.