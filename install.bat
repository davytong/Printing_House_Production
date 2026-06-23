@echo off
title PrintTracker - Install
echo.
echo ========================================
echo   Printing House Production - Install
echo ========================================
echo.

:: Check PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP not found! Install XAMPP first.
    echo Download: https://www.apachefriends.org/
    pause
    exit /b 1
)

:: Check Composer
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Composer not found!
    echo Download: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo [1/6] Installing PHP dependencies...
call composer install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Composer install failed!
    pause
    exit /b 1
)

echo.
echo [2/6] Setting up environment...
if not exist .env (
    copy .env.example .env
    echo       .env created from .env.example
    echo       ** EDIT .env WITH YOUR DATABASE CREDENTIALS **
    echo.
    php artisan key:generate
) else (
    echo       .env already exists - skipping
)

echo.
echo [3/6] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo [WARNING] Migration failed - check your DB connection in .env
    echo Make sure MySQL is running and database 'printing_system' exists
)

echo.
echo [4/6] Creating storage link...
php artisan storage:link 2>nul

echo.
echo [5/6] Caching configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo [6/6] Done!
echo.
echo ========================================
echo   INSTALLATION COMPLETE
echo ========================================
echo.
echo   To run with XAMPP:
echo     1. Start MySQL + Apache in XAMPP Control Panel
echo     2. Open: http://localhost:8080
echo.
echo   To run with PHP built-in server:
echo     php artisan serve
echo     Open: http://localhost:8000
echo.
echo ========================================
pause
