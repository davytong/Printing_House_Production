@echo off
title Printing Tracker - Background Worker
color 0A

echo ========================================================
echo        PRINTING TRACKER BACKGROUND WORKER
echo ========================================================
echo.
echo This window processes background tasks like sending 
echo Telegram reports so that the website stays incredibly fast.
echo.
echo Please leave this window open! You can minimize it.
echo.
echo Starting worker...
echo.

php artisan queue:work

echo.
echo Worker has stopped. Press any key to close this window.
pause
