/* =========================================================
   AURA — storefront scripts (no build step needed)
   ========================================================= */
(function () {
    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => [...el.querySelectorAll(s)];

    /* ---------- Header on scroll ---------- */
    const header = $('.header');
    const onScroll = () => header && header.classList.toggle('scrolled', scrollY > 30);
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- Menu drawer & search ---------- */
    const overlay = $('.overlay');
    const closeAll = () => {
        $$('.drawer, .search-box, .overlay').forEach(el => el.classList.remove('open'));
    };
    $$('[data-open]').forEach(btn => btn.addEventListener('click', e => {
        e.preventDefault();
        closeAll();
        $(btn.dataset.open).classList.add('open');
        overlay.classList.add('open');
        const input = $(btn.dataset.open + ' input');
        if (input) setTimeout(() => input.focus(), 300);
    }));
    $$('[data-close], .overlay').forEach(el => el.addEventListener('click', closeAll));
    addEventListener('keydown', e => e.key === 'Escape' && closeAll());

    // After adding / changing an item, the page comes back with the bag open
    const openCart = () => { $('.cart-drawer').classList.add('open'); overlay.classList.add('open'); };
    if (document.body.hasAttribute('data-open-cart')) setTimeout(openCart, 150);

    /* ---------- Quantity buttons ---------- */
    $$('.qty').forEach(q => {
        const input = $('input', q);
        $$('button', q).forEach(b => b.addEventListener('click', () => {
            const v = Math.max(1, Math.min(+input.max || 99, (+input.value || 1) + (+b.dataset.step)));
            input.value = v;
            if (q.dataset.autosubmit !== undefined) q.closest('form').submit();
        }));
    });

    /* ---------- Reveal on scroll ---------- */
    const io = new IntersectionObserver(entries => entries.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add('visible'); io.unobserve(en.target); }
    }), { threshold: .12 });
    $$('.on-scroll').forEach(el => io.observe(el));

    /* ---------- Intro: perfume falling from the sky ---------- */
    const intro = $('#intro');
    const ready = () => document.body.classList.add('ready');

    if (!intro) { ready(); return; }

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    let timers = [];
    let finished = false;

    function finish() {
        if (finished) return;
        finished = true;
        timers.forEach(clearTimeout);
        intro.classList.add('done');
        document.body.classList.remove('no-scroll');
        sessionStorage.setItem('auraIntroSeen', '1');
        const v = $('video', intro);
        if (v) v.pause();
        setTimeout(ready, 150);
    }

    $('.intro-skip', intro).addEventListener('click', finish);

    function play() {
        intro.style.transition = '';
        finished = false;
        timers.forEach(clearTimeout);
        timers = [];
        intro.className = '';
        document.body.classList.add('no-scroll');
        document.body.classList.remove('ready');

        // Option 1: a real video (public/videos/intro.mp4)
        const video = $('video', intro);
        if (video) {
            video.currentTime = 0;
            video.play().catch(finish);
            video.onended = finish;
            timers.push(setTimeout(finish, 15000)); // safety
            return;
        }

        // Option 2: animated intro
        startCanvas();
        const at = (ms, fn) => timers.push(setTimeout(fn, ms));
        at(350, () => intro.classList.add('falling'));
        at(2250, () => { intro.classList.add('landed'); burst(); });
        at(2900, () => intro.classList.add('titled'));
        at(5200, finish);
    }

    /* Canvas: twinkling stars, sparkles trailing the bottle, burst on landing */
    const canvas = $('canvas', intro);
    const ctx = canvas ? canvas.getContext('2d') : null;
    let stars = [], parts = [], raf;

    function resize() {
        if (!canvas) return;
        canvas.width = innerWidth * devicePixelRatio;
        canvas.height = innerHeight * devicePixelRatio;
        ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
    }

    function startCanvas() {
        if (!canvas) return;
        resize();
        stars = Array.from({ length: 160 }, () => ({
            x: Math.random() * innerWidth, y: Math.random() * innerHeight,
            r: Math.random() * 1.4 + .2, p: Math.random() * Math.PI * 2, s: Math.random() * .03 + .01,
        }));
        parts = [];
        cancelAnimationFrame(raf);
        loop();
    }

    function burst() {
        const cx = innerWidth / 2, cy = innerHeight * .6;
        for (let i = 0; i < 140; i++) {
            const a = Math.random() * Math.PI * 2, sp = Math.random() * 9 + 2;
            parts.push({ x: cx, y: cy, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp * .55 - 3, life: 1, r: Math.random() * 2.4 + .6, leaf: false });
        }
        for (let i = 0; i < 22; i++) { // autumn leaves
            const a = -Math.PI * Math.random(), sp = Math.random() * 6 + 3;
            parts.push({ x: cx, y: cy, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp - 2, life: 1, r: Math.random() * 7 + 6, leaf: true, rot: Math.random() * 6, vr: (Math.random() - .5) * .2 });
        }
    }

    function loop() {
        ctx.clearRect(0, 0, innerWidth, innerHeight);

        for (const s of stars) {
            s.p += s.s;
            ctx.globalAlpha = .35 + Math.sin(s.p) * .35;
            ctx.fillStyle = '#ffe9d6';
            ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, 7); ctx.fill();
        }

        // sparkles behind the falling bottle
        if (intro.classList.contains('falling') && !intro.classList.contains('landed')) {
            const b = $('.intro-bottle', intro).getBoundingClientRect();
            for (let i = 0; i < 4; i++) {
                parts.push({ x: b.left + b.width * (.35 + Math.random() * .3), y: b.top + b.height * .3, vx: (Math.random() - .5) * 1.2, vy: -Math.random() * 2, life: 1, r: Math.random() * 1.8 + .4, leaf: false });
            }
        }

        parts = parts.filter(p => p.life > 0);
        for (const p of parts) {
            p.x += p.vx; p.y += p.vy; p.vy += p.leaf ? .06 : .12; p.vx *= .985; p.life -= p.leaf ? .006 : .014;
            ctx.globalAlpha = Math.max(p.life, 0);
            if (p.leaf) {
                p.rot += p.vr;
                ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot);
                ctx.fillStyle = ['#b5561f', '#d9862f', '#8a3d14'][Math.floor(p.r) % 3];
                ctx.beginPath(); ctx.ellipse(0, 0, p.r, p.r * .45, 0, 0, 7); ctx.fill();
                ctx.restore();
            } else {
                ctx.fillStyle = '#ffb877';
                ctx.shadowColor = '#ff9a4a'; ctx.shadowBlur = 8;
                ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 7); ctx.fill();
                ctx.shadowBlur = 0;
            }
        }
        ctx.globalAlpha = 1;

        if (!finished) raf = requestAnimationFrame(loop);
    }
    addEventListener('resize', resize);

    // "Play Atmosphere" button replays the intro
    $$('[data-replay-intro]').forEach(b => b.addEventListener('click', play));

    // Coming back from another page (Back button restores the page from cache)
    addEventListener('pageshow', e => {
        if (e.persisted && finished) { document.body.classList.remove('no-scroll'); ready(); }
    });

    if (reduced || sessionStorage.getItem('auraIntroSeen')) {
        intro.style.transition = 'none'; // hide instantly, no fade
        intro.classList.add('done');
        finished = true;
        document.body.classList.remove('no-scroll');
        ready();
    } else {
        play();
    }
})();
