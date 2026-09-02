# Pulse

> A custom PHP micro-framework and portfolio site. Built from scratch, no bloat.

## What Makes This Different

This isn't Laravel. It isn't Symfony. It's my own framework — every component exists because I needed it and understand exactly why it's there. The router, the DI container, the middleware stack, the template engine, the model layer — all hand-written with deliberate design decisions.

## Features

- **Custom Router** — Regex-based with named captures, optional params, and per-route middleware
- **DI Container** — Auto-wiring constructor injection, singletons, and factory bindings
- **Middleware Stack** — Onion-style (request goes in, response bubbles out)
- **Template Engine** — Layout inheritance, variable injection, no compilation step
- **Active Record Models** — Thin wrapper over PDO with fluent query builders
- **Validation** — Rule-based with custom messages, throws on failure
- **CSRF Protection** — Token generation, session-based verification, timing-safe comparison
- **CLI Tool** — Migrate, seed, serve, route listing, stats — all with colorized output
- **Hand-written CSS** — No framework. Custom properties, grid, flexbox, dark theme.
- **SQLite-first** — Zero config. Swap to MySQL via .env when ready.

## Quick Start

```bash
# copy environment config
cp .env.example .env

# install dependencies (dotenv only)
composer install

# setup the database
php cli/pulse.php migrate
php cli/pulse.php seed

# start the dev server
php cli/pulse.php serve
```

Open `http://localhost:8080` in your browser.

## Deploying to Vercel

Pulse is pre-configured for one-click deployment on [Vercel](https://vercel.com) using the `vercel-php` serverless runtime:

1. Push your repository to GitHub / GitLab / Bitbucket.
2. Import your repository in Vercel.
3. Deploy!

The application includes `vercel.json` routing and `api/index.php` serverless function handlers. On serverless instances, SQLite database initialization (schema migrations & seed data), sessions, and error logs automatically adapt to temporary writable storage (`/tmp`) without manual configuration.

## CLI Commands

```
php cli/pulse.php serve          # Start dev server on :8080
php cli/pulse.php migrate        # Run database migrations
php cli/pulse.php seed           # Seed sample data
php cli/pulse.php fresh          # Drop, migrate, seed (clean slate)
php cli/pulse.php routes         # List all registered routes
php cli/pulse.php stats          # Show database statistics
php cli/pulse.php cache:clear    # Clear cached files
```

## Project Structure

```
├── app/
│   ├── Core/           # Framework internals
│   │   ├── Application.php      # DI container + kernel
│   │   ├── Router.php           # Regex-based URL router
│   │   ├── ViewEngine.php       # Template engine with layouts
│   │   ├── Model.php            # Active record base
│   │   ├── Collection.php       # Fluent array wrapper
│   │   ├── DotEnv.php           # .env file parser
│   │   ├── Singleton.php        # Reusable singleton trait
│   │   ├── helpers.php          # Global utility functions
│   │   └── Exceptions/          # Custom exception types
│   ├── Http/           # HTTP layer
│   │   ├── Request.php          # Request wrapper
│   │   ├── Response.php         # Response builder
│   │   ├── Controllers/         # Route handlers
│   │   ├── Middleware/          # Request middleware
│   │   └── Requests/            # Validation
│   ├── Models/         # Eloquent-like models
│   └── Views/          # Template files
├── cli/
│   └── pulse.php       # CLI tool
├── config/             # Configuration files
├── database/
│   ├── migrations/     # SQL schema files
│   └── seeds/          # Seed data
├── public/             # Web root
│   ├── index.php       # Entry point
│   └── .htaccess       # URL rewriting
├── routes/             # Route definitions
└── storage/            # Logs, cache, database
```

## Architecture Decisions

**Why hand-write a framework?** Because understanding every layer matters. When something breaks at 2am, I don't want to debug through five layers of framework abstraction. I want to know exactly what's happening.

**Why SQLite?** For a portfolio site, SQLite is more than capable. It handles concurrent reads fine, zero configuration, and the database is just a file. When the data gets serious, switch to MySQL with one .env change.

**Why no CSS framework?** Every site built with Tailwind or Bootstrap looks the same. Custom CSS with CSS custom properties gives me a unique visual identity that's immediately recognizable as mine.

**Why PHP in 2026?** PHP 8.2+ is genuinely excellent. Named arguments, readonly properties, enums, fibers, match expressions, intersection types — it's a modern, capable language that powers 77% of the web for a reason.

## Tech Stack

- **PHP 8.2+** — The framework, the models, everything
- **SQLite** — Zero-config database
- **Vanilla CSS** — Custom properties, grid, flexbox
- **Vanilla JS** — No build step, no bundler
- **PDO** — Database access layer

## License

MIT