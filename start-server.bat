@echo off
title Sego Sambelan - Dev Server
cd /d "%~dp0"

echo ========================================
echo   Sego Sambelan - Laravel Dev Server
echo ========================================
echo.

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP tidak ditemukan di PATH.
    echo Install PHP atau tambahkan folder PHP ke PATH Windows.
    pause
    exit /b 1
)

echo Membersihkan cache config...
php artisan config:clear

echo.
echo Server akan berjalan di:
echo   http://127.0.0.1:8000
echo   http://localhost:8000
echo.
echo Buka salah satu URL di browser (pakai http, BUKAN https).
echo Tekan Ctrl+C untuk menghentikan server.
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
