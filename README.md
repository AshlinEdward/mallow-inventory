# Mallow Inventory & Order Management API

A Laravel-based inventory and order management application developed as part of the Mallow Technologies Laravel Developer Mini Task.

## Features

- Product management with product code, unit price, tax percentage, and stock quantity.
- Customer management with unique email addresses.
- Order creation with multiple product items.
- Automatic subtotal, tax, and grand total calculation.
- Stock validation and deduction during order creation.
- Database transactions and row locking to help prevent overselling.
- Customer order history lookup by email.
- Low-stock product listing with a configurable threshold.
- Queued order confirmation job using application logs.
- Dashboard for viewing inventory, orders, and customers.

## Tech Stack

- PHP
- Laravel
- MySQL
- Laravel Blade, HTML, CSS, and JavaScript
- XAMPP
- PHPUnit / Laravel testing tools

## Requirements

- PHP and Composer
- MySQL
- XAMPP or another compatible local development environment

## Installation and Setup

1. Clone the repository:

   ```bash
   git clone <YOUR_PRIVATE_REPOSITORY_URL>
   cd mallow-inventory
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Create the environment file:

   ```bash
   copy .env.example .env
   ```

   If `.env` already exists, do not overwrite it.

4. Generate the application key:

   ```bash
   php artisan key:generate
   ```

5. Create a MySQL database named `mallow_inventory` in phpMyAdmin.

6. Configure the database settings in `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mallow_inventory
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Update the username and password if your MySQL configuration differs.

7. Run migrations and seed sample data:

   ```bash
   php artisan migrate --seed
   ```

8. Start the Laravel development server:

   ```bash
   php artisan serve
   ```

   Open `http://127.0.0.1:8000` in your browser.

## Queue Configuration

The application uses a queued job for simulated order confirmation.

Configure the database queue in `.env`:

```env
QUEUE_CONNECTION=database
```

Run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

After an order is created, check the Laravel log file for the simulated confirmation message:

`storage/logs/laravel.log`

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/products` | List products |
| GET | `/api/products/low-stock?threshold=10` | List low-stock products |
| GET | `/api/orders` | List orders |
| POST | `/api/orders` | Create an order |
| GET | `/api/customers` | List customers |
| GET | `/api/customers/orders?email=customer@example.com` | Retrieve a customer's order history |

### Create an Order

**Endpoint:** `POST /api/orders`

Example request body:

```json
{
  "customer": {
    "name": "Test Customer",
    "email": "testcustomer@example.com"
  },
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

The API validates the request, checks stock availability, calculates order totals, deducts stock, and returns the created order.

Use a valid product ID from `GET /api/products` when testing the request.

## Testing

Run the test suite:

```bash
php artisan test
```

The current test suite covers:

- Successful order creation.
- Rejection of orders when stock is insufficient.
- Prevention of stock overselling across orders.

## Assumptions and Design Decisions

- Product codes and customer email addresses must be unique.
- An order belongs to one customer and contains one or more product items.
- Order items store product details and prices at the time of purchase.
- Subtotal is calculated from item prices and quantities.
- Tax is calculated using each product's tax percentage.
- Grand total is the subtotal plus the calculated tax.
- Stock is deducted only when the order is successfully created.
- Database transactions and row locking are used to help maintain stock consistency during concurrent requests.
- Order confirmation is simulated through a queued job and application logs; no external email service is required.

## Prompt Log

Screenshots or a record of prompts used during development should be added to the `prompts/` directory, as required by the assignment.

## Walkthrough

A 5–10 minute walkthrough should demonstrate the application dashboard, inventory and low-stock listing, customer order history, order creation, stock deduction, queue job, and test results.
