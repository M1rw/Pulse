<?php
/**
 * Admin: Projects
 */
$__layout = 'app';
$pageTitle = 'Manage Projects — ' . config('app.name');
?>

<div class="flex justify-between items-center flex-wrap gap-4">
    <div>
        <span class="section-label">// <?= $projects->count() ?> projects</span>
        <h1 class="page-title">Projects</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="/admin/projects/new" class="btn btn-primary">+ New project</a>
        <a href="/admin" class="btn btn-ghost">Dashboard</a>
    </div>
</div>

<div class="adm-table-wrap mt-8">
    <table class="adm-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Created</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td class="text-mono" style="color:var(--t4);font-size:.78rem"><?= $p['id'] ?></td>
                <td>
                    <a href="/projects/<?= e($p['slug']) ?>" target="_blank" style="font-weight:600;color:var(--t1);font-size:.9rem"><?= e($p['title']) ?></a>
                    <div style="font-size:.72rem;color:var(--t4);font-family:var(--mono);margin-top:2px">/projects/<?= e($p['slug']) ?></div>
                </td>
                <td>
                    <span class="badge badge-<?= match($p['category']) {
                        'framework' => 'framework', 'fullstack' => 'fullstack',
                        'api' => 'api', 'tool' => 'tool',
                        'service' => 'service', 'library' => 'library',
                        default => 'accent'
                    } ?>"><?= e($p['category']) ?></span>
                </td>
                <td>
                    <span class="adm-status adm-status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span>
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-family:var(--mono);font-size:.75rem;color:<?= $p['featured'] ? 'var(--amber)' : 'var(--t4)' ?>">
                        <?php if ($p['featured']): ?>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Yes
                        <?php else: ?>—<?php endif; ?>
                    </span>
                </td>
                <td style="font-size:.78rem;color:var(--t3);font-family:var(--mono)"><?= time_ago($p['created_at']) ?></td>
                <td>
                    <div class="flex gap-2" style="justify-content:flex-end">
                        <a href="/admin/projects/<?= $p['id'] ?>/edit" class="btn btn-sm">Edit</a>
                        <form method="POST" action="/admin/projects/<?= $p['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete <?= e(addslashes($p['title'])) ?>?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.adm-table-wrap {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    overflow: hidden;
}

.adm-table {
    width: 100%;
    border-collapse: collapse;
}

.adm-table th {
    text-align: left;
    padding: 12px 16px;
    font-family: var(--mono);
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--t3);
    border-bottom: 1px solid var(--line);
    background: var(--bg);
    font-weight: 500;
}

.adm-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}

.adm-table tr:last-child td { border-bottom: none; }
.adm-table tr:hover td { background: var(--surface); }

.adm-status {
    font-family: var(--mono);
    font-size: .72rem;
    padding: 2px 8px;
    border-radius: var(--r-xs);
    border: 1px solid;
}

.adm-status-published { background: var(--emerald-dim); color: #6ee7b7; border-color: rgba(52,211,153,.3); }
.adm-status-draft     { background: rgba(251,191,36,.1); color: #fcd34d; border-color: rgba(251,191,36,.3); }
.adm-status-archived  { background: var(--surface); color: var(--t3); border-color: var(--line-2); }
</style>