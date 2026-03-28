# Technical Documentation

## 1. Project Overview
- **Project Name:** Larament Boilerplate (as per composer.json and README.md, though customized for an e-commerce/retail use case).
- **Purpose and Problem it Solves:** This system is a comprehensive e-commerce and retail management platform. It facilitates customer ordering, inventory management, order fulfillment by drivers, tracking returns, issue and receipt notes (for accounting/stocking), and push notifications. It solves the problem of managing end-to-end retail operations, from customer interactions to logistics (drivers) and backend accounting.
- **Target Users:**
  - **End-Users (Customers):** Who browse products, add to carts, place orders, and manage their profiles.
  - **Drivers:** Who receive tasks, deliver orders, handle cash settlements, and manage returns.
  - **Administrators/Accountants:** Who manage inventory, issue/receipt notes, supplier balances, tracking performance, and sending notifications.
- **Domain:** E-commerce / Retail / Logistics (Delivery).
- **Maturity Level:** Production-ready / Enterprise-grade. It features a robust database schema with extensive relations, comprehensive API/Web routes, a fully structured admin panel using Filament, and integration with real-time notifications (WebPush).

## 2. Architecture Analysis
- **High-level Architecture:** Monolithic architecture with a modular internal structure. The application follows a Domain-Driven Design (DDD) like approach through Service classes handling core business logic, separated from Controllers.
- **Architectural Patterns:**
  - **MVC (Model-View-Controller):** Standard Laravel pattern.
  - **Service Pattern:** Extensive use of Service classes (`CartService`, `OrderServices`, `PlaceOrderServices`, `StockServices`, etc.) to encapsulate business logic.
  - **Repository/Query Scope Pattern:** Use of Eloquent scopes for query encapsulation (e.g., `scopeAssignableToDrivers`, `scopeNeedsIssueNote` in `Order`).
- **Frontend / Backend Separation:** The project uses Laravel Inertia.js with React for the frontend (customer-facing application) and Filament PHP (Livewire) for the backend/admin panel. Thus, it's a "hybrid" approach where the backend serves a Single Page Application (SPA) frontend via Inertia.
- **API Style:** RESTful structure for standard web routes with Inertia responses. The API is partially defined in `api.php` (e.g., search) and partially in `web.php` serving JSON or Inertia responses.

## 3. Technology Stack
- **Backend Technologies:** PHP 8.2+, Laravel 11, Filament PHP 3.
- **Frontend Technologies:** React 18, Inertia.js, Tailwind CSS 3, Radix UI components (via Shadcn/ui-like setup).
- **Database:** SQLite (default in `.env.example`, but likely intended for MySQL/PostgreSQL in production given the complex schema and transactions). Eloquent ORM is used heavily.
- **DevOps & Infrastructure:** Vite for frontend asset bundling, Composer for PHP dependencies, NPM for JS dependencies.
- **Realtime Technologies:** WebPush (via `laravel-notification-channels/webpush`), Laravel Reverb (configured in `composer.json` but `BROADCAST_CONNECTION` is set to `log` in `.env.example`).

## 4. Codebase Structure
- **Folder Structure:**
  - `app/Http/Controllers/`: Handles incoming requests (Inertia/Web).
  - `app/Models/`: Eloquent models defining relationships and attributes.
  - `app/Services/`: Contains all the core business logic, keeping controllers thin.
  - `app/Filament/`: Contains the admin panel configurations (Resources, Pages, Widgets).
  - `resources/js/Pages/`: Contains React components for the Inertia frontend.
  - `database/migrations/`: Extensive database schema definitions.
- **Entry Points:** `public/index.php` (standard Laravel), `routes/web.php` for HTTP routing.
- **Configuration Management:** Standard Laravel `.env` and `config/` directory.

## 5. Feature Extraction (CRITICAL)

### Authentication & Authorization
- **Customer Authentication:** Registration with phone number verification (OTP via `OtpService`), Login, Password Reset. Handled in `app/Http/Controllers/Auth/`.
- **Admin/Driver Authentication:** Filament-based login. Authorization uses Spatie Permission (Roles/Permissions) and custom Filament logic (`canAccessPanel` in `User` model).

### User Management
- **Customers:** Profile management, address/location tracking, wishlists, and loyalty points (`rating_points`).
- **Drivers:** Tracking driver accounts, tasks (`DriverTask`), balances (`DriverBalanceTracker`), and cash settlements.
- **Admins:** Managed via Filament.

### Core Business Logic
- **Product Catalog:** Categories, Brands, Products with attributes (packet/piece prices), Limits per area (`ProductLimit`), and Inventory tracking (`StockItem`).
- **Cart System:** Adding items, updating quantities, validating availability against stock, handled by `CartService`.
- **Order Processing:** Placing orders (`PlaceOrderServices`), applying offers (`OfferService`), validating minimum order totals, and deducting stock.
- **Returns & Cancellations:** Handling returned items (`ReturnOrderItem`), cancelled items, and updating driver accounts accordingly.

### Logistics & Fulfillment
- **Driver Tasks:** Assigning orders to drivers.
- **Issue Notes:** Documents issued to drivers for delivery.
- **Receipt Notes:** Documents acknowledging receipt of goods/cash from drivers.
- **Cash Settlements:** Tracking cash collected by drivers.

### Inventory & Accounting
- **Stock Management:** Tracking items in warehouses (`StockCountingServices`), handling purchase invoices, and tracking supplier balances.
- **Expenses & Assets:** Tracking business expenses and fixed assets.

### Notifications
- **Notification Manager:** A robust system (`NotificationManager`, `NotificationService`) for sending WebPush and Database notifications to customers based on templates (`OrderTemplate`). Tracks read/click counts.

## 6. Data Layer
- **Schema Overview:** Highly relational. Key tables: `users`, `customers`, `products`, `orders`, `order_items`, `driver_tasks`, `issue_notes`, `receipt_notes`, `stock_items`, `notifications_manager`.
- **Migration System:** Standard Laravel migrations with over 80 migration files.
- **Query Patterns:** Heavy use of Eloquent relationships (e.g., `hasOneThrough`, `belongsToMany`), eager loading (`with()`), and query scopes (`scopeNotCancelled`).
- **Transactions:** Critical operations (e.g., placing orders, updating cart) use `DB::transaction()` to ensure atomicity.

## 7. API Design
- **Endpoints:**
  - `GET /products/{product}`: View product.
  - `POST /cart`: Add item to cart.
  - `POST /orders`: Place order.
  - `POST /notifications/{id}/read`: Mark notification read.
- **Authentication:** Session-based (Inertia) with `auth:customer` and `auth` middleware.
- **Request/Response:** Inertia responses for page loads, JSON responses for async actions (e.g., `placeOrder`).
- **Error Handling:** Standard Laravel exception handling, returning 422 for validation errors or custom messages caught in `try-catch` blocks.

## 8. Concurrency & State Management
- **Concurrency:** Uses `DB::transaction()` extensively in service classes to handle concurrent requests and maintain data consistency (e.g., placing orders, modifying carts).
- **State Management:** Frontend uses React context/hooks within Inertia pages. Backend relies on the database for persistent state.

## 9. Security Analysis
- **Authentication:** Sessions for web, hashed passwords. OTP verification for customer phone numbers.
- **Authorization:** Spatie Permission for RBAC in the admin panel. Custom `canAccessPanel` logic.
- **Validation:** Laravel Request validation rules are strictly enforced (e.g., in `RegisterController`).
- **Protection:** Standard Laravel CSRF and XSS protection.

## 10. Performance & Scalability
- **Optimizations:** Eager loading in queries to prevent N+1 issues. Vite prefetching configured.
- **Bottlenecks:** Calculating net totals and complex stock queries might become slow with large datasets if not properly indexed.
- **Scaling:** The service-oriented architecture allows for easier refactoring and scaling.

## 11. DevOps & Deployment
- **Build Process:** Vite (`npm run build`) compiles React and Tailwind assets.
- **CI/CD:** GitHub Actions workflows configured for Pint, Pest, and PHPStan (as per `README.md`).
- **Observability:** Laravel Telescope included in dev dependencies for monitoring.

## 12. Code Quality Assessment
- **Consistency:** High consistency. Clear separation of concerns (Controllers vs. Services).
- **Design Patterns:** Effective use of Domain services, Observers (`OrderObserver`), and Notification Channels.
- **Test Coverage:** Pest tests are configured and mentioned in the README.

## 13. Risks & Weaknesses
- **Tight Coupling:** Some controllers still have fat `try-catch` blocks that could be fully delegated to services.
- **Database Engine:** Using SQLite in `.env.example` might lead to concurrency issues if deployed without changing to MySQL/Postgres.
- **Real-time Configuration:** Broadcasting is set to `log` by default; requires proper setup (e.g., Reverb/Pusher) for production.

## 14. Improvement Recommendations
- **Refactoring:** Extract complex logic in `Order` model attributes (e.g., `calculateNetQuantity`) into a dedicated calculator service or value object.
- **Architectural:** Implement a caching layer for frequently accessed data like categories, brands, and active products using Redis.
- **Security:** Ensure robust rate limiting on OTP sending and login attempts.
- **Performance:** Add database indexes to frequently queried columns (e.g., status fields, foreign keys) if not already present.
