#!/usr/bin/env bash
# Run by cPanel Git "Deploy HEAD Commit" via .cpanel.yml.
# Staging always. Production only when the one-shot token ~/PRODUCTION_APPROVED exists
# (created after the owner approves in chat; deleted here before use).
set -Eeuo pipefail
REPO="$HOME/repositories/envitechal-wordpres"
LOGDIR="$HOME/deploy-logs"
mkdir -p "$LOGDIR"
STAMP=$(date +%Y%m%dT%H%M%S)
LOG="$LOGDIR/cpanel-$STAMP.log"
cd "$REPO"
{
  echo "== Staging deploy ($(git rev-parse --short HEAD))"
  bash scripts/deploy-staging-theme.sh
  if [ -f "$HOME/PRODUCTION_APPROVED" ]; then
    rm -f "$HOME/PRODUCTION_APPROVED"
    echo "== Production deploy (owner approved)"
    ETA_CONFIRM_PRODUCTION=yes bash scripts/deploy-production-theme.sh
    wp --path="$HOME/public_html" litespeed-purge all || true
  else
    echo "== Production NOT approved: staging only."
  fi
  echo "== DONE"
} >> "$LOG" 2>&1
