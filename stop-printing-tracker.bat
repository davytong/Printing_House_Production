@echo off
title PrintTracker - Stopping...
color 0C

echo ============================================
echo   PrintTracker - Shutting Down
echo ============================================
echo.

echo Stopping Apache...
taskkill /f /im httpd.exe >nul 2>&1
echo Done.

echo Stopping MySQL...
taskkill /f /im mysqld.exe >nul 2>&1
echo Done.

echo.
echo ============================================
echo   PrintTracker stopped successfully.
echo ============================================
timeout /t 3 /nobreak >nul
