# Grupo Ferretero Piedras 🏗️🪨

An interactive web platform for managing the services and operations of **Grupo Ferretero Piedras**. The system follows a client-server architecture with a PHP backend and an HTML5, CSS3, and JavaScript frontend.

🔗 **Live Demo:** [https://proydweb.com/desarrollo_web/2026/piedras/](https://proydweb.com/desarrollo_web/2026/piedras/)

---

## 🔑 Demo Credentials

Use the following accounts to explore each access level:

### 👤 Client Role — Customer Portal
| Field | Value |
|-------|-------|
| **Email** | `juan.perez.otero@email.com` |
| **Password** | `holaprogramas7M$` |

**Access includes:** browsing the materials catalog, editing profile, uploading tax documents (*Constancia de Situación Fiscal*), viewing purchase history, and printing generated invoices.

### 💼 Administrator Role — Admin Panel
| Field | Value |
|-------|-------|
| **Email** | `r.jpm@hotmail.com` |
| **Password** | `holaprogramas7M$` |

**Access includes:** full employee management (create, update, delete), client administration, supplier control, global sales overview, inventory management, and dynamic editing of the public homepage content.

### 🛠️ Employee Role — Employee Portal
| Field | Value |
|-------|-------|
| **Email** | `leonardo.mendezr@gmail.com` |
| **Password** | `Leonardo8@` |

**Access includes:** registering new clients in the system and processing in-store sales at the point of sale.

---

## 🚀 Local Setup Guide

Follow these steps to run the project on your local machine.

### 📋 Prerequisites

You will need the following installed on your system:

1. **A local web server** with PHP and MySQL support — e.g., [Laragon](https://laragon.org/) *(recommended)*, XAMPP, EasyPHP, or WampServer.
   - PHP **7.4 or higher** (PHP 8.x compatible)
   - MySQL **5.7+** or MariaDB
2. **[Composer](https://getcomposer.org/)** — PHP dependency manager, installed globally.

---

### 🛠️ Setup Steps

#### 1. Clone the Repository

Clone the repository into your local web server's root folder (e.g., `htdocs/` for XAMPP or `www/` for Laragon):

```bash
git clone https://github.com/Ydna352/Web-Page-Grupo-Ferretero-.git
cd Web-Page-Grupo-Ferretero-
```

#### 2. Install PHP Dependencies

The `vendor/` folder is not included in the repository. Regenerate it by running the following command in the project root:

```bash
composer install
```

This will download the `dompdf` library (used for automatic PDF invoice generation) along with its required dependencies.

#### 3. Configure the Database

1. Open your preferred database tool (phpMyAdmin, HeidiSQL, DBeaver, etc.).
2. Create a new database named `proydweb_p2026` (or any name you prefer).
3. Import the database schema file located in the project root:

```
dataBase_gf_piedras_final.sql
```

#### 4. Set Up the Connection Credentials

1. Navigate to the `PHP/` folder.
2. Copy the template file `config.php.example` and rename it to `config.php`:

```bash
cp PHP/config.php.example PHP/config.php
```

3. Open `PHP/config.php` in your text editor and update the constants with your local MySQL credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
```

#### 5. Verify Folder Permissions *(Optional)*

Make sure your local web server has **read and write** permissions on the following directories to prevent errors when uploading files or writing logs:

```
logs/
constancia_fiscal/
facturas_pdf/
uploads/
imagenes_materiales/
```
