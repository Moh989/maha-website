#!/bin/zsh

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

SITE_HOST="127.0.0.1"
SITE_PORT="8091"
DB_PORT="3308"
SITE_URL="http://${SITE_HOST}:${SITE_PORT}"
DB_NAME="almaha_finall_preview"
DATA_DIR="$PROJECT_DIR/storage/local-mysql"
DB_SOCKET="$DATA_DIR/mysql.sock"
DB_PID="$DATA_DIR/mysql.pid"
DB_LOG="$PROJECT_DIR/storage/logs/local-mysql.log"
PHP_PID="$DATA_DIR/php-server.pid"
PHP_LOG="$PROJECT_DIR/storage/logs/local-php-server.log"

PHP_BIN="$(command -v php || true)"
MYSQLD_BIN="$(command -v mysqld || true)"
MYSQL_BIN="$(command -v mysql || true)"
MYSQLADMIN_BIN="$(command -v mysqladmin || true)"
CURL_BIN="$(command -v curl || true)"

cleanup() {
    if [[ -f "$PHP_PID" ]]; then
        local server_pid="$(<"$PHP_PID")"
        if kill -0 "$server_pid" 2>/dev/null; then
            kill "$server_pid" 2>/dev/null || true
        fi
        rm -f "$PHP_PID"
    fi
    if [[ -n "${MYSQLADMIN_BIN:-}" && -S "$DB_SOCKET" ]]; then
        "$MYSQLADMIN_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot shutdown >/dev/null 2>&1 || true
    fi
}

trap cleanup EXIT INT TERM HUP

print_error() {
    echo
    echo "خطأ: $1"
    echo
    read -r "?اضغط Enter لإغلاق النافذة..."
    exit 1
}

[[ -n "$PHP_BIN" ]] || print_error "PHP غير مثبت أو غير مضاف إلى PATH."
[[ -n "$MYSQLD_BIN" && -n "$MYSQL_BIN" && -n "$MYSQLADMIN_BIN" ]] || print_error "MySQL غير مثبت أو غير مضاف إلى PATH."
[[ -n "$CURL_BIN" ]] || print_error "curl غير متوفر على الجهاز."

mkdir -p "$DATA_DIR" "$PROJECT_DIR/storage/logs" "$PROJECT_DIR/storage/uploads" "$PROJECT_DIR/storage/cache"

if [[ ! -d "$DATA_DIR/mysql" ]]; then
    echo "[1/6] تهيئة قاعدة البيانات المحلية لأول مرة..."
    "$MYSQLD_BIN" --initialize-insecure --datadir="$DATA_DIR" > "$DB_LOG" 2>&1 \
        || print_error "تعذر تهيئة MySQL. راجع $DB_LOG"
fi

if ! "$MYSQLADMIN_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot ping >/dev/null 2>&1; then
    echo "[2/6] تشغيل MySQL المحلي..."
    nohup "$MYSQLD_BIN" \
        --datadir="$DATA_DIR" \
        --port="$DB_PORT" \
        --bind-address="$SITE_HOST" \
        --socket="$DB_SOCKET" \
        --pid-file="$DB_PID" \
        --log-error="$DB_LOG" \
        --skip-log-bin \
        --mysqlx=OFF \
        >/dev/null 2>&1 &

    for attempt in {1..30}; do
        if "$MYSQLADMIN_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot ping >/dev/null 2>&1; then
            break
        fi
        sleep 1
    done
fi

"$MYSQLADMIN_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot ping >/dev/null 2>&1 \
    || print_error "تعذر تشغيل MySQL. راجع $DB_LOG"

echo "[3/6] تجهيز بيانات الموقع..."
"$MYSQL_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot \
    --execute="CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"$MYSQL_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot "$DB_NAME" < "$PROJECT_DIR/database/schema.sql"
"$MYSQL_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot "$DB_NAME" < "$PROJECT_DIR/database/seed.sql"
"$MYSQL_BIN" --protocol=SOCKET --socket="$DB_SOCKET" -uroot "$DB_NAME" < "$PROJECT_DIR/database/migrations/2026_10_19_remove_contact_phone.sql"

echo "[4/6] ضبط بيئة المعاينة المحلية..."
LOCAL_APP_KEY="$($PHP_BIN -r 'echo bin2hex(random_bytes(32));')"
{
    echo "APP_ENV=local"
    echo "APP_DEBUG=true"
    echo "APP_URL=$SITE_URL"
    echo "APP_KEY=$LOCAL_APP_KEY"
    echo "APP_TIMEZONE=Asia/Baghdad"
    echo
    echo "DB_HOST=$SITE_HOST"
    echo "DB_PORT=$DB_PORT"
    echo "DB_NAME=$DB_NAME"
    echo "DB_USER=root"
    echo "DB_PASS="
    echo "DB_CHARSET=utf8mb4"
    echo
    echo "SESSION_NAME=almaha_finall_preview"
    echo "SESSION_LIFETIME=7200"
    echo "SESSION_SECURE=false"
    echo
    echo "SMTP_ENABLED=true"
    echo "SMTP_HOST=$SITE_HOST"
    echo "SMTP_PORT=1"
    echo "SMTP_ENCRYPTION=none"
    echo "SMTP_TIMEOUT=5"
    echo "SMTP_FROM=preview@localhost.test"
    echo "SMTP_FROM_NAME=Al-Maha Preview"
    echo "SMTP_TO=info@almaha-group.com"
    echo
    echo "UPLOAD_MAX_MB=8"
    echo "LOG_MAX_MB=10"
    echo "LOG_MAX_FILES=5"
    echo "LOG_VIEWER_MAX_ENTRIES=2000"
} > "$PROJECT_DIR/.env"

if [[ -f "$PHP_PID" ]] && kill -0 "$(<"$PHP_PID")" 2>/dev/null; then
    echo "[5/6] خادم الموقع يعمل مسبقاً."
else
    echo "[5/6] تشغيل خادم الموقع مع ملفات CSS وJavaScript والصور..."
    nohup "$PHP_BIN" -S "${SITE_HOST}:${SITE_PORT}" -t "$PROJECT_DIR/public" "$PROJECT_DIR/public/router.php" \
        > "$PHP_LOG" 2>&1 &
    echo $! > "$PHP_PID"
fi

for attempt in {1..20}; do
    home_status="$($CURL_BIN --silent --output /dev/null --write-out '%{http_code}' "$SITE_URL/" || true)"
    css_status="$($CURL_BIN --silent --output /dev/null --write-out '%{http_code}' "$SITE_URL/assets/css/site.css" || true)"
    js_status="$($CURL_BIN --silent --output /dev/null --write-out '%{http_code}' "$SITE_URL/assets/js/site.js" || true)"
    [[ "$home_status" == "200" && "$css_status" == "200" && "$js_status" == "200" ]] && break
    sleep 1
done

[[ "${home_status:-000}" == "200" ]] || print_error "صفحة الموقع لم تبدأ. راجع $PHP_LOG"
[[ "${css_status:-000}" == "200" ]] || print_error "ملف CSS لم يُحمّل. راجع $PHP_LOG"
[[ "${js_status:-000}" == "200" ]] || print_error "ملف JavaScript لم يُحمّل. راجع $PHP_LOG"

echo "[6/6] اكتمل التشغيل بنجاح."
echo
echo "الموقع العربي:    $SITE_URL/"
echo "الموقع الإنجليزي: $SITE_URL/en"
echo "لوحة الإدارة:     $SITE_URL/admin/login"
echo

if command -v open >/dev/null 2>&1; then
    open "$SITE_URL/"
elif command -v xdg-open >/dev/null 2>&1; then
    xdg-open "$SITE_URL/" >/dev/null 2>&1 &
fi

echo "اترك نافذة التشغيل هذه مفتوحة طوال فترة مشاهدة الموقع."
echo "لإيقاف المعاينة أغلق النافذة أو شغّل ملف: إيقاف الموقع local.command"
echo

while [[ -f "$PHP_PID" ]] && kill -0 "$(<"$PHP_PID")" 2>/dev/null; do
    sleep 2
done

