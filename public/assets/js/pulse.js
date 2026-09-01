/**
 * Pulse Framework - Interactive Client Utilities
 */

document.addEventListener('DOMContentLoaded', function () {
    // ── Mobile Navigation ──────────────────────────────────────────
    var navToggle = document.querySelector('.mobile-menu-btn') || document.querySelector('.nav-toggle');
    var navLinks = document.querySelector('#mobile-nav') || document.querySelector('.nav-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
    }

    // ── Dynamic Header Expansion on Scroll ─────────────────────────
    var headerWrap = document.querySelector('.header-wrap');
    if (headerWrap) {
        var isScrolled = false;
        var onScroll = function () {
            var scrolled = window.scrollY > 20;
            if (scrolled !== isScrolled) {
                isScrolled = scrolled;
                if (isScrolled) {
                    headerWrap.classList.add('scrolled');
                } else {
                    headerWrap.classList.remove('scrolled');
                }
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ── Animated Stat Counters ─────────────────────────────────────
    var statNumbers = document.querySelectorAll('[data-count]');
    if (statNumbers.length > 0) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                    var current = 0;
                    var step = Math.max(1, Math.floor(target / 30));
                    var timer = setInterval(function () {
                        current += step;
                        if (current >= target) {
                            el.textContent = target;
                            clearInterval(timer);
                        } else {
                            el.textContent = current;
                        }
                    }, 30);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.2 });

        statNumbers.forEach(function (el) { observer.observe(el); });
    }

    // ── Interactive Terminal Emulator ─────────────────────────────
    var termBody = document.getElementById('terminal-body');
    var termInput = document.getElementById('term-input');
    var typedEl = document.getElementById('typed-text');

    if (typedEl) {
        var phrases = [
            "Building high-performance PHP micro-frameworks.",
            "Crafting clean distributed systems & APIs.",
            "Zero bloat. Complete control. Pure craftsmanship."
        ];
        var pIndex = 0, charIndex = 0, isDeleting = false;
        function typeEffect() {
            var currentPhrase = phrases[pIndex];
            if (isDeleting) {
                typedEl.textContent = currentPhrase.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typedEl.textContent = currentPhrase.substring(0, charIndex + 1);
                charIndex++;
            }

            var speed = isDeleting ? 30 : 60;
            if (!isDeleting && charIndex === currentPhrase.length) {
                speed = 2200;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                pIndex = (pIndex + 1) % phrases.length;
                speed = 400;
            }
            setTimeout(typeEffect, speed);
        }
        typeEffect();
    }

    // Terminal Commands
    var commands = {
        help: "Available commands:\n  • projects  - List featured projects\n  • stats     - Show framework telemetry\n  • skills    - Display core technical capabilities\n  • whoami    - About the author\n  • contact   - How to reach out\n  • clear     - Clear terminal screen",
        projects: "Featured Works:\n  1. [Pulse] Custom PHP micro-framework\n  2. [Vector Commerce] Headless real-time e-commerce API\n  3. [NetPulse CLI] Terminal network monitor with ANSI sparklines\n  4. [PixelForge] On-the-fly image manipulation service\n  → Visit /projects for full repository archive.",
        stats: "System Telemetry:\n  • Engine: PHP " + (window.PULSE_PHP_VERSION || "8.2.12") + "\n  • Database: SQLite WAL mode (zero config)\n  • Routing: Hand-rolled regex dispatcher (~0.4ms)\n  • Status: 100% Operational",
        skills: "Core Domains:\n  • Backend: PHP 8.2+, MySQL, SQLite, Redis, REST/GraphQL\n  • Frontend: Modern JS/TS, Vue, Canvas, CSS Architecture\n  • Systems: Docker, Linux/Nginx, Socket Programming, CI/CD",
        whoami: "Author: Senior Full-Stack Engineer & Systems Architect.\nFocus: Building resilient, bloat-free software from first principles.",
        contact: "Contact Channels:\n  • Form: /contact\n  • GitHub: github.com/yourhandle\n  • Direct message responses within 24 hours.",
        clear: "__CLEAR__"
    };

    window.runTermCommand = function (cmd) {
        if (!termBody) return;
        cmd = (cmd || '').trim().toLowerCase();
        if (!cmd) return;

        if (cmd === 'clear') {
            termBody.innerHTML = '<div class="terminal-line text-muted">// Terminal cleared. Type "help" for options.</div>';
            return;
        }

        var output = commands[cmd] || ("Command not found: '" + cmd + "'. Type 'help' for command list.");
        
        var cmdLine = document.createElement('div');
        cmdLine.className = 'terminal-line term-cmd-echo';
        cmdLine.innerHTML = '<span class="prompt">pulse $</span> <span class="cmd-text">' + escapeHtml(cmd) + '</span>';
        termBody.appendChild(cmdLine);

        var outLine = document.createElement('div');
        outLine.className = 'terminal-line term-output';
        outLine.innerText = output;
        termBody.appendChild(outLine);

        termBody.scrollTop = termBody.scrollHeight;
    };

    if (termInput) {
        termInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var cmd = termInput.value;
                termInput.value = '';
                runTermCommand(cmd);
            }
        });
    }

    // ── Live Client Project Filter & Search ─────────────────────────
    var projectSearchInput = document.getElementById('project-search');
    var projectCards = document.querySelectorAll('.project-card-item');

    if (projectSearchInput && projectCards.length > 0) {
        projectSearchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            projectCards.forEach(function (card) {
                var title = (card.getAttribute('data-title') || '').toLowerCase();
                var desc = (card.getAttribute('data-desc') || '').toLowerCase();
                var tech = (card.getAttribute('data-tech') || '').toLowerCase();
                var cat = (card.getAttribute('data-cat') || '').toLowerCase();

                if (!q || title.includes(q) || desc.includes(q) || tech.includes(q) || cat.includes(q)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Utility: HTML escape
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
