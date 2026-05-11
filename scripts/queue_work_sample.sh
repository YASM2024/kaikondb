#!/bin/bash
# Linux用
# このスクリプトは、Laravelのキューワーカーを一時的に稼働するために使用します。
# 例えば、cronジョブからこのスクリプトを呼び出すことができます。

PRJ_PATH="/path/to/prj"
PHP_PATH=$(which php)

cd "$PRJ_PATH" || exit 1

$PHP_PATH "$PRJ_PATH/artisan" kaikon:queue-work \
  --sleep=1 \
  --stop-when-empty \
  >> "$PRJ_PATH/storage/logs/queue.log" 2>&1
