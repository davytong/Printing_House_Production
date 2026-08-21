@echo off
title PrintTracker - Client PC Setup
color 0E
cls

echo.
echo  =====================================================
echo    PrintTracker - Client PC Setup
echo    (Run this on OTHER computers that need access)
echo    (Requires Administrator - Right-click + Run as Admin)
echo  =====================================================
echo.
echo  This will add 'printing.local' to this PC's hosts file
echo  so you can access PrintTracker by name instead of IP.
echo.

REM ?? Detect current server IP ??
set SERVER_IP=100.87.181.39
set SERVER_NAME=PRINTING

REM ?? Check if already exists ??
findstr /i "printing.local" "C:\Windows\System32\drivers\etc\hosts" >nul 2>&1
if not errorlevel 1 (
    echo  'printing.local' is already configured on this PC!
    echo.
    echo  You can access PrintTracker at:
    echo    http://printing.local          (by name)
    echo    http://%SERVER_IP%             (by IP)
    echo    http://%SERVER_NAME%           (by computer name)
    echo.
    pause
    exit /b
)

REM ?? Add the entry ??
echo  Adding printing.local = %SERVER_IP% to hosts file...
echo %SERVER_IP%   printing.local >> "C:\Windows\System32\drivers\etc\hosts"

if errorlevel 1 (
    echo.
    echo  ERROR: Could not write to hosts file.
    echo  Please RIGHT-CLICK this file and choose 'Run as Administrator'.
    echo.
    pause
    exit /b
)

echo.
echo  =====================================================
echo   SUCCESS! PrintTracker is now accessible at:
echo.
echo     http://printing.local           (by name)
echo     http://%SERVER_IP%              (by IP)
echo     http://%SERVER_NAME%            (by computer name)
echo.
echo   Open any of these in your browser!
echo   Bookmark the one that works best.
echo  =====================================================
echo.

REM Open browser
timeout /t 2 /nobreak >nul
start "" "http://printing.local"

pause
