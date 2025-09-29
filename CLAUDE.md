# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Technology Stack

This is a **Laravel 10** e-commerce platform with PHP 8.3+ using **Filament v2** for admin interface and **Laravel Sail** for Docker-based development.

**Key packages:**
- **Filament v2** - Admin panel for content management
- **Laravel Scout + Meilisearch** - Full-text search
- **Laravel Sanctum** - API authentication
- **Intervention Image** - Image processing
- **DomPDF** - PDF generation
- **Maatwebsite Excel** - Excel import/export
- **Laravel Localization** - Multi-language support
- **Spatie Media Library** - File/image management

**Frontend:** Vite + TailwindCSS + Alpine.js + Blade templates

## Development Commands

**Laravel Sail (preferred for local development):**
```bash
./vendor/bin/sail up -d              # Start Docker environment
./vendor/bin/sail artisan migrate    # Run migrations
./vendor/bin/sail pest               # Run tests
./vendor/bin/sail pint               # Code style check
./vendor/bin/sail bin duster lint    # Lint code
./vendor/bin/sail bin duster fix     # Fix linting issues
./vendor/bin/sail mysql -u sail -p password cetronics < backup.sql  # Import DB dump
./vendor/bin/sail artisan storage:link  # Link storage
```

**Alternative (without Docker):**
```bash
php artisan test                     # Run tests
php artisan migrate                  # Run migrations
npm run dev                          # Frontend development build
npm run build                        # Production build
composer install                     # Install dependencies
```

## Architecture Overview

**Domain-Driven Design Approach:**
- **Actions/** - Business logic operations (Order, Cart, User management)
- **Services/** - Business logic layer (BrandService, CartService, ItemService)
- **DataObjects/** - DTOs for clean data handling
- **Observers/** - Event-driven model behavior

**Core Models:**
- `User` - Customers with brand associations and custom discounts
- `Item` - Products with metadata and complex pricing logic
- `Order/OrderItem` - Order processing system
- `Cart/CartItem` - Shopping cart with persistent state
- `Brand/Category` - Product organization
- `Location` - Geographic/warehouse management

**Key Features:**
- Multi-language support via Laravel Localization
- Complex pricing system with user-specific discounts
- Brand-based access control for users
- PDF catalog generation
- Customer statistics and reporting
- Filament admin panel with custom widgets

## Testing

Uses **Pest** framework with SQLite in-memory database for tests.
- Feature tests for end-to-end functionality
- Unit tests for isolated components
- Static analysis with PHPStan/Larastan (Level 5)

## Setup Process

1. Copy `.env.dev` to `.env`
2. Install dependencies: `composer install`
3. Start Docker: `./vendor/bin/sail up -d`
4. Import database dump (get from admin)
5. Copy product images to `storage/app/public/*` (get from admin)
6. Run migrations: `./vendor/bin/sail artisan migrate`
7. Link storage: `./vendor/bin/sail artisan storage:link`

## Development Notes

- Project uses **French** as primary language for UI and comments
- Filament admin panel available at `/admin`
- MailPit for email testing at `http://localhost:8025`
- Database accessible at `localhost:3306`
- CI/CD pipeline deploys to Laravel Forge on main branch pushes
- Uses Laravel Horizon for queue monitoring