@echo off
title Restart Apache - Admin Required
color 0A

echo =====================================================
echo   Restarting Apache2.4 Service (Admin Mode)
echo =====================================================
echo.

REM Stop the service
echo [1/3] Stopping Apache2.4...
net stop Apache2.4
if errorlevel 1 (
    echo Failed to stop. Trying force kill...
    taskkill /f /im httpd.exe
    timeout /t 2 /nobreak >nul
)

REM Wait for it to fully stop
timeout /t 3 /nobreak >nul

REM Start the service
echo [2/3] Starting Apache2.4...
net start Apache2.4
if errorlevel 1 (
    echo Service start failed. Starting directly...
    start "" /min "C:\xampp\apache\bin\httpd.exe" -d "C:\xampp\apache"
    timeout /t 3 /nobreak >nul
)

REM Verify
echo [3/3] Verifying...
timeout /t 2 /nobreak >nul
netstat -an | findstr ":80 " | findstr "LISTEN"
if errorlevel 1 (
    echo WARNING: Port 80 not detected. Check Apache logs.
) else (
    echo Port 80 is LISTENING - Apache is running!
)

REM Clear Laravel caches
echo.
echo Refreshing Laravel...
cd /d "D:\printing-tracker\printing-tracker"
php artisan config:clear >nul 2>&1
php artisan config:cache >nul 2>&1
php artisan route:cache  >nul 2>&1
echo Laravel cache refreshed.

echo.
echo =====================================================
echo  Apache restarted with new config!
echo  PrintTracker is now at:
echo    http://printing.local
echo    http://172.16.18.177
echo    http://OFFICE-Printing
echo =====================================================
echo.
timeout /t 2 /nobreak >nul
start "" "http://printing.local"
pause
