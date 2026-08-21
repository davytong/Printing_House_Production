@echo off
if "%~1"=="RunHidden" goto :StartNow

:: If not running hidden, create a quick VBS to restart this very script completely hidden
set "vbs=%temp%\run_hidden_tracker_ip.vbs"
echo Set WshShell = CreateObject("WScript.Shell") > "%vbs%"
echo WshShell.Run chr(34) ^& "%~f0" ^& chr(34) ^& " RunHidden", 0, False >> "%vbs%"
wscript "%vbs%"
del "%vbs%"
exit /b

:StartNow
title PrintTracker - Startup and IP Auto-Update
color 0A

REM --- Get the current LAN IP ---
for /f "usebackq" %%a in (`powershell -NoProfile -Command "(Get-NetIPAddress -InterfaceAlias 'Ethernet' -AddressFamily IPv4 -AddressState Preferred).IPAddress"`) do (
    set "CURRENT_IP=%%a"
)

if "%CURRENT_IP%"=="" (
    echo Could not detect LAN IP. Network may not be connected.
    echo PrintTracker will still work on THIS computer.
    goto :cache_only
)

echo Detected LAN IP: %CURRENT_IP%

REM --- Update .env APP_URL ---
set ENV_FILE=D:\printing-tracker\printing-tracker\.env

REM Replace the APP_URL line using PowerShell
powershell -Command "(Get-Content '%ENV_FILE%') -replace 'APP_URL=.*', 'APP_URL=http://%CURRENT_IP%' | Set-Content '%ENV_FILE%'"

REM Update SERVER_IP inside client-setup.bat
powershell -Command "(Get-Content 'D:\printing-tracker\printing-tracker\tools\network\client-setup.bat') -replace 'set SERVER_IP=.*', 'set SERVER_IP=%CURRENT_IP%' | Set-Content 'D:\printing-tracker\printing-tracker\tools\network\client-setup.bat'"

echo Updated APP_URL to http://%CURRENT_IP% and updated client-setup.bat

:cache_only
REM --- Rebuild Laravel config cache ---
cd /d "D:\printing-tracker\printing-tracker"
php artisan config:clear >nul 2>&1
php artisan config:cache >nul 2>&1
php artisan route:cache  >nul 2>&1
echo Laravel cache refreshed.

REM --- Update hosts file on this machine ---
findstr /i "printing.local" "C:\Windows\System32\drivers\etc\hosts" >nul 2>&1
if errorlevel 1 (
    echo %CURRENT_IP%   printing.local >> "C:\Windows\System32\drivers\etc\hosts"
    echo Added printing.local to local hosts file.
) else (
    REM Update existing entry with new IP
    powershell -Command "(Get-Content 'C:\Windows\System32\drivers\etc\hosts') -replace '^\d+\.\d+\.\d+\.\d+\s+printing\.local.*', '%CURRENT_IP%   printing.local' | Set-Content 'C:\Windows\System32\drivers\etc\hosts'"
    echo Updated printing.local IP in hosts file.
)

echo.
echo =====================================================
echo  PrintTracker is running at:
echo.
echo    http://%CURRENT_IP%           (for other devices)
echo    http://printing.local         (after hosts setup)
echo    http://printing               (Windows hostname)
echo.
echo  Share this with other PCs: http://%CURRENT_IP%
echo =====================================================
echo.

exit /b 0
