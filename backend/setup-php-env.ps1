# ============================================================
#  setup-php-env.ps1
#  Mengaktifkan PHP Laragon & Composer untuk sesi PowerShell ini,
#  sehingga perintah `php` dan `composer` langsung dikenali.
#
#  Catatan:
#  - PATH Laragon ditaruh di depan agar menang dari file kosong
#    C:\Windows\System32\php (penyebab `php` tidak dikenali).
#
#  Cara pakai:
#    . .\setup-php-env.ps1      # (dot-source, agar PATH tetap di sesi aktif)
# ============================================================

$phpDir = 'D:\LARAGON\bin\php\php-8.3.33-Win32-vs16-x64'
$composerDir = 'D:\LARAGON\bin\composer'

if (-not (Test-Path (Join-Path $phpDir 'php.exe'))) {
    Write-Error "php.exe tidak ditemukan di $phpDir"
    return
}

# Selalu taruh di depan agar mengalahkan C:\Windows\System32\php
$env:Path = "$phpDir;$composerDir;$env:Path"

Write-Host "PHP environment siap:" -ForegroundColor Green
& (Join-Path $phpDir 'php.exe') -v | Select-Object -First 1