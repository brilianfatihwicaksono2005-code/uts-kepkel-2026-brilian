@echo off
rem ============================================================
rem  artisan.bat - Menjalankan Laravel artisan lewat php.bat
rem  Pemakaian: artisan serve
rem ============================================================
call "%~dp0php.bat" "%~dp0artisan" %*
exit /b %errorlevel%