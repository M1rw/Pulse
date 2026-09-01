<?php $__layout = 'app'; $pageTitle = '419 — Session Expired'; ?>
<div style="padding:96px 0;text-align:center;max-width:480px;margin:0 auto">
    <span class="text-mono" style="font-size:.75rem;color:var(--t4);letter-spacing:.1em">419</span>
    <h1 style="font-size:2.4rem;font-weight:800;letter-spacing:-.04em;margin-top:12px;color:var(--t1)">Session expired</h1>
    <p style="font-size:.95rem;color:var(--t2);line-height:1.7;margin-top:12px">
        Your CSRF token has expired — this is a security feature. Go back and try again.
    </p>
    <div style="margin-top:28px">
        <a href="javascript:history.back()" class="btn btn-primary">Go back</a>
    </div>
</div>