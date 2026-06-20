Set WshShell = CreateObject("WScript.Shell")

WshShell.Run "cmd /c cd /d D:\printing-tracker\printing-tracker && php artisan serve --host=0.0.0.0 --port=8000", 0