
# DoAnTotNghiep Backend

This is the backend repository for the Student Dormitory Management application. The system is built using Laravel and provides a RESTful API to manage dormitory data, contracts, payments, feedback, and more.

## Features
- User authentication and role management (Admin, Owner, Tenant).
- CRUD operations for dormitories, rooms, and contracts.
- Payment tracking and validation.
- Feedback and review system for tenants.
- Reporting on revenue and debts.

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/ThanhThn/DoAnTotNghiep.git
   ```

2. Navigate to the project folder:
   ```bash
   cd DoAnTotNghiep
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Copy the `.env.example` to `.env` and configure your database connection.

5. Generate the application key:
   ```bash
   php artisan key:generate
   ```

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Serve the application:
   ```bash
   php artisan serve
   ```

## Technologies Used
- Laravel
- PostgresSQL
- JWT Authentication
- Ably Broadcast
- Redis

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
