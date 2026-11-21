$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
if (Test-Path 'C:\xampp\php\php.exe') { $env:Path = 'C:\xampp\php;' + $env:Path }
Get-Command php -ErrorAction Stop | Out-Null
Get-Command composer -ErrorAction Stop | Out-Null
function Check-Exit { if ($LASTEXITCODE -ne 0) { throw "Previous command failed. Fix the error above before continuing." } }
php -r "exit(extension_loaded('gd') && extension_loaded('pdo_sqlite') && extension_loaded('mbstring') ? 0 : 1);"
if ($LASTEXITCODE -ne 0) { throw 'Enable gd, pdo_sqlite, sqlite3, and mbstring in C:\xampp\php\php.ini, then rerun.' }
composer install
Check-Exit
if (!(Test-Path '.env')) { Copy-Item '.env.example' '.env' }
if (!(Test-Path 'database/database.sqlite')) { New-Item 'database/database.sqlite' -ItemType File | Out-Null }
if (!(Select-String -Path '.env' -Pattern '^APP_KEY=.+$' -Quiet)) {
    php artisan key:generate
    Check-Exit
}
php artisan migrate --force
Check-Exit
php artisan pos:install
Check-Exit
Write-Host 'Ready. Run: php artisan serve'
