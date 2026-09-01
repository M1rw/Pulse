<?php
/**
 * Main Layout.
 * 
 * Pulse Framework - Obsidian Dev Command Center Design System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? config('app.name') . ' — ' . config('app.tagline')) ?></title>
    
    <!-- Global Pulse Styles -->
    <link rel="stylesheet" href="/assets/css/pulse.css">

    <script>
        window.PULSE_PHP_VERSION = "<?= PHP_VERSION ?>";
    </script>
</head>
<body>
    <div class="shell">
        <!-- Modern Centered Floating Header -->
        <header class="header-wrap">
            <div class="header-inner">
                <!-- Brand (Left) -->
                <a href="/" class="brand">
                    <span class="brand-text"><?= e(config('app.name')) ?></span>
                </a>

                <?php 
                    $uri = $_SERVER['REQUEST_URI'] ?? '/';
                    $uriPath = parse_url($uri, PHP_URL_PATH) ?? '/';
                ?>

                <!-- Navigation Links (Centered Middle Island) -->
                <nav class="nav-middle">
                    <a href="/" class="nav-link <?= $uriPath === '/' ? 'active' : '' ?>">Home</a>
                    <a href="/projects" class="nav-link <?= str_starts_with($uriPath, '/projects') ? 'active' : '' ?>">Projects</a>
                    <a href="/about" class="nav-link <?= $uriPath === '/about' ? 'active' : '' ?>">About</a>
                    <a href="/contact" class="nav-link <?= $uriPath === '/contact' ? 'active' : '' ?>">Contact</a>
                </nav>

                <!-- Actions (Right) -->
                <div class="header-actions">
                    <a href="/admin" class="admin-link <?= str_starts_with($uriPath, '/admin') ? 'active' : '' ?>">
                        <span>Admin</span>
                    </a>
                    <?php if ($gh = config('app.author.github')): ?>
                        <a href="<?= e($gh) ?>" target="_blank" class="icon-link" aria-label="GitHub" title="GitHub">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                        </a>
                    <?php endif; ?>
                    <button class="mobile-menu-btn" aria-label="Toggle navigation">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer -->
            <div class="mobile-nav" id="mobile-nav">
                <a href="/" class="mobile-link <?= $uriPath === '/' ? 'active' : '' ?>">Home</a>
                <a href="/projects" class="mobile-link <?= str_starts_with($uriPath, '/projects') ? 'active' : '' ?>">Projects</a>
                <a href="/about" class="mobile-link <?= $uriPath === '/about' ? 'active' : '' ?>">About & Architecture</a>
                <a href="/contact" class="mobile-link <?= $uriPath === '/contact' ? 'active' : '' ?>">Contact</a>
                <a href="/admin" class="mobile-link <?= str_starts_with($uriPath, '/admin') ? 'active' : '' ?>">Admin Panel</a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="container">
            <!-- Flash Message Alerts -->
            <?php if ($success = flash('success')): ?>
                <div class="flash-msg flash-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span><?= e($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors = flash('errors')): ?>
                <div class="flash-msg flash-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <?php foreach ($errors as $field => $errs): ?>
                            <?php foreach ($errs as $err): ?>
                                <div><?= e($err) ?></div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- @section(content) -->
        </main>

        <!-- Modern Footer -->
        <footer class="footer">
            <div class="container footer-inner">
                <div class="flex items-center gap-4">
                    <span class="text-secondary">&copy; <?= date('Y') ?> <?= e(config('app.author.name')) ?></span>
                    <span class="text-dim">•</span>
                    <span class="footer-tag">Pulse Micro-Framework</span>
                </div>

                <div class="footer-links">
                    <span class="text-muted text-mono" style="font-size:0.8rem">PHP <?= PHP_VERSION ?></span>
                    <span class="footer-tag">SQLite (WAL)</span>
                    <?php if ($gh = config('app.author.github')): ?>
                        <a href="<?= e($gh) ?>" target="_blank" class="btn btn-sm btn-ghost">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                            <span>GitHub</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
    </div>

    <!-- Client Scripts -->
    <script src="/assets/js/pulse.js"></script>
</body>
</html>