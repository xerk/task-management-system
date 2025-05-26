# Task Management System

A task management REST API built with Laravel 12, featuring user authentication, task assignment, commenting system, and email notifications, you use postman to test the api [Postman Collection](https://github.com/xerk/task-management-system/blob/main/Task_postman_collection.json)

## Features

-   **User Management**: Registration, authentication, and profile
-   **Task Management**: Create, read, update, delete tasks with status tracking
-   **Task Assignment**: Assign tasks to users
-   **Commenting System**: Add comments to tasks with email notifications
-   **Email Notifications**: Automatic email alerts for task comments
-   **Local Email Server**: Mailpit for development
-   **Caching**: Redis-based caching for improved performance
-   **Queue System**: Background job processing for emails
-   **API Documentation**: Postman collection included

## Technology Stack

-   **Backend**: Laravel 12.x
-   **PHP**: 8.4.1
-   **Database**: MySQL 8.0
-   **Cache**: Redis
-   **Queue**: Redis/Database
-   **Email**: SMTP (Mailpit for development)
-   **Testing**: PHPUnit/Pest

## Quick Start

### Option 1: Docker Setup (Recommended)

1. **Clone the repository**

    ```bash
    git clone https://github.com/xerk/task-management-system.git
    cd task-management-system
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Configure environment variables**

Copy the .env.example file and rename it to .env.

or use the following environment variables:

    ```env
    APP_NAME="Task Management System"
    APP_ENV=local
    APP_KEY=<generated-key>
    APP_DEBUG=true
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=task_management
    DB_USERNAME=root
    DB_PASSWORD=password

    CACHE_DRIVER=redis
    QUEUE_CONNECTION=redis
    SESSION_DRIVER=database

    REDIS_HOST=redis
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    MAIL_MAILER=smtp
    MAIL_HOST=mailpit
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS="noreply@taskmanagement.local"
    MAIL_FROM_NAME="${APP_NAME}"
    ```

5. **Start Docker services**

    ```bash
    ./vendor/bin/sail up -d
    ```

6. **Run database migrations**

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

7. **Start queue worker**

    ```bash
    ./vendor/bin/sail artisan queue:work
    ```

8. **Access the application**
    - API: http://localhost/api
    - Mailpit: http://localhost:8025

### Option 2: Local Setup

1. **Prerequisites**

    - PHP 8.2+
    - Composer
    - MySQL 8.0+
    - Redis

2. **Clone and install**

    ```bash
    git clone https://github.com/xerk/task-management-system.git
    cd task-management-system
    composer install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Database setup**

    ```bash
    # Create database
    mysql -u root -p
    CREATE DATABASE task_management_system;
    exit;

    # Update .env file
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=task_management
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5. **Cache and queue configuration**

    ```env
    CACHE_DRIVER=redis
    QUEUE_CONNECTION=database
    SESSION_DRIVER=database

    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```

6. **Run migrations**

    ```bash
    php artisan migrate
    ```

7. **Start services**

    ```bash
    # Start development server
    php artisan serve

    # Start queue worker (new terminal)
    php artisan queue:work

    # Start Redis (if not running as service)
    redis-server
    ```

## API Documentation

### Authentication Endpoints

| Method | Endpoint             | Description            |
| ------ | -------------------- | ---------------------- |
| POST   | `/api/auth/register` | Register new user      |
| POST   | `/api/auth/login`    | User login             |
| POST   | `/api/auth/logout`   | User logout            |
| GET    | `/api/user`          | Get authenticated user |
| GET    | `/api/csrf-cookie`   | Get CSRF token         |

### Task Endpoints

| Method | Endpoint                | Description      |
| ------ | ----------------------- | ---------------- |
| GET    | `/api/tasks`            | List user tasks  |
| POST   | `/api/tasks`            | Create new task  |
| GET    | `/api/tasks/{id}`       | Get task details |
| PUT    | `/api/tasks/{id}`       | Update task      |
| DELETE | `/api/tasks/{id}`       | Delete task      |
| GET    | `/api/users/{id}/tasks` | Get user's tasks |

### Comment Endpoints

| Method | Endpoint                   | Description         |
| ------ | -------------------------- | ------------------- |
| GET    | `/api/tasks/{id}/comments` | Get task comments   |
| POST   | `/api/comments`            | Create comment      |
| GET    | `/api/comments/{id}`       | Get comment details |
| PUT    | `/api/comments/{id}`       | Update comment      |
| DELETE | `/api/comments/{id}`       | Delete comment      |
| GET    | `/api/users/{id}/comments` | Get user's comments |

### Postman Collection

Import the `Task_postman_collection.json` file into Postman for complete API testing:

1. Open Postman
2. Click Import
3. Select `Task_postman_collection.json`
4. Update the `url` variable to your API base URL
5. The collection handles CSRF tokens automatically

## Development

### Running Tests

```bash
# With Docker
./vendor/bin/sail test

# Local
php artisan test
```

```

### Database Operations

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed
```


## Performance

-   Redis caching for frequently accessed data
-   Database query optimization
-   Background job processing
-   Lazy loading of relationships
-   Efficient database indexing


Built By [@xerk](https://github.com/xerk)
