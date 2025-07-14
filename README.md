# Larament

Larament is a starter template for building projects with Laravel and FilamentPHP. It provides a solid foundation for developing modern, data-driven web applications with a focus on e-commerce and inventory management.

## Features

- **E-commerce Storefront:** A fully functional, customer-facing website for browsing products, managing carts, and placing orders.
- **Advanced Admin Panel:** A comprehensive admin panel built with FilamentPHP for managing products, categories, brands, orders, customers, and more.
- **Inventory Management:** A robust system for tracking stock levels, managing purchase invoices, and handling returns.
- **Reporting and Analytics:** Detailed reports on sales, products, customers, and other key metrics.
- **User Authentication:** Secure user registration, login, and profile management for both customers and administrators.
- **Role-Based Access Control:** Fine-grained permission control using FilamentShield.
- **Notifications:** A flexible notification system for keeping users informed about their orders and other important events.
- **Search:** Powerful full-text search functionality for quickly finding products.
- **Multi-language Support:** The application is designed to be easily translated into multiple languages.

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** TypeScript, React, Inertia.js
- **Admin Panel:** FilamentPHP 3
- **Database:** MySQL, PostgreSQL, or SQLite
- **Testing:** Pest
- **Code Styling:** Pint
- **Static Analysis:** Larastan

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/your-username/larament.git
   ```

2. **Install dependencies:**

   ```bash
   composer install
   npm install
   ```

3. **Set up your environment:**

   - Copy the `.env.example` file to `.env`:

     ```bash
     cp .env.example .env
     ```

   - Generate an application key:

     ```bash
     php artisan key:generate
     ```

   - Configure your database connection in the `.env` file.

4. **Run database migrations and seed the database:**

   ```bash
   php artisan migrate --seed
   ```

5. **Build frontend assets:**

   ```bash
   npm run build
   ```

6. **Start the development server:**

   ```bash
   php artisan serve
   ```

### Accessing the Admin Panel

The admin panel is located at `/admin`. You can log in with the default credentials:

- **Email:** `admin@example.com`
- **Password:** `password`

## Testing

To run the test suite, use the following command:

```bash
composer pest
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue.
