@echo off
REM Windows 下 Workerman 不支持 count>1，也不支持单进程多 Worker。
REM 拆分为三个独立进程启动：Register / Gateway / BusinessWorker，均 count=1。
REM Linux 生产环境请使用 start.php，本脚本仅供 Windows 开发调试。

start "SocialwsRegister" php "%~dp0start_register_win.php" start
start "SocialwsGateway" php "%~dp0start_gateway_win.php" start
start "SocialwsBusiness" php "%~dp0start_businessworker_win.php" start
echo Socialws services started in separate windows. Close each window to stop.
pause