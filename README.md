# Printing House Production System

A complete production management system for a printing house, built with Laravel 12 + MySQL + XAMPP.

## Features

- **Production Tracking** — 48 books across 6 ESL levels, daily print recording
- **Stock Management** — Paper, Film, Consumable materials with daily reporting
- **Procurement** — Multi-item requests, file attachments, supplier history, analytics
- **Telegram Integration** — Send reports to group topics, auto-routing by purpose
- **Entry Screen** — Simple name + position login (no password), localStorage remember
- **Mobile Friendly** — Bottom nav, responsive sidebar, touch-optimized

## Requirements

- PHP 8.2+
- MySQL/MariaDB 10.4+
- Apache (XAMPP recommended on Windows)
- Composer

## Setup on a New Device

### 1. Clone the repository

```bash
git clone https://github.com/davytong/Printing_House_Production.git
cd Printing_House_Production
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=printing_system
DB_USERNAME=root
DB_PASSWORD=

TELEGRAM_BOT_TOKEN=your_bot_token_here
```

### 4. Create database and run migrations

```sql
CREATE DATABASE printing_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate
```

### 5. Create storage link

```bash
php artisan storage:link
```

### 6. Build caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Run the application

**Option A — XAMPP (recommended for office)**

Configure Apache VirtualHost in `httpd-vhosts.conf`:

```apache
<VirtualHost *:8080>
    DocumentRoot "C:/path/to/Printing_House_Production/public"
    ServerName localhost

    <Directory "C:/path/to/Printing_House_Production/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Then access at `http://localhost:8080`

**Option B — Laravel dev server (quick test)**

```bash
php artisan serve
```

Access at `http://localhost:8000`

## Network Access (Office LAN)

Other computers on the same network can access via:
```
http://SERVER_IP:8080
```

Find server IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)

## Project Structure

```
app/
├── Http/Controllers/
│   ├── EntryController.php        — Login screen
│   ├── DashboardController.php    — Main dashboard
│   ├── PrintingController.php     — Book production
│   ├── ProcurementController.php  — Purchase requests
│   ├── Stock/
│   │   ├── MaterialController.php — Stock items
│   │   ├── MovementController.php — Stock movements + daily report
│   │   └── StockReportController.php
│   ├── TelegramController.php     — Bot API endpoints
│   └── TelegramSetupController.php
├── Models/
│   ├── Book.php, DailyPrint.php
│   ├── Material.php, StockMovement.php
│   ├── ProcurementRequest.php, ProcurementItem.php
│   ├── TelegramGroup.php
│   └── ActivityLog.php
└── Services/
    ├── StockService.php
    ├── TelegramService.php
    ├── AlertService.php
    ├── ReportService.php
    └── ImageService.php
```

## Telegram Bot Setup

1. Create bot via @BotFather
2. Add bot to your group
3. Go to `/telegram` in the app
4. Click "Poll" to detect groups
5. Assign purpose to each topic (Paper Stock, Press Report, etc.)

## License

Private — BELTEI Printing Press
