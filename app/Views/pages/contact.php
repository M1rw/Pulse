<?php
/**
 * Contact Page
 */
$__layout = 'app';
$pageTitle = 'Contact — ' . config('app.name');
$old = flash('old');
?>

<div class="page-header">
    <span class="section-label">// Direct inbox</span>
    <h1 class="page-title">Let's work together</h1>
    <p class="page-subtitle">
        Send a message directly — I respond personally to every inquiry within 24 hours.
    </p>
</div>

<div class="ct-grid">

    <!-- Channels -->
    <div class="ct-info">
        <div class="ct-card">
            <h2 class="ct-card-title">Contact channels</h2>

            <div class="ct-channels mt-5">
                <div class="ct-ch-item">
                    <div class="ct-ch-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <span class="ct-ch-label">Email</span>
                        <span class="ct-ch-val">hello@pulse.dev</span>
                    </div>
                </div>

                <div class="ct-ch-item">
                    <div class="ct-ch-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                    </div>
                    <div>
                        <span class="ct-ch-label">GitHub</span>
                        <a href="<?= e(config('app.author.github')) ?>" target="_blank" class="ct-ch-val"><?= e(config('app.author.github')) ?></a>
                    </div>
                </div>

                <div class="ct-ch-item">
                    <div class="ct-ch-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <span class="ct-ch-label">Response time</span>
                        <span class="ct-ch-val" style="color:var(--emerald)">Within 24 hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="ct-form-wrap">
        <div class="ct-card">
            <h2 class="ct-card-title">Send a message</h2>

            <form method="POST" action="/contact" class="mt-6">
                <?= csrf_field() ?>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= e($old['name'] ?? '') ?>" placeholder="Your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= e($old['email'] ?? '') ?>" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control"
                           value="<?= e($old['subject'] ?? '') ?>" placeholder="What's this about?" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="form-control"
                              style="min-height:140px"
                              placeholder="Tell me about your project, goals, and timeline…" required><?= e($old['message'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
                    Send message
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.ct-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 20px;
    align-items: start;
}

.ct-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    padding: 24px;
}

.ct-card-title {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--t1);
}

.ct-channels { display: flex; flex-direction: column; gap: 20px; }

.ct-ch-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.ct-ch-icon {
    width: 34px;
    height: 34px;
    border-radius: var(--r-sm);
    background: var(--accent-dim);
    border: 1px solid var(--accent-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-light);
    flex-shrink: 0;
}

.ct-ch-label {
    display: block;
    font-size: 0.7rem;
    color: var(--t3);
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-family: var(--mono);
}

.ct-ch-val {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--t1);
}

@media (max-width: 860px) {
    .ct-grid { grid-template-columns: 1fr; }
}
</style>