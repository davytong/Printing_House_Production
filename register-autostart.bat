@echo off
echo Registering PrintTracker as a Windows Startup Task...
echo (Requires Administrator – right-click and Run as Administrator)
echo.

REM Delete old task if exists
schtasks /delete /tn "PrintTracker AutoStart" /f >nul 2>&1

REM Create new task: runs at logon for ALL users, elevated
schtasks /create ^
  /tn "PrintTracker AutoStart" ^
  /tr "wscript.exe \"D:\printing-tracker\printing-tracker\autostart-silent.vbs\"" ^
  /sc ONLOGON ^
  /rl HIGHEST ^
  /f

echo.
echo ============================================
echo   Task registered successfully!
echo   PrintTracker will now start automatically
echo   every time Windows boots and you log in.
echo.
echo   To start manually: double-click
echo   start-printing-tracker.bat
echo.
echo   To remove auto-start: run
echo   remove-autostart.bat
echo ============================================
pause
