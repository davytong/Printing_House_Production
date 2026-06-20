@echo off
title PrintTracker Pro — Starting...
color 0A
cls

echo.
echo  ============================================
echo    PrintTracker Pro — Office Startup
echo  ============================================
echo.

REM ── Kill stale processes ─────────────────────
echo  [1/5] Stopping old processes...
taskkill /f /im httpd.exe   >nul 2>&1
taskkill /f /im mysqld.exe  >nul 2>&1
timeout /t 2 /nobreak >nul

REM ── Start MySQL ──────────────────────────────
echo  [2/5] Starting MySQL...
start "" /min "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone

REM Wait for MySQL (up to 20 s)
set /a tries=0
:waitMySQL
set /a tries+=1
if %tries% GTR 20 goto mysqlDone
timeout /t 1 /nobreak >nul
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
if errorlevel 1 goto waitMySQL
:mysqlDone
echo     MySQL ready.

REM ── Start Apache ─────────────────────────────
echo  [3/5] Starting Apache...
start "" /min "C:\xampp\apache\bin\httpd.exe" -d "C:\xampp\apache"
timeout /t 3 /nobreak >nul
echo     Apache ready.

REM ── Laravel warm-up ──────────────────────────
echo  [4/5] Optimising Laravel...
cd /d "D:\printing-tracker\printing-tracker"
php artisan config:cache >nul 2>&1
php artisan route:cache  >nul 2>&1
php artisan view:cache   >nul 2>&1
echo     Done.

REM ── Open browser ─────────────────────────────
echo  [5/5] Opening PrintTracker...
timeout /t 2 /nobreak >nul
start "" "http://localhost:8080"

echo.
echo  ============================================
echo    PrintTracker Pro is RUNNING
echo    URL  : http://localhost:8080
echo    Stop : run stop-printing-tracker.bat
echo  ============================================
echo.
pause
