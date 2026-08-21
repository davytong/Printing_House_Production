Set WshShell = CreateObject("WScript.Shell")

' 1. Run the IP Auto-Update script silently
WshShell.Run "cmd /c D:\printing-tracker\printing-tracker\auto-update-ip.bat", 0, False

' 2. Run the Telegram Bot polling watcher in the background silently
WshShell.Run "cmd /c cd /d D:\printing-tracker\printing-tracker && C:\xampp\php\php.exe artisan telegram:poll --watch --interval=5 > D:\printing-tracker\printing-tracker\telegram_bot.log 2>&1", 0, False
