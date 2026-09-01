<?php
$__layout = 'app';
$pageTitle = '404 — Not Found';
?>
<div style="padding:96px 0;text-align:center;max-width:480px;margin:0 auto">
    <span class="text-mono" style="font-size:.75rem;color:var(--t4);letter-spacing:.1em">404</span>
    <h1 style="font-size:2.4rem;font-weight:800;letter-spacing:-.04em;margin-top:12px;color:var(--t1)">Page not found</h1>
    <p style="font-size:.95rem;color:var(--t2);line-height:1.7;margin-top:12px">
        The route <code style="font-family:var(--mono);font-size:.85em;padding:2px 6px;background:var(--surface-2);border-radius:4px;color:var(--accent-light)"><?= e($_SERVER['REQUEST_URI'] ?? '/') ?></code> doesn't exist.
    </p>
    <div style="display:flex;justify-content:center;gap:10px;margin-top:28px">
        <a href="/" class="btn btn-primary">Go home</a>
        <a href="/projects" class="btn">Browse projects</a>
    </div>
</div>