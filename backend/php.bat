@echo off
setlocal enabledelayedexpansion

rem ============================================================
rem  php.bat - Portable PHP shim untuk Laragon (Windows)
rem  Otomatis mendeteksi php.exe dari instalasi Laragon.
rem  Pemakaian: php artisan serve
rem ============================================================

set "PHP_EXE="

rem -- Kandidat path, dicek berurutan --
call :try "D:\LARAGON\bin\php\php-8.3.33-Win32-vs16-x64\php.exe"
call :try "D:\LARAGON\bin\php\php.exe"

rem -- Deteksi otomatis versi PHP terbaru di folder Laragon --
if not defined PHP_EXE if exist "D:\LARAGON\bin\php" (
    for /f "delims=" %%D in ('dir /b /ad /o-n "D:\LARAGON\bin\php" 2^>nul') do (
        if not defined PHP_EXE if exist "D:\LARAGON\bin\php\%%D\php.exe" (
            set "PHP_EXE=D:\LARAGON\bin\php\%%D\php.exe"
        )
    )
)

rem -- Fallback: php.exe yang sudah ada di PATH --
if not defined PHP_EXE (
    for %%P in (php.exe) do if not "%%~$PATH:P"=="" set "PHP_EXE=%%~$PATH:P"
)

if not defined PHP_EXE (
    echo [php.bat] ERROR: php.exe tidak ditemukan.
    echo [php.bat] Pastikan Laragon terpasang, atau ubah kandidat path di dalam file ini.
    exit /b 9009
)

"%PHP_EXE%" %*
exit /b %errorlevel%

:try
if not defined PHP_EXE if exist %1 set "PHP_EXE=%~1"
exit /b 0