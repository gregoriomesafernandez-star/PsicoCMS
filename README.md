# PsicoCMS (Laravel)

PsicoCMS is a content management system for psychology practices built with Laravel 12. It features a web-based installation wizard that automatically creates and configures the MySQL database. Developed with assistance from Claude AI, following the Spec-Driven Development (SSD) methodology.

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

PsicoCMS uses MySQL as its database system.

- **Automatic database creation:** The web installer automatically creates the database during the installation process. No manual database creation through phpMyAdmin is required.
- **Database configuration:** Configure the MySQL connection through the web installer.
- **Requirements:** Make sure PHP, Composer, and MySQL are installed and running before starting the installation.

The MySQL user must have the necessary permissions to create databases.

---

## Author

Gregorio Mesa