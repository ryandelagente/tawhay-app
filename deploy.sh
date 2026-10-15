#!/usr/bin/env bash
# Deploy Tawhay to production (droplet hoahub-prod).
# Usage (Git Bash, from the repo root): ./deploy.sh
# Requires SSH key access to root@143.198.217.126.
set -euo pipefail

SERVER="root@143.198.217.126"
APP_DIR="/var/www/tawhay.app/app"
BRANCH="main"

# The repo lives on a drive that doesn't record ownership, so tell git to trust it.
git() { command git -c safe.directory='*' "$@"; }

# Only deploy what is already committed and pushed to GitHub.
if [ -n "$(git status --porcelain)" ]; then
  echo "Uncommitted changes - commit and push first." >&2; exit 1
fi
git fetch -q origin "$BRANCH"
if [ "$(git rev-parse HEAD)" != "$(git rev-parse "origin/$BRANCH")" ]; then
  echo "Local $BRANCH differs from origin/$BRANCH - push (or pull) first." >&2; exit 1
fi

ssh "$SERVER" "APP_DIR='$APP_DIR' BRANCH='$BRANCH' bash -s" <<'REMOTE'
set -euo pipefail
cd "$APP_DIR"

echo "==> Pulling $BRANCH as the tawhay user"
sudo -u tawhay git pull --ff-only origin "$BRANCH"
sudo -u tawhay git log --oneline -1

# No composer.json/package.json: vendor/ and assets are committed, so nothing to install or build.
# CodeIgniter migrations are disabled (application/config/migration.php), so no migration step.

echo "==> Reloading PHP-FPM (also clears opcache)"
php-fpm8.2 -t
systemctl reload php8.2-fpm
REMOTE

echo "==> Health check"
curl -s -o /dev/null -w "https://tawhay.app -> HTTP %{http_code}\n" https://tawhay.app/
