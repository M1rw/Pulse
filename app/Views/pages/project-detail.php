<?php
/**
 * Project Detail Page
 */
$__layout = 'app';
$pageTitle = $project['title'] . ' — ' . config('app.name');
$techs = array_filter(array_map('trim', explode(',', $project['tech_stack'] ?? '')));
?>

<div class="pd-back">
    <a href="/projects" class="pd-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        All projects
    </a>
    <span class="text-mono" style="font-size:.72rem;color:var(--t4)">/projects/<?= e($project['slug']) ?></span>
</div>

<div class="pd-hero mt-6">
    <div>
        <span class="badge badge-<?= match($project['category']) {
            'framework' => 'framework', 'fullstack' => 'fullstack',
            'api' => 'api', 'tool' => 'tool', 'service' => 'service',
            'library' => 'library', default => 'accent'
        } ?>"><?= e($project['category']) ?></span>
    </div>

    <h1 class="pd-title mt-4"><?= e($project['title']) ?></h1>
    <p class="pd-desc mt-3"><?= e($project['description']) ?></p>

    <div class="pd-actions mt-6">
        <?php if (!empty($project['live_url'])): ?>
            <a href="<?= e($project['live_url']) ?>" target="_blank" class="btn btn-primary btn-lg">
                Live app
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
        <?php endif; ?>
        <?php if (!empty($project['source_url'])): ?>
            <a href="<?= e($project['source_url']) ?>" target="_blank" class="btn btn-lg">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                Source code
            </a>
        <?php endif; ?>
        <a href="/contact" class="btn btn-ghost btn-lg">Inquire about similar work</a>
    </div>
</div>

<!-- Tech stack -->
<div class="pd-tech-card mt-8">
    <span class="section-label">// Technology stack</span>
    <div class="tags mt-3">
        <?php foreach ($techs as $tech): ?>
            <span class="tag"><?= e($tech) ?></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Content + Specs -->
<div class="pd-body mt-8">
    <div class="pd-prose">
        <div class="pd-card">
            <span class="section-label">// Architecture & design decisions</span>
            <div class="prose mt-5">
                <?php foreach (explode("\n\n", $project['long_description'] ?: $project['description']) as $para): ?>
                    <?php if (trim($para)): ?><p><?= e(trim($para)) ?></p><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside class="pd-specs">
        <div class="pd-card">
            <span class="specs-heading">Project specs</span>
            <div class="specs-list mt-5">
                <div class="spec-row">
                    <span class="spec-label">Status</span>
                    <span class="spec-val" style="display:inline-flex;align-items:center;gap:6px">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--emerald);flex-shrink:0"></span>
                        <span style="color:var(--emerald)"><?= $project['status'] === 'published' ? 'Production' : ucfirst($project['status']) ?></span>
                    </span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Category</span>
                    <span class="spec-val"><?= ucfirst(e($project['category'])) ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Created</span>
                    <span class="spec-val text-mono" style="font-size:.82rem"><?= date('M j, Y', strtotime($project['created_at'])) ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Updated</span>
                    <span class="spec-val text-mono" style="font-size:.82rem"><?= time_ago($project['updated_at']) ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">License</span>
                    <span class="spec-val">MIT</span>
                </div>
            </div>
        </div>
    </aside>
</div>

<style>
.pd-back {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pd-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    color: var(--t3);
    padding: 5px 10px;
    border: 1px solid var(--line);
    border-radius: var(--r-sm);
    background: var(--surface);
    transition: all var(--fast);
}

.pd-back-link:hover { color: var(--t1); border-color: var(--line-2); }

.pd-hero {
    max-width: 780px;
}

.pd-title {
    font-size: 2.4rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.15;
    color: var(--t1);
}

.pd-desc {
    font-size: 1.05rem;
    color: var(--t2);
    line-height: 1.7;
}

.pd-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.pd-tech-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    padding: 18px 20px;
}

.pd-body {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 20px;
    align-items: start;
}

.pd-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 24px;
}

@media (max-width: 860px) {
    .pd-body { grid-template-columns: 1fr; }
    .pd-title { font-size: 1.8rem; }
}
</style>