<?php
/**
 * About Page
 */
$__layout = 'app';
$pageTitle = 'About & Architecture — ' . config('app.name');
$author = config('app.author');
?>

<div class="page-header">
    <span class="section-label">// About & philosophy</span>
    <h1 class="page-title"><?= e($author['name']) ?></h1>
    <p class="page-subtitle"><?= e($author['role']) ?></p>
</div>

<div class="about-grid">

    <!-- Main content -->
    <div class="about-main">

        <!-- Philosophy -->
        <section class="about-card">
            <h2 class="about-card-title">Engineering Philosophy</h2>
            <div class="prose mt-4">
                <p>
                    I build on a simple conviction: <strong>software should be transparent, fast, and fit exactly what it needs to do</strong>. Most modern web stacks ship hundreds of megabytes of abstraction before you write a single line of your own code. That's not power — it's debt.
                </p>
                <p>
                    This portfolio is the proof. Instead of reaching for Laravel or Symfony, I designed <strong>Pulse</strong> from scratch — a hand-rolled PHP 8.2 micro-framework with a PSR-4 autoloader, regex route dispatcher, onion middleware pipeline, and active-record models. Every layer exists because it needs to, not because a generator put it there.
                </p>
                <p>
                    Understanding a system at the socket, WAL-lock, and memory-lifecycle level is what separates engineers who debug infrastructure from those who are confused by it.
                </p>
            </div>
        </section>

        <!-- Capabilities -->
        <section class="about-card mt-8">
            <h2 class="about-card-title">Core Capabilities</h2>
            <p class="mt-2" style="font-size:.875rem;color:var(--t3)">Tools chosen for reliability and maintainability — not trend-chasing.</p>

            <div class="cap-grid mt-6">
                <div class="cap-item">
                    <div class="cap-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                    <div>
                        <h3 class="cap-name">Backend & Systems</h3>
                        <p class="cap-desc">High-performance API design, microservices, socket workers, custom framework engineering.</p>
                        <div class="tags mt-3">
                            <span class="tag">PHP 8.2+</span><span class="tag">Go</span>
                            <span class="tag">Node.js</span><span class="tag">REST</span><span class="tag">WebSockets</span>
                        </div>
                    </div>
                </div>

                <div class="cap-item">
                    <div class="cap-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.657 4.03 3 9 3s9-1.343 9-3V5"/><path d="M3 12c0 1.657 4.03 3 9 3s9-1.343 9-3"/></svg>
                    </div>
                    <div>
                        <h3 class="cap-name">Databases & Caching</h3>
                        <p class="cap-desc">Schema design, query optimization, WAL transactions, caching tiers, data streaming.</p>
                        <div class="tags mt-3">
                            <span class="tag">PostgreSQL</span><span class="tag">MySQL</span>
                            <span class="tag">SQLite WAL</span><span class="tag">Redis</span>
                        </div>
                    </div>
                </div>

                <div class="cap-item">
                    <div class="cap-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                    <div>
                        <h3 class="cap-name">Frontend</h3>
                        <p class="cap-desc">Design systems, reactive SPAs, vanilla CSS, TypeScript architecture.</p>
                        <div class="tags mt-3">
                            <span class="tag">TypeScript</span><span class="tag">Vue.js</span>
                            <span class="tag">CSS Grid</span><span class="tag">Canvas</span>
                        </div>
                    </div>
                </div>

                <div class="cap-item">
                    <div class="cap-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                    </div>
                    <div>
                        <h3 class="cap-name">DevOps & Tooling</h3>
                        <p class="cap-desc">Containerized deployments, reverse proxies, CI/CD automation, CLI tooling.</p>
                        <div class="tags mt-3">
                            <span class="tag">Docker</span><span class="tag">Nginx</span>
                            <span class="tag">Linux</span><span class="tag">Bash</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Framework blueprint -->
        <section class="about-card mt-8">
            <h2 class="about-card-title">How Pulse Works</h2>
            <p class="mt-2" style="font-size:.875rem;color:var(--t3)">Four core systems, built from scratch, zero vendor dependencies.</p>

            <div class="bp-list mt-6">
                <div class="bp-item">
                    <span class="bp-num text-mono">01</span>
                    <div>
                        <h4 class="bp-title">Auto-wiring DI Container</h4>
                        <p class="bp-desc">Resolves constructor parameters via PHP 8 Reflection — no config files required.</p>
                    </div>
                </div>
                <div class="bp-item">
                    <span class="bp-num text-mono">02</span>
                    <div>
                        <h4 class="bp-title">Onion Middleware Pipeline</h4>
                        <p class="bp-desc">Requests pass through Session and CSRF layers before reaching the dispatcher.</p>
                    </div>
                </div>
                <div class="bp-item">
                    <span class="bp-num text-mono">03</span>
                    <div>
                        <h4 class="bp-title">Named Regex Route Dispatcher</h4>
                        <p class="bp-desc">Parses <code>/projects/{slug}</code> URI captures in sub-millisecond time.</p>
                    </div>
                </div>
                <div class="bp-item">
                    <span class="bp-num text-mono">04</span>
                    <div>
                        <h4 class="bp-title">Active Record + SQLite WAL</h4>
                        <p class="bp-desc">Native PDO prepared statements, WAL journaling, zero-overhead data layer.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Sidebar -->
    <aside class="about-sidebar">
        <div class="about-card">
            <h3 class="specs-heading">Quick info</h3>
            <div class="specs-list mt-5">
                <div class="spec-row">
                    <span class="spec-label">Location</span>
                    <span class="spec-val">Remote / Global</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Experience</span>
                    <span class="spec-val">5+ Years</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Primary stack</span>
                    <span class="spec-val">PHP · JS · SQL</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Status</span>
                    <span class="spec-val" style="color:var(--emerald)">Available</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Response SLA</span>
                    <span class="spec-val">&lt; 24 hours</span>
                </div>
            </div>
            <a href="/contact" class="btn btn-primary mt-6" style="width:100%;justify-content:center">
                Get in touch
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </aside>
</div>

<style>
.about-grid {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 24px;
    align-items: start;
}

.about-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 28px;
}

.about-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--t1);
}

.cap-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.cap-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.cap-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--r-sm);
    background: var(--accent-dim);
    border: 1px solid var(--accent-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-light);
    flex-shrink: 0;
    margin-top: 2px;
}

.cap-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--t1);
    margin-bottom: 4px;
}

.cap-desc {
    font-size: 0.8rem;
    color: var(--t3);
    line-height: 1.5;
}

.bp-list { display: flex; flex-direction: column; gap: 0; }

.bp-item {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    padding: 14px 0;
}

.bp-num {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--t4);
    padding-top: 3px;
    width: 20px;
    flex-shrink: 0;
}

.bp-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--t1);
    margin-bottom: 4px;
}

.bp-desc {
    font-size: 0.82rem;
    color: var(--t3);
    line-height: 1.5;
}

@media (max-width: 960px) {
    .about-grid { grid-template-columns: 1fr; }
    .cap-grid { grid-template-columns: 1fr; }
}
</style>