<?php
/**
 * Admin Dashboard
 */
$__layout = 'app';
$pageTitle = 'Admin Dashboard — ' . config('app.name');
?>

<div class="adm-hd">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <span class="section-label" style="margin-bottom:0">// Control center</span>
            <span class="adm-live-dot-wrap">
                <span class="adm-live-dot"></span>
                <span class="text-mono" style="font-size:.7rem;color:var(--emerald)">LIVE</span>
            </span>
        </div>
        <h1 class="page-title">Dashboard</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="/admin/projects/new" class="btn btn-primary">New project</a>
        <a href="/admin/messages" class="btn">Inbox <?php if ($stats['unread'] > 0): ?><span class="adm-badge"><?= $stats['unread'] ?></span><?php endif; ?></a>
        <a href="/" target="_blank" class="btn btn-ghost">Public site</a>
    </div>
</div>

<!-- Metric cards -->
<div class="grid-4 mt-8">
    <div class="adm-metric">
        <span class="adm-metric-label">Projects</span>
        <div class="adm-metric-val"><?= $stats['projects'] ?></div>
        <a href="/admin/projects" class="adm-metric-link">Manage</a>
    </div>
    <div class="adm-metric">
        <span class="adm-metric-label">Messages</span>
        <div class="adm-metric-val"><?= $stats['messages'] ?></div>
        <a href="/admin/messages" class="adm-metric-link">View all</a>
    </div>
    <div class="adm-metric">
        <span class="adm-metric-label">Unread</span>
        <div class="adm-metric-val <?= $stats['unread'] > 0 ? 'text-accent' : '' ?>"><?= $stats['unread'] ?></div>
        <span class="adm-metric-sub">Pending review</span>
    </div>
    <div class="adm-metric">
        <span class="adm-metric-label">Today's events</span>
        <div class="adm-metric-val"><?= $stats['activity']['today'] ?? 0 ?></div>
        <span class="adm-metric-sub">Logged activity</span>
    </div>
</div>

<!-- Activity + Messages -->
<div class="grid-2 mt-8">
    <div class="adm-panel">
        <div class="adm-panel-hd">
            <div>
                <h3 class="adm-panel-title">System activity</h3>
                <span class="adm-panel-sub">Recent events</span>
            </div>
            <button class="btn btn-sm btn-ghost" onclick="window.location.reload()">Refresh</button>
        </div>
        <div class="activity-list" style="padding:0 20px">
            <?php foreach ($activity as $a): ?>
            <div class="activity-item">
                <span class="activity-dot" style="background:<?= match($a['event_type']) {
                    'deploy' => 'var(--emerald)', 'visit' => 'var(--teal)',
                    'contact' => 'var(--amber)', 'create_project' => 'var(--accent-light)',
                    'delete_project' => 'var(--rose)', default => '#8b5cf6',
                } ?>"></span>
                <span class="activity-desc"><?= e($a['description']) ?></span>
                <span class="activity-time"><?= time_ago($a['created_at']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="adm-panel">
        <div class="adm-panel-hd">
            <div>
                <h3 class="adm-panel-title">Unread messages</h3>
                <span class="adm-panel-sub">Waiting for reply</span>
            </div>
            <a href="/admin/messages" class="btn btn-sm">All</a>
        </div>

        <?php if ($messages->isEmpty()): ?>
            <div style="padding:32px 20px;text-align:center">
                <div class="flex items-center gap-2" style="justify-content:center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span style="font-size:.82rem;color:var(--t3);font-family:var(--mono)">Inbox zero</span>
                </div>
            </div>
        <?php else: ?>
            <div class="adm-msg-list">
                <?php foreach ($messages as $m): ?>
                <div class="adm-msg-item">
                    <div class="flex justify-between items-center">
                        <span style="font-size:.9rem;font-weight:600;color:var(--t1)"><?= e($m['name']) ?></span>
                        <span style="font-size:.72rem;color:var(--t3);font-family:var(--mono)"><?= time_ago($m['created_at']) ?></span>
                    </div>
                    <div style="font-size:.82rem;color:var(--t2);margin-top:3px"><?= e($m['subject']) ?></div>
                    <div class="flex gap-2 mt-3">
                        <form method="POST" action="/admin/messages/<?= $m['id'] ?>/read" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost" style="font-size:.72rem">Mark read</button>
                        </form>
                        <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-sm" style="font-size:.72rem">Reply</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.adm-hd {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.adm-live-dot-wrap {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.adm-live-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--emerald);
    animation: live-pulse 2.2s ease-in-out infinite;
}

.adm-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--accent);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    border-radius: 100px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
}

.adm-metric {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.adm-metric-label {
    font-size: .72rem;
    color: var(--t3);
    font-family: var(--mono);
    text-transform: uppercase;
    letter-spacing: .05em;
}

.adm-metric-val {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -.04em;
    color: var(--t1);
    font-family: var(--mono);
    margin-top: 6px;
}

.adm-metric-link, .adm-metric-sub {
    font-size: .75rem;
    margin-top: 8px;
}

.adm-metric-link { color: var(--accent-light); }
.adm-metric-sub  { color: var(--t3); }

.adm-panel {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    overflow: hidden;
}

.adm-panel-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
}

.adm-panel-title {
    font-size: .9rem;
    font-weight: 700;
    color: var(--t1);
}

.adm-panel-sub {
    font-size: .72rem;
    color: var(--t3);
    font-family: var(--mono);
    display: block;
    margin-top: 2px;
}

.adm-msg-list { padding: 0 20px; }

.adm-msg-item {
    padding: 12px 0;
}
</style>