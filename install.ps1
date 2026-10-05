# 一条命令装好苹果v12。用法：.\install.ps1
# 或：.\install.ps1 YourPass#1
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root
$Pass = if ($args.Count -ge 1) { [string]$args[0] } else { "admin123" }

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Error "需要 PHP 8.4+，并把 php 加进 PATH。"
}
$ver = & php -r "echo PHP_VERSION;"
$ok = & php -r "echo version_compare(PHP_VERSION, '8.4.0', '>=') ? '1' : '0';"
if ($ok -ne "1") {
    Write-Error "当前 PHP 是 $ver，需要 8.4 或更高。"
}
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Error "请先安装 Composer：https://getcomposer.org/download/"
}

Write-Host "PHP $ver"
composer install --no-interaction --prefer-dist --no-ansi
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate --force --ansi | Out-Null
New-Item -ItemType Directory -Force -Path storage/framework/views, storage/framework/cache/data, storage/framework/sessions, storage/logs, storage/app, bootstrap/cache, database | Out-Null
if (-not (Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite | Out-Null }

if (Test-Path storage/app/install.lock) {
    Write-Host "已经装过。要重装先删 storage/app/install.lock"
} else {
    php artisan video:install --password=$Pass --demo --ansi
}

Write-Host ""
Write-Host "装好了。试用： php artisan serve --host=127.0.0.1 --port=8010"
Write-Host "后台 http://127.0.0.1:8010/admin/login  账号 admin  密码 $Pass"
