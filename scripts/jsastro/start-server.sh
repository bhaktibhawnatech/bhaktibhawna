#!/bin/bash
# BB Astro daemon launcher — idempotent, safe to run from cron every N minutes.
#
# Logic:
#   1. Check if daemon responds on 127.0.0.1:8917/health
#   2. If yes, exit 0 (no-op)
#   3. If no, kill stale PID if any, then start fresh via nohup setsid

set -u

HOST=127.0.0.1
PORT=8917
NODE="${BB_ASTRO_NODE:-/opt/alt/alt-nodejs22/root/usr/bin/node}"
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PID_FILE="$APP_DIR/astro-server.pid"
LOG_FILE="$APP_DIR/astro-server.log"

cd "$APP_DIR" || exit 1

# Health check (1s timeout, follow no redirects)
if curl -sS --max-time 1 "http://${HOST}:${PORT}/health" | grep -q '"ok":true'; then
  exit 0
fi

# Daemon not responding. If old PID file exists, try to kill the stale process.
if [ -f "$PID_FILE" ]; then
  OLDPID=$(cat "$PID_FILE")
  if [ -n "$OLDPID" ] && kill -0 "$OLDPID" 2>/dev/null; then
    kill -TERM "$OLDPID" 2>/dev/null
    sleep 1
    kill -KILL "$OLDPID" 2>/dev/null
  fi
  rm -f "$PID_FILE"
fi

# Truncate log if it grew large (>5MB)
if [ -f "$LOG_FILE" ] && [ "$(stat -c%s "$LOG_FILE" 2>/dev/null || stat -f%z "$LOG_FILE")" -gt 5242880 ]; then
  : > "$LOG_FILE"
fi

# Launch detached
nohup setsid "$NODE" "$APP_DIR/astro-server.js" >> "$LOG_FILE" 2>&1 &
NEWPID=$!
echo "$NEWPID" > "$PID_FILE"
disown $NEWPID 2>/dev/null

# Wait briefly and confirm it came up
for i in 1 2 3 4 5 6 7 8; do
  sleep 1
  if curl -sS --max-time 1 "http://${HOST}:${PORT}/health" | grep -q '"ok":true'; then
    echo "started pid=$NEWPID"
    exit 0
  fi
done
echo "FAILED to start daemon — check $LOG_FILE" >&2
exit 1
