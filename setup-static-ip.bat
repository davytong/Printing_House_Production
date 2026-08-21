@echo off
title PrintTracker - Set Static IP
color 0B
cls

echo.
echo  =====================================================
echo    PrintTracker - Static IP Setup
echo    (Run as Administrator - Right-click and Run as Admin)
echo  =====================================================
echo.
echo  This script will assign a FIXED IP address (172.16.17.65)
echo  to this computer's Ethernet adapter so it never changes.
echo.

REM ?? Detect Ethernet adapter name ?????????????????????
for /f "tokens=1,* delims=:" %%A in ('netsh interface show interface ^| findstr /i "Ethernet"') do (
    set "ADAPTER=%%B"
    goto :gotAdapter
)
:gotAdapter

if "%ADAPTER%"=="" (
    set "ETH_NAME=Ethernet"
) else (
    REM Trim leading space
    set "ETH_NAME=%ADAPTER:~1%"
)

echo  Detected adapter: "%ETH_NAME%"
echo.
echo  ?????????????????????????????????????????????????????
echo   STATIC IP SETTINGS TO BE APPLIED:
echo  ?????????????????????????????????????????????????????
echo   IP Address  : 172.16.17.65
echo   Subnet Mask : 255.255.248.0
echo   Gateway     : 172.16.16.1
echo   DNS 1       : 180.178.124.12
echo   DNS 2       : 8.8.8.8
echo  ?????????????????????????????????????????????????????
echo.
echo  Note: You won't need to type ":8000" anymore!
echo        The app will run on: http://172.16.17.65
echo.

set /p CONFIRM=  Apply these static IP settings? (Y/N): 
if /i "%CONFIRM%" NEQ "Y" (
    echo  Cancelled. No changes made.
    pause
    exit /b
)

echo.
echo  Applying settings on: "%ETH_NAME%"
netsh interface ip set address name="%ETH_NAME%" static 172.16.17.65 255.255.248.0 172.16.16.1
netsh interface ip set dns name="%ETH_NAME%" static 180.178.124.12
netsh interface ip add dns name="%ETH_NAME%" 8.8.8.8 index=2

echo.
echo  =====================================================
echo   DONE! Static IP has been set to 172.16.17.65.
echo.
echo   PrintTracker is now permanently accessible at:
echo     http://172.16.17.65
echo     http://printing.local          (after hosts setup)
echo     http://OFFICE-Printing         (computer name)
echo.
echo   Let's update the Laravel config...
echo  =====================================================
echo.

cd /d "D:\printing-tracker\printing-tracker"
REM We will update the APP_URL inside .env as well
powershell -Command "(Get-Content '.env') -replace 'APP_URL=.*', 'APP_URL=http://172.16.17.65' | Set-Content '.env'"
php artisan config:clear >nul 2>&1
php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1

echo Laravel config updated to http://172.16.17.65.
echo.
pause
