@echo off
REM Windows用
REM このスクリプトは、Laravelのスケジュールタスクを実行するために使用します。
REM 例えば、タスクスケジューラからこのスクリプトを呼び出すことができます。

SET PRJ_PATH=C:\path\to\prj
SET PHP_PATH=C:\php\php.exe

cd /d %PRJ_PATH%

%PHP_PATH% artisan schedule:run >> "%PRJ_PATH%\storage\logs\schedule.log" 2>&1
