#!/bin/zsh

set -u

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
DATA_DIR="$PROJECT_DIR/storage/local-mysql"
DB_SOCKET="$DATA_DIR/mysql.sock"
PHP_PID="$DATA_DIR/php-server.pid"
MYSQLADMIN_BIN="$(command -v mysqladmin || true)"

if [[ -f "$PHP_PID" ]]; then
    server_pid="$(<"$PHP_PID")"
    if kill -0 "$server_pid" 2>/dev/null; then
        kill "$server_pid" 2>/dev/null || true
    fi
    rm -f "$PHP_PID"
fi

if [[ -n "$MYSQLADMIN_BIN" && -S "$DB_SOCKET" ]]; then
    "$MYSQLADMIN_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot shutdown >/dev/null 2>&1 || true
fi

echo "تم إيقاف الموقع وقاعدة المعاينة المحلية."
read -r "?اضغط Enter لإغلاق النافذة..."
