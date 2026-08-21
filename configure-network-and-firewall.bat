@echo off
title PrintTracker - Configure Network and Firewall
color 0B
cls

echo.
echo  =====================================================
echo    PrintTracker - Network & Firewall Setup
echo    (Run as Administrator - Right-click + Run as Admin)
echo  =====================================================
echo.
echo  This script will:
echo  1. Change network profile to 'Private' (trusted local network)
echo  2. Open Port 80 in Windows Firewall for HTTP web traffic
echo.

set /p CONFIRM=  Configure network and firewall now? (Y/N): 
if /i "%CONFIRM%" NEQ "Y" (
    echo  Cancelled.
    pause
    exit /b
)

echo.
echo  [1/2] Changing network profile to Private...
powershell -Command "Set-NetConnectionProfile -InterfaceIndex 15 -NetworkCategory Private"
if errorlevel 1 (
    echo  Failed to set profile automatically. Trying via interface alias...
    powershell -Command "Set-NetConnectionProfile -InterfaceAlias 'Ethernet' -NetworkCategory Private"
)

echo.
echo  [2/2] Adding inbound firewall rule for Port 80...
netsh advfirewall firewall add rule name="PrintTracker HTTP Port 80" dir=in action=allow protocol=TCP localport=80

echo.
echo  =====================================================
echo   COMPLETED!
echo   1. Network profile is set to Private.
echo   2. Firewall Port 80 is open.
echo  =====================================================
echo.
echo   Now, try opening this URL on the other device:
echo   ?? http://172.16.18.177
echo.
pause
