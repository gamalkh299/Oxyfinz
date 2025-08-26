# Expense Management System

A web-based expense management system built with Yii2 Framework that allows users to submit and manage expenses while administrators can approve or reject them.

## Features

* User authentication
* Role-based access control (Admin and Regular users)
* Expense submission and management
* Expense approval workflow
* REST API support
* SQLite database

## Setup

1. Clone the repository:

```bash
git clone <https://github.com/gamalkh299/Oxyfinz>
cd <Oxyfinz>
```

2. Install dependencies:

```bash
composer install
```

3. Initialize the application:

```bash
php yii migrate/fresh
```

4. Database:

The application uses an SQLite database located at `@app/data/database.sqlite`. Migrations will automatically create required tables.

5. Running the Application:

Start the built-in PHP server:

```bash
php yii serve
```

The application will be available at [http://localhost:8080](http://localhost:8080).

## Default Users (Seed Data)

| Role  | Email                                             | Username | Password |
| ----- | ------------------------------------------------- | -------- | -------- |
| Admin | [admin@oxyfinz.local](mailto:admin@oxyfinz.local) | admin    | admin123 |
| User  | [user@oxyfinz.local](mailto:user@oxyfinz.local)   | user     | user123  |

## API Endpoints

All API endpoints require Bearer token authentication except login/register.

### Authentication

* `POST /api/auth/login` - Login user
* `POST /api/auth/register` - Register new user
* `POST /api/auth/logout` - Logout user
* `GET /api/auth/profile` - Get user profile

### Expenses

* `GET /api/expense` - List all expenses (users see only their expenses, admin sees all)
* `GET /api/expense/{id}` - View specific expense
* `POST /api/expense` - Create new expense
* `PUT /api/expense/{id}` - Update expense (only pending status)
* `DELETE /api/expense/{id}` - Delete expense
* `POST /api/expense/approve/{id}` - Approve expense (admin only)
* `POST /api/expense/reject/{id}` - Reject expense (admin only)

## Web Interface

* `/site/index` - Homepage
* `/site/login` - Login page
* `/site/register` - Registration page
* `/expense/index` - List expenses
* `/expense/create` - Create expense
* `/expense/update/{id}` - Update expense
* `/expense/view/{id}` - View expense details
