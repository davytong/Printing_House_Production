@echo off
echo Removing PrintTracker AutoStart task...
schtasks /delete /tn "PrintTracker AutoStart" /f
echo Done. PrintTracker will no longer start automatically.
pause
