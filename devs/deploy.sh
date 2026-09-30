#!/usr/bin/env bash
#
# Deploy futurecampus: pull the production branch and fix permissions on
# the folders the app writes to (uploads and the other runtime folders).
#
# Usage (as root):
#   sudo bash /var/www/html/futurecampus/devs/deploy.sh            # normal deploy
#   sudo bash /var/www/html/futurecampus/devs/deploy.sh --stash    # keep server edits aside, then pull
#   sudo bash /var/www/html/futurecampus/devs/deploy.sh --force    # throw away server edits, match production exactly
#   sudo bash /var/www/html/futurecampus/devs/deploy.sh --perms    # only fix permissions, no pull
#
# Everything is wrapped in main() so bash reads the whole file before running:
# the pull may update this script while it runs.

main() {
    set -euo pipefail

    # ── settings ────────────────────────────────────────────
    local APP_DIR="/var/www/html/futurecampus"
    local BRANCH="production"
    local REMOTE="origin"
    local WEB_USER="www-data"
    local WEB_GROUP="www-data"
    local LOG_FILE="/var/log/futurecampus-deploy.log"

    # folders the app writes to (relative to APP_DIR); created if missing
    local WRITABLE_DIRS=(
        "uploads"               # all uploaded files and every subfolder
        "books"
        "application/cache"     # includes design_jobs (Download Design ZIPs)
        "application/logs"
    )

    # ── options ─────────────────────────────────────────────
    local MODE="normal"
    case "${1:-}" in
        "")        ;;
        --stash)   MODE="stash" ;;
        --force)   MODE="force" ;;
        --perms)   MODE="perms" ;;
        -h|--help) sed -n '2,13p' "$0"; exit 0 ;;
        *)         echo "Unknown option: $1 (use --help)"; exit 1 ;;
    esac

    # ── helpers ─────────────────────────────────────────────
    local C_OK=$'\e[32m' C_WARN=$'\e[33m' C_ERR=$'\e[31m' C_DIM=$'\e[2m' C_END=$'\e[0m'
    log()  { echo "${C_DIM}$(date '+%H:%M:%S')${C_END} $*"; echo "$(date '+%F %T') $*" | sed 's/\x1b\[[0-9;]*m//g' >> "$LOG_FILE"; }
    ok()   { log "${C_OK}✔${C_END} $*"; }
    warn() { log "${C_WARN}!${C_END} $*"; }
    die()  { log "${C_ERR}✘ $*${C_END}"; exit 1; }
    git_app() { git -C "$APP_DIR" -c safe.directory="$APP_DIR" "$@"; }

    # ── pre-checks ──────────────────────────────────────────
    [[ $EUID -eq 0 ]] || { echo "Run as root: sudo bash $0 ${1:-}"; exit 1; }
    touch "$LOG_FILE" 2>/dev/null || LOG_FILE="/tmp/futurecampus-deploy.log"
    [[ -d "$APP_DIR/.git" ]] || die "$APP_DIR is not a git repository"
    id "$WEB_USER" >/dev/null 2>&1 || die "web user '$WEB_USER' does not exist"

    # one deploy at a time
    exec 9>/var/lock/futurecampus-deploy.lock
    flock -n 9 || die "another deploy is already running"

    log "──── deploy started (mode: $MODE) ────"

    # ── 1. pull ─────────────────────────────────────────────
    if [[ "$MODE" != "perms" ]]; then
        local OLD_HEAD NEW_HEAD
        OLD_HEAD=$(git_app rev-parse HEAD)

        log "fetching $REMOTE/$BRANCH ..."
        git_app fetch --prune "$REMOTE" "$BRANCH" || die "git fetch failed (network or credentials?)"

        # files edited directly on the server would be overwritten or block the pull
        local LOCAL_CHANGES
        LOCAL_CHANGES=$(git_app status --porcelain --untracked-files=no)
        if [[ -n "$LOCAL_CHANGES" ]]; then
            case "$MODE" in
                normal)
                    warn "files were changed directly on the server:"
                    echo "$LOCAL_CHANGES" | sed 's/^/      /'
                    die "nothing pulled. Re-run with --stash (keep them aside) or --force (discard them)"
                    ;;
                stash)
                    git_app stash push -m "deploy $(date '+%F %T')" >/dev/null
                    warn "server edits saved with 'git stash' (see: git -C $APP_DIR stash list)"
                    ;;
                force)
                    warn "discarding server edits:"
                    echo "$LOCAL_CHANGES" | sed 's/^/      /'
                    ;;
            esac
        fi

        # be on the production branch
        if [[ "$(git_app rev-parse --abbrev-ref HEAD)" != "$BRANCH" ]]; then
            log "switching to branch $BRANCH"
            local CHECKOUT_FLAGS=()
            [[ "$MODE" == "force" ]] && CHECKOUT_FLAGS=(-f)
            git_app checkout "${CHECKOUT_FLAGS[@]}" -B "$BRANCH" "$REMOTE/$BRANCH" >/dev/null 2>&1 || die "could not switch to $BRANCH"
        fi

        if [[ "$MODE" == "force" ]]; then
            git_app reset --hard "$REMOTE/$BRANCH" >/dev/null
        else
            git_app merge --ff-only "$REMOTE/$BRANCH" >/dev/null 2>&1 \
                || die "server branch has diverged from $REMOTE/$BRANCH. Re-run with --force to match production exactly"
        fi

        NEW_HEAD=$(git_app rev-parse HEAD)
        if [[ "$OLD_HEAD" == "$NEW_HEAD" ]]; then
            ok "already up to date ($(git_app log -1 --format='%h %s'))"
        else
            ok "updated ${OLD_HEAD:0:7} → ${NEW_HEAD:0:7}"
            git_app log -20 --format='      %h %s (%an)' "$OLD_HEAD..$NEW_HEAD"
            log "changed files:"
            git_app diff --stat "$OLD_HEAD" "$NEW_HEAD" | tail -25 | sed 's/^/     /' || true

            local NEW_SQL
            NEW_SQL=$(git_app diff --name-only "$OLD_HEAD" "$NEW_HEAD" -- '*.sql' || true)
            if [[ -n "$NEW_SQL" ]]; then
                warn "SQL files changed — run them on the database if needed:"
                echo "$NEW_SQL" | sed 's/^/      /'
            fi
        fi
    fi

    # ── 2. permissions on writable folders ──────────────────
    #   owner www-data (the web server & cron worker), group www-data
    #   folders 2775: read/write for owner+group; setgid keeps new files in the group
    #   files   664 : read/write for owner+group, read-only for others
    #   (777 is avoided on purpose: it lets any process on the server write into the site)
    local dir
    for dir in "${WRITABLE_DIRS[@]}"; do
        local path="$APP_DIR/$dir"
        mkdir -p "$path"
        chown -R "$WEB_USER":"$WEB_GROUP" "$path"
        find "$path" -type d -exec chmod 2775 {} +
        find "$path" -type f -exec chmod 664 {} +
        ok "permissions set: $dir  ($(find "$path" -type f | wc -l) files)"
    done

    # anything root left behind in the writable folders would break uploads/the worker
    local ROOT_OWNED
    ROOT_OWNED=$(for dir in "${WRITABLE_DIRS[@]}"; do find "$APP_DIR/$dir" -user root -print -quit; done)
    [[ -z "$ROOT_OWNED" ]] || warn "still owned by root: $ROOT_OWNED"

    # ── 3. reload PHP so new code is used (clears opcache) ──
    if [[ "$MODE" != "perms" ]]; then
        local svc
        svc=$(systemctl list-units --type=service --state=running --no-legend 2>/dev/null \
              | awk '{print $1}' | grep -E '^php[0-9.]*-fpm\.service$' | head -1 || true)
        if [[ -n "$svc" ]]; then
            systemctl reload "$svc" && ok "reloaded $svc"
        elif systemctl is-active --quiet apache2; then
            systemctl reload apache2 && ok "reloaded apache2"
        fi
    fi

    # ── 4. sanity checks ────────────────────────────────────
    if crontab -u "$WEB_USER" -l 2>/dev/null | grep -q "design_worker run"; then
        ok "design worker cron is installed for $WEB_USER"
    else
        warn "design worker cron is missing — add it with: crontab -u $WEB_USER -e"
        echo "      * * * * * /usr/bin/php $APP_DIR/index.php design_worker run >> $APP_DIR/application/logs/design_worker.log 2>&1"
    fi

    log "──── deploy finished ────"
}

main "$@"
