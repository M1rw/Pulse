<?php
/**
 * Home — Pulse Portfolio
 */
$__layout = 'app';
?>

<!-- Clean Minimal Hero -->
<section class="home-hero">
    <div class="hero-container">
        <h1 class="hero-title">
            Software engineer &<br>systems architect.
        </h1>

        <p class="hero-lead">
            I design backend architectures, custom micro-frameworks, and developer tooling built from first principles. Currently engineering <strong>Pulse</strong> — a zero-dependency PHP micro-framework.
        </p>

        <div class="hero-links">
            <a href="/projects" class="hero-link">
                <span>Selected projects</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="/about" class="hero-link">
                <span>Architecture & philosophy</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="/contact" class="hero-link">
                <span>Get in touch</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- ░░ FEATURED PROJECTS ░░ -->
<section class="home-section mt-16">
    <div class="home-section-hd">
        <div>
            <span class="section-label">// Featured work</span>
            <h2 class="home-section-title">Selected Projects</h2>
        </div>
        <a href="/projects" class="hero-link" style="font-size:0.84rem">
            <span>View all (<?= $stats['projects'] ?>)</span>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="proj-grid mt-8">
        <?php foreach ($projects as $p): ?>
        <a href="/projects/<?= e($p['slug']) ?>" class="proj-card">
            <div class="proj-card-top">
                <span class="badge badge-<?= match($p['category']) {
                    'framework' => 'framework', 'fullstack' => 'fullstack',
                    'api' => 'api', 'tool' => 'tool', 'service' => 'service',
                    'library' => 'library', default => 'accent'
                } ?>"><?= e($p['category']) ?></span>
                <?php if ($p['featured']): ?>
                <span class="proj-featured">
                    Featured
                </span>
                <?php endif; ?>
            </div>

            <h3 class="proj-title"><?= e($p['title']) ?></h3>
            <p class="proj-desc"><?= e($p['description']) ?></p>

            <div class="tags mt-5">
                <?php foreach (array_slice(array_map('trim', explode(',', $p['tech_stack'] ?? '')), 0, 4) as $tech): ?>
                    <?php if ($tech): ?><span class="tag"><?= e($tech) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="proj-footer">
                <span class="proj-link text-mono">
                    View case study
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>
                <span class="text-mono text-dim" style="font-size:.72rem"><?= time_ago($p['created_at']) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ░░ ACTIVITY FEED ░░ -->
<section class="home-section mt-20">
    <div class="home-section-hd mb-6">
        <div>
            <span class="section-label">// System heartbeat</span>
            <h2 class="home-section-title">Recent Activity</h2>
        </div>
        <button class="btn btn-sm btn-ghost" onclick="window.location.reload()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh
        </button>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="activity-list" style="padding:0 20px">
            <?php foreach ($activity as $a): ?>
            <div class="activity-item">
                <span class="activity-dot" style="background:<?= match($a['event_type']) {
                    'deploy' => 'var(--emerald)', 'visit' => 'var(--teal)',
                    'contact' => 'var(--amber)', 'create_project' => 'var(--accent-light)',
                    'delete_project' => 'var(--rose)', default => '#8b5cf6'
                } ?>"></span>
                <span class="activity-desc"><?= e($a['description']) ?></span>
                <span class="activity-time"><?= time_ago($a['created_at']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ░░ STYLES ░░ -->
<style>
/* ── Clean Hero ── */
.home-hero {
    padding: 64px 0 48px;
}

.hero-container {
    max-width: 760px;
}

.hero-title {
    font-size: 3.2rem;
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -0.04em;
    color: var(--t1);
    margin-bottom: 20px;
}

.hero-lead {
    font-size: 1.1rem;
    line-height: 1.75;
    color: var(--t2);
    margin-bottom: 32px;
}

.hero-lead strong {
    color: var(--t1);
    font-weight: 600;
}

.hero-links {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.hero-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--t3);
    transition: color var(--fast), transform var(--fast);
}

.hero-link:hover {
    color: var(--t1);
    transform: translateX(2px);
}

/* ── Section header ── */
.home-section-hd {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
}

.home-section-title {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--t1);
    margin-top: 4px;
}

/* ── Project Cards ── */
.proj-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.proj-card {
    display: flex;
    flex-direction: column;
    padding: 20px;
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    transition: border-color var(--mid) var(--ease), transform var(--mid) var(--ease);
    text-decoration: none;
}

.proj-card:hover {
    border-color: var(--line-2);
    transform: translateY(-2px);
}

.proj-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.proj-featured {
    font-size: 0.7rem;
    font-family: var(--mono);
    font-weight: 500;
    color: var(--t3);
    letter-spacing: 0.03em;
}

.proj-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--t1);
    letter-spacing: -0.02em;
    margin-bottom: 6px;
    transition: color var(--fast);
}

.proj-card:hover .proj-title { color: var(--accent-light); }

.proj-desc {
    font-size: 0.85rem;
    color: var(--t2);
    line-height: 1.6;
    flex: 1;
}

.proj-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 18px;
    padding-top: 16px;
}

.proj-link {
    font-size: 0.75rem;
    color: var(--t3);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color var(--fast);
}

.proj-card:hover .proj-link { color: var(--accent-light); }

/* ── Responsive ── */
@media (max-width: 1100px) {
    .hero-title { font-size: 2.6rem; }
    .proj-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .hero-title { font-size: 2.2rem; }
    .proj-grid { grid-template-columns: 1fr; }
    .hero-links { flex-direction: column; align-items: flex-start; gap: 14px; }
}
</style>