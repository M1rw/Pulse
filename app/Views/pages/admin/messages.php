<?php
/**
 * Admin: Messages Inbox
 */
$__layout = 'app';
$pageTitle = 'Messages — ' . config('app.name');
?>

<div class="flex justify-between items-center flex-wrap gap-4">
    <div>
        <span class="section-label">// <?= $messages->count() ?> messages</span>
        <h1 class="page-title">Inbox</h1>
    </div>
    <a href="/admin" class="btn btn-ghost">← Dashboard</a>
</div>

<div class="adm-inbox mt-8">
    <?php if ($messages->isEmpty()): ?>
        <div class="adm-inbox-empty">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span class="text-mono" style="font-size:.82rem;color:var(--t3)">Inbox zero — all messages read</span>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $m): ?>
        <div class="adm-msg <?= !$m['is_read'] ? 'adm-msg--unread' : '' ?>">
            <div class="adm-msg-hd">
                <div>
                    <span class="adm-msg-name"><?= e($m['name']) ?></span>
                    <span class="adm-msg-email text-mono">&lt;<?= e($m['email']) ?>&gt;</span>
                    <?php if (!$m['is_read']): ?>
                        <span class="adm-unread-pill">Unread</span>
                    <?php endif; ?>
                </div>
                <span style="font-size:.75rem;color:var(--t3);font-family:var(--mono)"><?= date('M j, Y · H:i', strtotime($m['created_at'])) ?></span>
            </div>

            <h3 class="adm-msg-subject mt-2"><?= e($m['subject']) ?></h3>

            <div class="adm-msg-body mt-3">
                <?= nl2br(e($m['message'])) ?>
            </div>

            <div class="adm-msg-actions mt-4">
                <div class="flex gap-2">
                    <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-sm btn-primary">Reply via email</a>

                    <?php if (!$m['is_read']): ?>
                        <form method="POST" action="/admin/messages/<?= $m['id'] ?>/read" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost">Mark read</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/admin/messages/<?= $m['id'] ?>/unread" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost">Mark unread</button>
                        </form>
                    <?php endif; ?>
                </div>

                <form method="POST" action="/admin/messages/<?= $m['id'] ?>/delete" onsubmit="return confirm('Delete this message?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.adm-inbox { display: flex; flex-direction: column; gap: 12px; }

.adm-inbox-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 48px;
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
}

.adm-msg {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 20px 24px;
    transition: border-color var(--fast);
}

.adm-msg--unread {
    border-left: 3px solid var(--accent);
}

.adm-msg-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.adm-msg-name {
    font-size: .95rem;
    font-weight: 700;
    color: var(--t1);
    margin-right: 8px;
}

.adm-msg-email {
    font-size: .8rem;
    color: var(--t3);
}

.adm-unread-pill {
    display: inline-flex;
    align-items: center;
    font-size: .65rem;
    font-family: var(--mono);
    font-weight: 600;
    letter-spacing: .06em;
    padding: 2px 7px;
    background: var(--accent-dim);
    color: var(--accent-light);
    border: 1px solid var(--accent-border);
    border-radius: var(--r-xs);
    margin-left: 8px;
    text-transform: uppercase;
}

.adm-msg-subject {
    font-size: .95rem;
    font-weight: 600;
    color: var(--t1);
}

.adm-msg-body {
    font-size: .875rem;
    color: var(--t2);
    line-height: 1.7;
    padding: 14px 16px;
    background: var(--bg);
    border: 1px solid var(--line);
    border-radius: var(--r-sm);
}

.adm-msg-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
}
</style>