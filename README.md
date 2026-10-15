# PsicoCMS (Laravel)

A content management system for psychology practices built with Laravel. PsicoCMS provides a web-based installation wizard to configure the database and set up the application.

---

## Features

- Developed with Claude AI assistance
- SSD (Spec-Driven Development) methodology
- Web-based installation wizard
- Database configuration through the installer
- Application setup from scratch
- Spanish language configuration

---

## Technologies

- PHP
- Laravel 12
- Eloquent ORM
- MySQL
- Blade
- JavaScript
- HTML
- CSS

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/gregoriomesafernandez-star/PsicoCMS.git
cd PsicoCMS
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure the environment

Create your `.env` file from the example:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**Linux / macOS:**

```bash
cp .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Create the storage link

Create the symbolic link required to serve uploaded files and images:

```bash
php artisan storage:link
```

### 6. Start the development server

```bash
php artisan serve
```

### 7. Complete the installation

Open the application in your browser:

http://127.0.0.1:8000

Follow the web installer to configure the MySQL database and complete the installation.

---

## Configuration

The application uses MySQL as its database system.

Database connection settings are configured through the web installer.

Make sure PHP, Composer, and MySQL are installed and running before starting the installation.

---

## Author

Gregorio Mesa