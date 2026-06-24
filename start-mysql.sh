#!/bin/bash
# Start development MySQL instance on port 3307
# Requires: MySQL 8.0 installed

DATADIR="/tmp/vuno-mysql-data"
SOCKET="/tmp/vuno-mysql.sock"
PORT=3307
PIDFILE="/tmp/vuno-mysql.pid"
ERRORLOG="/tmp/vuno-mysql-error.log"

if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
  echo "MySQL is already running (PID: $(cat $PIDFILE))"
  exit 0
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

if [ ! -d "$DATADIR/mysql" ]; then
  echo "First run: initializing MySQL data directory..."
  rm -rf "$DATADIR"
  mysqld --initialize-insecure --datadir="$DATADIR" --log-error="$ERRORLOG" 2>&1
  if [ $? -ne 0 ]; then
    echo "Failed to initialize MySQL data directory"
    cat "$ERRORLOG"
    exit 1
  fi

  /usr/sbin/mysqld --datadir="$DATADIR" --socket="$SOCKET" --port="$PORT" \
    --log-error="$ERRORLOG" --mysqlx=0 --pid-file="$PIDFILE" --daemonize 2>&1
  sleep 3

  echo "Running database schema..."
  mysql -S "$SOCKET" -u root < "$SCRIPT_DIR/database/schema.sql"
  MYSQL_VUNO_PASS="${MYSQL_VUNO_PASS:-MYSQL_VUNO_PASS_PLACEHOLDER}"
  mysql -S "$SOCKET" -u root -e \
    "CREATE USER IF NOT EXISTS 'vuno'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_VUNO_PASS}';
     GRANT ALL PRIVILEGES ON vuno_app_leed.* TO 'vuno'@'localhost';
     FLUSH PRIVILEGES;"

  mysqladmin -S "$SOCKET" -u root shutdown
  sleep 1
  echo "Database ready. Starting MySQL..."
fi

/usr/sbin/mysqld --datadir="$DATADIR" --socket="$SOCKET" --port="$PORT" \
  --log-error="$ERRORLOG" --mysqlx=0 --pid-file="$PIDFILE" --daemonize 2>&1

sleep 2

if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
  echo "MySQL started (PID: $(cat $PIDFILE))"
  echo "Socket: $SOCKET"
  echo "Port:   $PORT"
else
  echo "Failed to start MySQL"
  tail -5 "$ERRORLOG"
  exit 1
fi
