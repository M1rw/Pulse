<?php
/**
 * Projects Archive
 */
$__layout = 'app';
$pageTitle = 'Projects — ' . config('app.name');
?>

<div class="page-header">
    <div class="flex items-center gap-3">
        <span class="section-label">// <?= $projects->count() ?> projects</span>
    </div>
    <h1 class="page-title">Projects & Systems</h1>
    <p class="page-subtitle">
        Open-source libraries, distributed services, APIs, and developer tooling — all built with deliberate architecture decisions.
    </p>
</div>

<!-- Filters & Search -->
<div class="pf-bar">
    <div class="pf-pills">
        <a href="/projects" class="pf-pill <?= empty($activeCat) ? 'pf-pill--on' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="/projects?cat=<?= e(urlencode($cat)) ?>"
               class="pf-pill <?= ($activeCat ?? '') === $cat ? 'pf-pill--on' : '' ?>">
                <?= e(ucfirst($cat)) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="pf-search-wrap">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="project-search" placeholder="Search projects…" value="<?= e($search ?? '') ?>" autocomplete="off">
    </div>
</div>

<!-- Grid -->
<div class="pf-grid mt-8" id="project-grid">
    <?php if ($projects->isEmpty()): ?>
        <div class="pf-empty" style="grid-column:1/-1">
            <p class="text-mono text-muted" style="font-size:.85rem">No projects match your filter. <a href="/projects">Reset</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $p): ?>
        <a href="/projects/<?= e($p['slug']) ?>"
           class="pf-card project-card-item"
           data-title="<?= e($p['title']) ?>"
           data-desc="<?= e($p['description']) ?>"
           data-tech="<?= e($p['tech_stack'] ?? '') ?>"
           data-cat="<?= e($p['category']) ?>">

            <div class="pf-card-hd">
                <span class="badge badge-<?= match($p['category']) {
                    'framework' => 'framework', 'fullstack' => 'fullstack',
                    'api' => 'api', 'tool' => 'tool',
                    'service' => 'service', 'library' => 'library',
                    default => 'accent'
                } ?>"><?= e($p['category']) ?></span>

                <div class="flex items-center gap-2">
                    <?php if (!empty($p['live_url'])): ?>
                        <span class="pf-link-tag">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Live
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($p['source_url'])): ?>
                        <span class="pf-link-tag">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                            Code
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <h2 class="pf-card-title"><?= e($p['title']) ?></h2>
            <p class="pf-card-desc"><?= e($p['description']) ?></p>

            <div class="tags mt-5">
                <?php foreach (array_slice(array_map('trim', explode(',', $p['tech_stack'] ?? '')), 0, 5) as $tech): ?>
                    <?php if ($tech): ?><span class="tag"><?= e($tech) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="pf-card-ft">
                <span class="pf-card-cta text-mono">
                    View case study
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>
                <span class="text-dim text-mono" style="font-size:.7rem"><?= time_ago($p['created_at']) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.pf-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px 16px;
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-md);
}

.pf-pills { display: flex; flex-wrap: wrap; gap: 4px; }

.pf-pill {
    padding: 5px 12px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--t3);
    border-radius: var(--r-sm);
    border: 1px solid transparent;
    transition: all var(--fast);
}

.pf-pill:hover { color: var(--t1); background: var(--surface-2); }
.pf-pill--on   { color: var(--t1); background: var(--surface-2); border-color: var(--line-2); }

.pf-search-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--bg);
    border: 1px solid var(--line-2);
    border-radius: var(--r-sm);
    padding: 7px 12px;
    color: var(--t3);
    min-width: 220px;
}

.pf-search-wrap input {
    background: none;
    border: none;
    outline: none;
    font-size: 0.85rem;
    color: var(--t1);
    width: 100%;
}

.pf-search-wrap input::placeholder { color: var(--t4); }

.pf-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.pf-card {
    display: flex;
    flex-direction: column;
    padding: 20px;
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    transition: border-color var(--mid) var(--ease), transform var(--mid) var(--ease);
    text-decoration: none;
}

.pf-card:hover {
    border-color: var(--line-2);
    transform: translateY(-2px);
}

.pf-card-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.pf-link-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    font-family: var(--mono);
    color: var(--t3);
    padding: 2px 7px;
    border: 1px solid var(--line);
    border-radius: var(--r-xs);
    background: var(--surface);
}

.pf-card-title {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--t1);
    margin-bottom: 6px;
    transition: color var(--fast);
}

.pf-card:hover .pf-card-title { color: var(--accent-light); }

.pf-card-desc {
    font-size: 0.85rem;
    color: var(--t2);
    line-height: 1.6;
    flex: 1;
}

.pf-card-ft {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 14px;
}

.pf-card-cta {
    font-size: 0.74rem;
    color: var(--t3);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color var(--fast);
}

.pf-card:hover .pf-card-cta { color: var(--accent-light); }

.pf-empty { padding: 48px; text-align: center; }

@media (max-width: 768px) {
    .pf-grid { grid-template-columns: 1fr; }
}
</style>

<script>
const searchInput = document.getElementById('project-search');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.project-card-item').forEach(card => {
            const match = (card.dataset.title + card.dataset.desc + card.dataset.tech).toLowerCase().includes(q);
            card.style.display = match ? '' : 'none';
        });
    });
}
</script>