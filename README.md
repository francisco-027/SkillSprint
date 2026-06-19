# Microlearn-AI (SkillSprint)

Project for AppDev (ADET) — BSIT 3-1.

An AI-powered micro-learning platform built with **Laravel 13**, **laravel/ui (Bootstrap 5)**, **Vue 3**, and **Vite**.

---

## Local Setup (step by step)

### 1. Clone the repository

- Open Terminal then enter these commands:
```bash
git clone https://github.com/ReizuP/Microlearn-AI.git
cd Microlearn-AI
```

### 2. Install PHP dependencies

```bash
composer install #if this didn't work try: 
composer update
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Create your environment file

- Make a copy of   `.env.example`, you should now have `.env copy.example`
- Then rename the `.env copy.example` to `.env`

### 5. Generate the application key

 - Open terminal and run this command:
```bash
php artisan key:generate
```

### 6. Set up the local PostgreSQL database

For local development, use a local PostgreSQL instance (no Supabase needed).

**Install PostgreSQL** if you haven't: [postgresql.org/download](https://www.postgresql.org/download/) — the installer includes pgAdmin and sets up a `postgres` superuser.

**Create the database.** Open pgAdmin or `psql` and run:

```sql
CREATE DATABASE microlearn;
```

Or via the terminal:

```bash
psql -U postgres -c "CREATE DATABASE microlearn;"
```

Then fill in `.env` with your local credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=microlearn
DB_USERNAME=postgres
DB_PASSWORD=your-local-postgres-password
DB_SSLMODE=disable
```

> **Note:** make sure `pdo_pgsql` is enabled in your `php.ini`. Search for `extension=pdo_pgsql` and uncomment it (remove the `;`), then restart your server.

> **Deploying to production?** Swap these values for your Supabase credentials and set `DB_SSLMODE=require`. See the [Deploying to Render](#deploying-to-render) section below.

### 7. Run the migrations

- Open terminal and run this command:
```bash
php artisan migrate --force
```

### 8. Build the front-end assets

```bash
npm run build
```

---

## Running the app

In one terminal:

```bash
php artisan serve
```

In a second terminal (for live CSS/JS reload while developing):

```bash
npm run dev
```

> Re-run `npm run build` after changing CSS/JS if you're not using `npm run dev`.

---

## Deploying to Render

A `render.yaml` is included at the project root — Render will detect it automatically.

The production database is **Supabase**. Go to your Supabase project dashboard:

> **Project Settings → Database → Connection parameters** — use the **Session mode pooler** on port **5432** (NOT port 6543)

Note your **Host** (e.g. `aws-0-ap-southeast-1.pooler.supabase.com`) and **Username** (e.g. `postgres.xxxxxxxxxxxx`) — you'll need them in step 4 below.

### Steps

1. Push your code to GitHub (make sure `render.yaml` is committed).
2. Go to [render.com](https://render.com) → **New → Web Service** → connect your GitHub repo.
3. Render will auto-detect `render.yaml`. Review the settings and click **Create Web Service**.
4. In the Render dashboard for your service, go to **Environment** and fill in the two secret values marked `sync: false`:
   - `DB_HOST` — your Supabase pooler host (e.g. `aws-0-ap-southeast-1.pooler.supabase.com`)
   - `DB_PASSWORD` — your Supabase database password
5. After the first deploy succeeds, open the **Shell** tab in Render and run:
   ```bash
   php artisan migrate --force
   ```
6. Update `APP_URL` in `render.yaml` (or in the Render dashboard) to your actual `.onrender.com` URL.

> Render's free tier spins down after inactivity — the first request after sleep will be slow. Upgrade to a paid plan to avoid this.

---

## Project structure

| Path | What's there |
|------|--------------|
| `routes/web.php` | Page routes (`/` landing, auth routes, `/home` dashboard) |
| `resources/views/landing.blade.php` | Public landing page (home) |
| `resources/views/auth/login.blade.php` | Login + register page (tab toggle) |
| `resources/views/home.blade.php` | Authenticated dashboard |
| `resources/css/` | Custom stylesheets (`landing.css`, `auth.css`) |
| `resources/sass/app.scss` | Bootstrap entry point |
| `resources/js/` | JavaScript / Vue entry (`app.js`, `bootstrap.js`) |
| `vite.config.js` | Vite build inputs |
| `render.yaml` | Render deployment config |

---

## Useful commands

```bash
php artisan migrate:fresh        # drop all tables and re-migrate
php artisan migrate:fresh --seed # re-migrate and seed
php artisan test                 # run tests
php artisan route:list           # list all routes
php artisan optimize:clear       # clear cached config/routes/views
npm run build                    # compile assets for production
npm run dev                      # Vite dev server with hot reload
```

---

## Troubleshooting

- **`Unable to locate file in Vite manifest`** — you haven't built the assets. Run `npm run build` (or `npm run dev`).
- **`No application encryption key has been specified`** — run `php artisan key:generate`.
- **`could not find driver` / pgsql errors** — enable `extension=pdo_pgsql` in `php.ini`.
- **SSL connection errors to Supabase** — make sure `DB_SSLMODE=require` is set and you're using port `5432` (session pooler), not `6543`.
- **Blank page or 500 after pulling changes** — run `php artisan optimize:clear`, then `composer install` and `npm install`.
- **Styles look broken** — confirm `npm run build` finished without errors and `public/build/` exists.
