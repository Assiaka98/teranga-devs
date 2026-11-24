# TerangaDevs

- Plain PHP 8 web app with no framework, Composer manifest, build step, or test suite. Web root is historically the repo root (`php -S localhost:8000`), while refactoring toward a clean MVC layout (`core/Router.php`, `app/Models/`, `app/Controllers/`, `app/Views/`, `public/index.php`) on the `refactor-mvc` workflow[cite: 9].
- Existing legacy routes are real files (`index.php`, `question.php?id=...`, `articles.php`, `article_read.php?slug=...`, `profile.php?id=...`), alongside the MVC front controller at `public/index.php` (`Router` -> `QuestionController` -> `QuestionModel`), reachable at `/public/index.php` unless the docroot is set to `public/` (`php -S localhost:8000 -t public`). `app/Views/` and `public/assets/` are being populated progressively.
- Tailwind comes from a CDN in `includes/header.php` (dark UI aesthetic); `assets/CSS/style.css` is unreferenced. `assets/js/main.js` is loaded from `includes/footer.php`.

## Architecture & Business Rules

- **MySQL/MariaDB via PDO:** Standardize database access, schema definitions, and queries on MySQL/MariaDB using PDO prepared statements (`prepare` / `execute`) with `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` and `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`. Do not add dual SQLite/MySQL branching logic. Keep `config/teranga_devs.sql` and `seed.php` aligned with the active MySQL/MariaDB schema (`articles.slug`, `extrait`, `couverture`, `date_publication`, `users.titre_bio`, `avatar`).
- **MVC boundaries:** Keep SQL queries strictly inside `app/Models/`, request handling, session checks, validation, and orchestration inside `app/Controllers/`, and HTML/Tailwind rendering inside `app/Views/` (no SQL queries inside views).
- **Security & hygiene:** Always use PDO prepared statements for SQL, escape HTML output with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`, validate uploaded files, and never commit secrets or API keys.

## Setup and verification

- `config/db.php` is tracked despite being in `.gitignore`. The target local connection is MySQL/MariaDB (`teranga_devs`) via PDO.
- Initialize the schema with `mysql -u root < config/teranga_devs.sql`, then open `/seed.php` in a browser (or run via PHP) to populate demo data. Seeded demo users share password `teranga2026`.
- No lint/test scripts exist. Check changes with `find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l`, then manually exercise the affected page/API and MVC routes with MySQL/MariaDB running.

## Gotchas

- `includes/auth_guard.php` checks `basename($_SERVER['PHP_SELF'])` against a page allowlist. Do not add it to `/api/*.php`: unauthenticated calls get redirected to `login.php` HTML instead of JSON. API endpoints do their own session checks.
- API include paths are inconsistent (`config/db.php`, `../config/db.php`, `includes/auth_guard.php`); run the dev server from the repo root when testing legacy endpoints.
- `config/teranga_devs.sql` is stale: it lacks `articles.slug`, `extrait`, `couverture`, `date_publication`, and `users.titre_bio`, `avatar`, which article/profile pages expect. Do not treat it as the full current schema without updating it.
- `users.karma` and `users.reputation` are separate: profile displays `karma`, votes update `karma`, while Q&A pages display `reputation`. `responses.est_solution` is the schema column, but `api/post_solution.php` and `question.php` also reference `is_solution`.
- `public/index.php` accepts GET `/`, `/questions`, `/questions/{id}` and POST `/`, `/questions` (JSON or form). Keep `app/Models/` queries clean, portable across MySQL/MariaDB versions, and strictly parameterized via PDO.
- Root `index.php` posts its question form to nonexistent `post_question.php`; `assets/js/main.js` instead expects `#question-form` and `api/post_question.php`. Do not assume that flow works without wiring it.
- Cover uploads go to `uploads/` with relative DB paths like `uploads/cover_*.jpeg`; the directory is not ignored by Git.