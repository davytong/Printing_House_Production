Set WshShell = CreateObject("WScript.Shell")

' 1. Run the IP Auto-Update script silently
WshShell.Run "cmd /c D:\printing-tracker\printing-tracker\auto-update-ip.bat", 0, False

' 2. Run Telegram Bot polling watcher silently
WshShell.Run "cmd /c cd /d D:\printing-tracker\printing-tracker && C:\xampp\php\php.exe artisan telegram:poll --watch --interval=5 > D:\printing-tracker\printing-tracker\telegram_bot.log 2>&1", 0, False

' 3. Start Laravel silently
WshShell.Run "cmd /c cd /d D:\printing-tracker\printing-tracker && C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000 > D:\printing-tracker\printing-tracker\laravel.log 2>&1", 0, False

' 4. Start Cloudflare Quick Tunnel silently
WshShell.Run "cmd /c timeout /t 8 /nobreak >nul && ""C:\Program Files (x86)\cloudflared\cloudflared.exe"" tunnel --url http://127.0.0.1:8000 > ""D:\printing-tracker\printing-tracker\cloudflare.log"" 2>&1", 0, False