-- Pulse Database Schema
-- 
-- I write SQL by hand. Not because I'm against migrations,
-- but because for a project this size, one SQL file
-- is faster to read, faster to modify, faster to reason about.
-- 
-- Run: php cli/pulse.php migrate

PRAGMA journal_mode=WAL;
PRAGMA foreign_keys=ON;

-- ── projects ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    title           TEXT NOT NULL,
    slug            TEXT NOT NULL UNIQUE,
    description     TEXT NOT NULL DEFAULT '',
    long_description TEXT NOT NULL DEFAULT '',
    tech_stack      TEXT NOT NULL DEFAULT '',
    category        TEXT NOT NULL DEFAULT 'web',
    thumbnail       TEXT NOT NULL DEFAULT '',
    live_url        TEXT NOT NULL DEFAULT '',
    source_url      TEXT NOT NULL DEFAULT '',
    featured        INTEGER NOT NULL DEFAULT 0,
    sort_order      INTEGER NOT NULL DEFAULT 0,
    status          TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft', 'published', 'archived')),
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

-- ── activity logs ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_logs (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type      TEXT NOT NULL,
    description     TEXT NOT NULL DEFAULT '',
    metadata        TEXT NOT NULL DEFAULT '{}',
    ip_address      TEXT NOT NULL DEFAULT '',
    created_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

-- ── contact messages ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT NOT NULL,
    email           TEXT NOT NULL,
    subject         TEXT NOT NULL,
    message         TEXT NOT NULL,
    ip_address      TEXT NOT NULL DEFAULT '',
    is_read         INTEGER NOT NULL DEFAULT 0,
    created_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

-- ── indexes ─────────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_projects_slug ON projects(slug);
CREATE INDEX IF NOT EXISTS idx_projects_status ON projects(status);
CREATE INDEX IF NOT EXISTS idx_projects_featured ON projects(featured);
CREATE INDEX IF NOT EXISTS idx_projects_category ON projects(category);
CREATE INDEX IF NOT EXISTS idx_activity_type ON activity_logs(event_type);
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_messages_read ON contact_messages(is_read);