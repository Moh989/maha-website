(() => {
    'use strict';

    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const themeButton = document.querySelector('[data-theme-toggle]');
    const setTheme = (theme) => {
        root.dataset.theme = theme;
        try { localStorage.setItem('almaha-theme', theme); } catch (_) {}
        themeButton?.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    };
    themeButton?.addEventListener('click', () => setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark'));

    const header = document.querySelector('[data-header]');
    const progress = document.querySelector('.scroll-progress span');
    const updateScroll = () => {
        header?.classList.toggle('scrolled', window.scrollY > 24);
        if (progress) {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.width = `${max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0}%`;
        }
    };
    updateScroll();
    window.addEventListener('scroll', updateScroll, { passive: true });

    const menu = document.querySelector('[data-mobile-menu]');
    const openButton = document.querySelector('[data-menu-open]');
    const closeButton = document.querySelector('[data-menu-close]');
    let menuTrigger = null;
    const setMenu = (open) => {
        if (!menu) return;
        menu.classList.toggle('open', open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        openButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('menu-open', open);
        if (open) {
            menuTrigger = document.activeElement;
            closeButton?.focus();
        } else if (menuTrigger instanceof HTMLElement) {
            menuTrigger.focus();
        }
    };
    openButton?.addEventListener('click', () => setMenu(true));
    closeButton?.addEventListener('click', () => setMenu(false));
    menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu?.classList.contains('open')) setMenu(false);
    });

    document.querySelectorAll('.nav-dropdown > button').forEach((button) => {
        button.addEventListener('click', () => button.setAttribute('aria-expanded', button.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'));
        button.parentElement?.addEventListener('focusout', (event) => {
            if (!button.parentElement?.contains(event.relatedTarget)) button.setAttribute('aria-expanded', 'false');
        });
    });

    document.querySelectorAll('[data-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-slide]')];
        const prev = slider.querySelector('[data-slider-prev]');
        const next = slider.querySelector('[data-slider-next]');
        const pagination = slider.querySelector('[data-slider-pagination]');
        if (!slides.length) return;

        let index = 0;
        let timer = null;
        let pointerStart = null;
        let paused = false;
        const dots = slides.map((_, dotIndex) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('aria-label', `${document.documentElement.lang === 'ar' ? 'انتقل إلى الشريحة' : 'Go to slide'} ${dotIndex + 1}`);
            button.addEventListener('click', () => show(dotIndex, true));
            pagination?.append(button);
            return button;
        });

        const show = (newIndex, userInitiated = false) => {
            index = (newIndex + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === index;
                slide.classList.toggle('active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('active', dotIndex === index);
                dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
            });
            if (userInitiated) restart();
        };
        const start = () => {
            if (slides.length < 2 || reducedMotion || paused || document.hidden) return;
            clearInterval(timer);
            timer = window.setInterval(() => show(index + 1), 6500);
        };
        const stop = () => { clearInterval(timer); timer = null; };
        const restart = () => { stop(); start(); };

        prev?.addEventListener('click', () => show(index - 1, true));
        next?.addEventListener('click', () => show(index + 1, true));
        slider.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') { event.preventDefault(); show(index - 1, true); }
            if (event.key === 'ArrowRight') { event.preventDefault(); show(index + 1, true); }
            if (event.key === 'Home') { event.preventDefault(); show(0, true); }
            if (event.key === 'End') { event.preventDefault(); show(slides.length - 1, true); }
        });
        slider.addEventListener('pointerdown', (event) => { pointerStart = event.clientX; });
        slider.addEventListener('pointerup', (event) => {
            if (pointerStart === null) return;
            const distance = event.clientX - pointerStart;
            if (Math.abs(distance) > 45) show(distance > 0 ? index - 1 : index + 1, true);
            pointerStart = null;
        });
        slider.addEventListener('mouseenter', () => { paused = true; stop(); });
        slider.addEventListener('mouseleave', () => { paused = false; start(); });
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        show(0);
        start();
    });

    if ('IntersectionObserver' in window && !reducedMotion) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -30px' });
        document.querySelectorAll('.reveal, .reveal-image').forEach((element) => observer.observe(element));
    } else {
        document.querySelectorAll('.reveal, .reveal-image').forEach((element) => element.classList.add('visible'));
    }

    const counters = document.querySelectorAll('[data-counter]');
    const runCounter = (element) => {
        const target = Number(element.dataset.counter || 0);
        if (!Number.isFinite(target) || reducedMotion) return;
        const startTime = performance.now();
        const duration = 1300;
        const frame = (time) => {
            const progressValue = Math.min(1, (time - startTime) / duration);
            const eased = 1 - Math.pow(1 - progressValue, 3);
            element.textContent = Math.round(target * eased).toLocaleString(document.documentElement.lang === 'ar' ? 'en-US' : 'en-US');
            if (progressValue < 1) requestAnimationFrame(frame);
        };
        requestAnimationFrame(frame);
    };
    if ('IntersectionObserver' in window) {
        const counterObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) { runCounter(entry.target); counterObserver.unobserve(entry.target); }
        }), { threshold: .5 });
        counters.forEach((counter) => counterObserver.observe(counter));
    }

    document.querySelectorAll('[data-validate]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            let valid = true;
            const lang = document.documentElement.lang;
            form.querySelectorAll('[required]').forEach((field) => {
                if (!field.checkValidity()) {
                    valid = false;
                    field.setAttribute('aria-invalid', 'true');
                } else {
                    field.removeAttribute('aria-invalid');
                }
            });
            const message = form.querySelector('textarea[name="message"]');
            if (message && message.value.trim().length > 0 && message.value.trim().length < 20) {
                valid = false;
                message.setCustomValidity(lang === 'ar' ? 'أدخل 20 حرفاً على الأقل.' : 'Enter at least 20 characters.');
            } else if (message) {
                message.setCustomValidity('');
            }
            if (!valid) {
                event.preventDefault();
                form.querySelector(':invalid')?.focus();
            }
        });
        form.querySelectorAll('input, select, textarea').forEach((field) => field.addEventListener('input', () => {
            field.removeAttribute('aria-invalid');
            field.setCustomValidity('');
        }));
    });
})();
