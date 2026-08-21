# PHP + MySQL Docker Sandbox

A ready-to-use local development environment for practicing PHP and MySQL — no need to install PHP, Apache, or MySQL on your own machine. Everything runs inside Docker containers.

## What's Included

- **PHP 8.4** with Apache (`mysqli` and `pdo_mysql` extensions pre-installed)
- **MySQL 8.4**
- Your code lives in the `src/` folder and is instantly reflected in the browser — no rebuild needed for PHP file changes

## Prerequisites

Install **Docker Desktop** (includes Docker Compose):
- Windows / Mac: https://www.docker.com/products/docker-desktop
- Linux: install Docker Engine + the Compose plugin via your package manager

Verify it's installed:
```bash
docker --version
docker compose version
```

### New to the terminal? Read this first

All the commands in this README need to be typed into a terminal, not a regular file explorer window.

- **Windows**: open **PowerShell** or **Command Prompt** (search for either in the Start menu)
- **Mac**: open **Terminal** (search via Spotlight with `Cmd + Space`)
- **Linux**: open your distro's terminal app

Once it's open, use `cd` to navigate into your project folder before running any commands, e.g.:
```bash
cd path/to/php-sandbox
```
### If you're using VS Code, open the folder in VS Code — just open the integrated terminal with `` Ctrl+` `` (backtick), which opens already inside your project folder.And run the commands 

## Project Structure

```
php-sandbox/
├── .env                  # Your local secrets (you create this — not in git)
├── .gitignore
├── compose.yaml           # Defines the php + mysql services
├── Dockerfile              # Builds the PHP+Apache image
└── src/                   # Your PHP code goes here
    ├── index.php
    ├── phpinfo.php
    └── db-test.php
```

## Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/titas-ops/php-sandbox.git
cd php-sandbox
```

### 2. Create your `.env` file

This file holds your database credentials and is **not** committed to git (it's already in `.gitignore`, so it stays private to your machine). Create a new file named exactly `.env` in the project root (same folder as `compose.yaml`) with the following:

```env
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=college_php
MYSQL_USER=student
MYSQL_PASSWORD=studentpassword
```

**What each value means:**

| Variable | What it's for |
|---|---|
| `MYSQL_ROOT_PASSWORD` | Password for the MySQL admin (`root`) account. Used for full database access. |
| `MYSQL_DATABASE` | Name of the database created automatically on first run. Your PHP code connects to this database. |
| `MYSQL_USER` | A regular (non-root) database user, created automatically for you. |
| `MYSQL_PASSWORD` | Password for `MYSQL_USER`. This is what your PHP code uses day-to-day. |

**You can safely use the values above exactly as-is** — this is a local practice environment, not a real deployed server, so there's no security risk in using simple passwords. Just make sure whatever values you pick match what's expected — the PHP code (like `db-test.php`) reads these same variable names to connect, so don't rename the keys, only the values if you want to customize them.

> ⚠️ If you ever reuse this setup for something real (a deployed app, a shared server), replace these with strong, unique passwords — never commit real credentials to git.

### 3. Build and start the containers

```bash
docker compose up -d --build
```

- `-d` runs it in the background (detached mode)
- `--build` rebuilds the PHP image if the Dockerfile changed (safe to always include)

First run will take a bit longer since it downloads the base images. You should see output ending in something like:

```
✔ Container php-sandbox-mysql-1 Healthy
✔ Container php-sandbox-php-1   Started
```

### 4. Open it in your browser

- http://localhost:8080/index.php — sanity check that PHP is running
- http://localhost:8080/phpinfo.php — full PHP configuration info
- http://localhost:8080/db-test.php — confirms PHP can talk to MySQL

If `db-test.php` says **"Database connection successful!"**, you're good to go.

### Recommended daily workflow

You don't need to rebuild every time you sit down to code. A normal session looks like this:

```bash
docker compose up -d --build   # start everything (once, at the beginning)

# ... write your PHP/HTML/CSS/JS, refresh the browser as you go ...

docker compose down            # stop everything (once, when you're done)
```

- `docker compose up -d --build` — run this **once** when you start working. It builds (if needed) and starts both containers in the background.
- While coding, just save your files in `src/` and refresh the browser — **no command needed**, changes show up instantly.
- `docker compose down` — run this when you're done for the day. This is the **normal, safe way to close everything** — it stops and removes the containers but keeps your database data intact, so next time you run `up` again, your data (and tables) are still there.

You only need to add `--build` again later if you change the `Dockerfile` (e.g. installing a new PHP extension). For everyday PHP/HTML/CSS/JS editing, `--build` isn't strictly necessary but is safe to always include out of habit.

## Writing Your Own Code

Just add `.php` files inside the `src/` folder. Refresh your browser at `http://localhost:8080/your-file.php` — changes are picked up immediately, no restart needed.

To connect to the database from your own PHP files, reuse the pattern in `db-test.php`:

```php
<?php
$host = "mysql"; // Docker service name — always "mysql", not "localhost"
$dbname = getenv("MYSQL_DATABASE");
$username = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

## Useful Commands

| Command | What it does |
|---|---|
| `docker compose up -d --build` | Build (if needed) and start everything in the background |
| `docker compose down` | Stop and remove containers (database data is preserved) |
| `docker compose down -v` | Stop and remove containers **and delete the database data** — use this for a totally fresh start |
| `docker compose logs -f php` | Watch live logs from the PHP container |
| `docker compose logs -f mysql` | Watch live logs from the MySQL container |
| `docker compose exec php bash` | Open a shell inside the PHP container |
| `docker compose exec mysql mysql -u root -p` | Open a MySQL shell (enter your root password from `.env`) |
| `docker compose ps` | See status of running containers |

## Troubleshooting

**`db-test.php` shows a connection error right after first startup**
MySQL can take a few seconds to initialize on the very first run. Wait a moment and refresh — the `compose.yaml` healthcheck should already handle this by making PHP wait for MySQL to be ready, but a stubborn refresh usually clears it up.

**Changed `.env` but the database still has old credentials**
MySQL only runs its setup (creating the database/user) the **first time** it starts with an empty data volume. If you change `.env` after that, run:
```bash
docker compose down -v
docker compose up -d --build
```
This wipes the database and re-initializes it with your new `.env` values.

**Port 8080 or 3306 already in use**
Something else on your machine is using that port. Either stop it, or edit the port mapping in `compose.yaml` (e.g. change `"8080:80"` to `"8081:80"` and visit `localhost:8081` instead).

## Notes

- Never commit your `.env` file — it's already excluded via `.gitignore`.
- Port `3306` is exposed to your host machine so you can optionally connect with a GUI tool like TablePlus, DBeaver, or MySQL Workbench using `localhost:3306` and your `.env` credentials.