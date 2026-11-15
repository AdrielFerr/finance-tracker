@echo off
cd /d "C:\xampp\htdocs\finance-tracker"
"C:\xampp\php\php.exe" artisan schedule:run
