#!/bin/bash
# Linux用
# このスクリプトは、Laravelのスケジュールタスクを実行するために使用します。
# 例えば、cronジョブからこのスクリプトを呼び出すことができます。

PRJ_PATH="/path/to/prj"
PHP_PATH=$(which php)

cd "$PRJ_PATH" || exit 1

$PHP_PATH artisan schedule:run >> "$PRJ_PATH/storage/logs/schedule.log" 2>&1
