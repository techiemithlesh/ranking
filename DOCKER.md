# Running rankers locally with Docker

PHP 8.1 + Apache (same PHP as production) and MySQL 8.0, without touching the
machine's own Apache/PHP 7.4 setup.

| What  | Where |
|-------|-------|
| App   | http://localhost:8081 |
| MySQL | `127.0.0.1:3307`, user `rankers` / `rankers` (root: `root`), database `futurecampus` |

## Start / stop

```bash
docker compose up -d --build   # first time, or after changing docker/php/*
docker compose up -d           # normal start
docker compose down            # stop (database is kept)
```

Code is bind-mounted, so edits under this folder show up immediately with no rebuild.
PHP errors go to `docker logs -f rankers-app`.

## Database

The container has its **own copy** of `futurecampus`, so nothing you do here
affects the original database. On the first start of an empty volume it imports
every `*.sql` file in `docker/db/` in name order:

- `01-futurecampus.sql`: full dump of the local `futurecampus` database
- `02-view_student_details.sql`: recreates `view_student_details` without the
  production-only `campus_user` DEFINER, which doesn't exist locally

These dumps contain real data and are git-ignored (`docker/db/.gitignore`).

To refresh the copy from the local MySQL:

```bash
mysqldump -uroot -p --single-transaction --routines --triggers --set-gtid-purged=OFF \
  --ignore-table=futurecampus.view_student_details futurecampus > docker/db/01-futurecampus.sql
docker compose down -v         # -v deletes the old database volume
docker compose up -d
```

`application/config/database.php` reads `DB_HOST`/`DB_USER`/`DB_PASS`/`DB_NAME`
from the environment (set in `docker-compose.yml`) and falls back to the local
values otherwise, so `http://localhost/rankers` on Apache keeps working.
