@echo off
REM Windows用
REM このスクリプトは、Laravelのキューワーカーを一時的に稼働するために使用します。
REM 例えば、タスクスケジューラからこのスクリプトを呼び出すことができます。

SET PRJ_PATH=C:\path\to\prj
SET PHP_PATH=C:\php\php.exe

cd /d %PRJ_PATH%

%PHP_PATH% artisan kaikon:queue-work ^
  --sleep=1 ^
  --max-jobs=20 ^
  --max-time=50 ^
  --stop-when-empty ^
  >> "%PRJ_PATH%\storage\logs\queue.log" 2>&1
