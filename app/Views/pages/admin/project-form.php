<?php
/**
 * Admin: Project Create / Edit Form
 */
$__layout = 'app';
$pageTitle = ($project ? 'Edit' : 'New') . ' Project — ' . config('app.name');
$old = flash('old');
$p = $project ? (array) $project : ($old ?? []);
?>

<div class="flex justify-between items-center flex-wrap gap-4">
    <div>
        <span class="section-label">// <?= $project ? 'Edit project' : 'New project' ?></span>
        <h1 class="page-title"><?= $project ? e($project['title']) : 'Create project' ?></h1>
    </div>
    <a href="/admin/projects" class="btn btn-ghost">← Projects</a>
</div>

<div class="adm-form-card mt-8">
    <form method="POST" action="<?= $project ? '/admin/projects/' . $project['id'] . '/update' : '/admin/projects' ?>">
        <?= csrf_field() ?>

        <div class="grid-2">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= e($p['title'] ?? '') ?>" placeholder="e.g. NetPulse CLI" required>
            </div>
            <div class="form-group">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" class="form-control"
                       value="<?= e($p['slug'] ?? '') ?>" placeholder="e.g. netpulse-cli" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Short Description</label>
            <input type="text" id="description" name="description" class="form-control"
                   value="<?= e($p['description'] ?? '') ?>" placeholder="One-sentence tagline" required>
        </div>

        <div class="form-group">
            <label for="long_description">Architecture & Story</label>
            <textarea id="long_description" name="long_description" class="form-control" style="min-height:200px"
                      placeholder="Detailed write-up: challenges solved, design decisions, key components…"><?= e($p['long_description'] ?? '') ?></textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control">
                    <?php foreach (['framework', 'fullstack', 'api', 'tool', 'service', 'library', 'web'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($p['category'] ?? 'framework') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="published" <?= ($p['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($p['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="archived" <?= ($p['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="tech_stack">Tech Stack (comma-separated)</label>
            <input type="text" id="tech_stack" name="tech_stack" class="form-control"
                   value="<?= e($p['tech_stack'] ?? '') ?>" placeholder="PHP 8.2, SQLite, Vue.js, Redis">
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="live_url">Live URL</label>
                <input type="url" id="live_url" name="live_url" class="form-control"
                       value="<?= e($p['live_url'] ?? '') ?>" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label for="source_url">Source Code URL</label>
                <input type="url" id="source_url" name="source_url" class="form-control"
                       value="<?= e($p['source_url'] ?? '') ?>" placeholder="https://github.com/…">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control"
                       value="<?= e($p['sort_order'] ?? 0) ?>" min="0">
            </div>
            <div class="form-group" style="display:flex;align-items:center;padding-top:26px">
                <label style="display:inline-flex;align-items:center;gap:10px;font-size:.875rem;font-weight:500;color:var(--t2);cursor:pointer">
                    <input type="checkbox" name="featured" value="1" <?= !empty($p['featured']) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--accent)">
                    Show on homepage (featured)
                </label>
            </div>
        </div>

        <div class="flex gap-3 mt-8">
            <button type="submit" class="btn btn-primary btn-lg"><?= $project ? 'Save changes' : 'Create project' ?></button>
            <a href="/admin/projects" class="btn btn-lg btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<style>
.adm-form-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 28px;
    max-width: 860px;
}
</style>

<script>
// Live slug generation
const titleIn = document.getElementById('title');
const slugIn  = document.getElementById('slug');
if (titleIn && slugIn) {
    titleIn.addEventListener('input', () => {
        if (!slugIn.dataset.manual) {
            slugIn.value = titleIn.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });
    slugIn.addEventListener('input', () => { slugIn.dataset.manual = '1'; });
}
</script>