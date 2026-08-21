@echo off
title PrintTracker - Clean Client Hosts
color 0A

:: Self-elevation script
:: Check for Admin permissions
net session >nul 2>&1
if %errorLevel% NEQ 0 (
    echo Requesting administrative privileges...
    echo Set UAC = CreateObject^("Shell.Application"^) > "%temp%\getadmin.vbs"
    echo UAC.ShellExecute "%~s0", "", "", "runas", 1 >> "%temp%\getadmin.vbs"
    "%temp%\getadmin.vbs"
    exit /B
)

pushd "%CD%"
CD /D "%~dp0"

echo =====================================================
echo  PrintTracker - Cleaning hosts file...
echo =====================================================
echo.

:: Remove Read-only attribute in case it is set
attrib -r C:\Windows\System32\drivers\etc\hosts >nul 2>&1

:: Clean up printing.local from hosts file
powershell -Command "(Get-Content C:\Windows\System32\drivers\etc\hosts) | Where-Object { $_ -notmatch 'printing.local' } | Set-Content C:\Windows\System32\drivers\etc\hosts"

:: Restore Read-only attribute for security
attrib +r C:\Windows\System32\drivers\etc\hosts >nul 2>&1

echo.
echo =====================================================
echo  SUCCESS! Old printing.local mapping has been removed.
echo  Your PC will now dynamically find the server.
echo =====================================================
echo.
pause
