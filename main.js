/* ============================================================
   PORTFOLIO — main.js
   Full interactive JavaScript: canvas, themes, cursor, 
   testimonial slider, project filter, form validation,
   skill tabs, counters, scroll effects, and more.
   ============================================================ */

'use strict';

/* ─────────────────────────────────────────
   UTILITIES
───────────────────────────────────────── */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);
const raf = requestAnimationFrame.bind(window);

/* ─────────────────────────────────────────
   PRELOADER
───────────────────────────────────────── */
function initPreloader() {
  const loader  = $('#preloader');
  const fill    = $('#preloader-fill');
  const pct     = $('#preloader-pct');
  if (!loader) return;

  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.random() * 18;
    if (progress >= 100) {
      progress = 100;
      clearInterval(interval);
      setTimeout(() => {
        loader.classList.add('hidden');
        document.body.style.overflow = '';
        initFadeUpElements();
      }, 350);
    }
    if (fill) fill.style.width = progress + '%';
    if (pct)  pct.textContent  = Math.round(progress) + '%';
  }, 80);

  document.body.style.overflow = 'hidden';
}

/* ─────────────────────────────────────────
   HERO CANVAS — Particle field
───────────────────────────────────────── */
function initHeroCanvas() {
  const canvas = $('#hero-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];
  let accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();

  function resize() {
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }

  function makeParticle() {
    return {
      x: Math.random() * W,
      y: Math.random() * H,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.4,
      r: Math.random() * 1.5 + 0.5,
      alpha: Math.random() * 0.5 + 0.1,
    };
  }

  function initParticles() {
    particles = Array.from({ length: 120 }, makeParticle);
  }

  function draw() {
    ctx.clearRect(0, 0, W, H);
    accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#8B5CF6';

    particles.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = accentColor;
      ctx.globalAlpha = p.alpha;
      ctx.fill();
    });

    // Draw connecting lines for nearby particles
    ctx.globalAlpha = 1;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 90) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = accentColor;
          ctx.globalAlpha = (1 - dist / 90) * 0.12;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
    ctx.globalAlpha = 1;
    raf(draw);
  }

  resize();
  initParticles();
  draw();
  on(window, 'resize', () => { resize(); initParticles(); });
}

/* ─────────────────────────────────────────
   CUSTOM CURSOR
───────────────────────────────────────── */
function initCursor() {
  const cursor = $('#cursor');
  const label  = $('#cursor-label');
  if (!cursor || window.innerWidth < 640) return;

  let mx = 0, my = 0;

  on(document, 'mousemove', e => {
    mx = e.clientX; my = e.clientY;
    cursor.style.left = mx + 'px';
    cursor.style.top  = my + 'px';
  });

  $$('[data-cursor]').forEach(el => {
    on(el, 'mouseenter', () => {
      cursor.classList.add('expand');
      if (label) {
        label.textContent = el.dataset.cursor;
        cursor.classList.add('labeled');
      }
    });
    on(el, 'mouseleave', () => {
      cursor.classList.remove('expand', 'labeled');
    });
  });

  // Auto-label for links and buttons
  $$('a, button, .proj-card, .srv, .bcard').forEach(el => {
    on(el, 'mouseenter', () => cursor.classList.add('expand'));
    on(el, 'mouseleave', () => cursor.classList.remove('expand', 'labeled'));
  });
}

/* ─────────────────────────────────────────
   SCROLL PROGRESS BAR
───────────────────────────────────────── */
function initScrollProgress() {
  const bar = $('#scroll-progress');
  if (!bar) return;
  on(window, 'scroll', () => {
    const scrolled = window.scrollY;
    const total = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.transform = `scaleX(${scrolled / total})`;
  }, { passive: true });
}

/* ─────────────────────────────────────────
   NAV — Scroll & Mobile
───────────────────────────────────────── */
function initNav() {
  const nav    = $('#nav');
  const burger = $('#hamburger');
  const overlay = $('#mobile-overlay');
  if (!nav) return;

  // Scroll effect
  on(window, 'scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  }, { passive: true });

  // Active link
  const sections = $$('section[id]');
  const links    = $$('.nav-link');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        links.forEach(l => l.classList.remove('active'));
        const a = $(`a[href="#${e.target.id}"]`);
        if (a) a.classList.add('active');
      }
    });
  }, { threshold: 0.35 });

  sections.forEach(s => observer.observe(s));

  // Hamburger
  if (burger && overlay) {
    on(burger, 'click', () => {
      burger.classList.toggle('open');
      overlay.classList.toggle('open');
    });
    $$('.m-link, .m-cta', overlay).forEach(l => {
      on(l, 'click', () => {
        burger.classList.remove('open');
        overlay.classList.remove('open');
      });
    });
    on(overlay, 'click', e => {
      if (e.target === overlay) {
        burger.classList.remove('open');
        overlay.classList.remove('open');
      }
    });
  }
}

/* ─────────────────────────────────────────
   SMOOTH SCROLL
───────────────────────────────────────── */
function initSmoothScroll() {
  const nav = $('#nav');
  on(document, 'click', e => {
    const anchor = e.target.closest('a[href^="#"]');
    if (!anchor) return;
    const target = $(anchor.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    const offset = (nav ? nav.offsetHeight : 70) + 20;
    window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
  });
}

/* ─────────────────────────────────────────
   SCROLL REVEAL
───────────────────────────────────────── */
function initReveal() {
  const els = $$('.reveal');
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const siblings = $$('.reveal', entry.target.parentElement);
      const idx = siblings.indexOf(entry.target);
      setTimeout(() => entry.target.classList.add('visible'), idx * 90);
      io.unobserve(entry.target);
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  els.forEach(el => io.observe(el));
}

function initFadeUpElements() {
  const els = $$('.fade-up');
  els.forEach((el, i) => {
    setTimeout(() => el.classList.add('visible'), 300 + i * 150);
  });
}

/* ─────────────────────────────────────────
   COUNTER ANIMATION
───────────────────────────────────────── */
function initCounters() {
  const counters = $$('.counter');
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const target = parseInt(el.dataset.target, 10);
      const dur = 1800;
      const start = performance.now();
      const update = now => {
        const p = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(eased * target);
        if (p < 1) raf(update);
      };
      raf(update);
      io.unobserve(el);
    });
  }, { threshold: 0.5 });
  counters.forEach(c => io.observe(c));
}

/* ─────────────────────────────────────────
   COLOR THEME SWITCHER
───────────────────────────────────────── */
function initThemeSwitcher() {
  const panel   = $('#theme-panel');
  const toggleBtn = $('#theme-toggle-btn');
  const drawer  = $('#theme-drawer');
  const fontUp  = $('#font-up');
  const fontDown = $('#font-down');
  const fontVal = $('#font-val');
  if (!panel) return;

  let fontSize = 16;

  // Toggle drawer
  on(toggleBtn, 'click', e => {
    e.stopPropagation();
    drawer.classList.toggle('open');
  });
  on(document, 'click', e => {
    if (!panel.contains(e.target)) drawer.classList.remove('open');
  });

  // Mode buttons
  $$('.mode-btn').forEach(btn => {
    on(btn, 'click', () => {
      $$('.mode-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.documentElement.setAttribute('data-theme', btn.dataset.mode);
      localStorage.setItem('pf-theme', btn.dataset.mode);
    });
  });

  // Color swatches
  $$('.swatch').forEach(sw => {
    on(sw, 'click', () => {
      $$('.swatch').forEach(s => s.classList.remove('active'));
      sw.classList.add('active');
      document.documentElement.setAttribute('data-color', sw.dataset.color);
      localStorage.setItem('pf-color', sw.dataset.color);
    });
  });

  // Font size
  on(fontUp, 'click', () => {
    if (fontSize >= 22) return;
    fontSize += 1;
    document.documentElement.style.fontSize = fontSize + 'px';
    if (fontVal) fontVal.textContent = fontSize + 'px';
    localStorage.setItem('pf-font', fontSize);
  });
  on(fontDown, 'click', () => {
    if (fontSize <= 12) return;
    fontSize -= 1;
    document.documentElement.style.fontSize = fontSize + 'px';
    if (fontVal) fontVal.textContent = fontSize + 'px';
    localStorage.setItem('pf-font', fontSize);
  });

  // Restore saved prefs
  const savedTheme = localStorage.getItem('pf-theme');
  const savedColor = localStorage.getItem('pf-color');
  const savedFont  = localStorage.getItem('pf-font');

  if (savedTheme) {
    document.documentElement.setAttribute('data-theme', savedTheme);
    $$('.mode-btn').forEach(b => b.classList.toggle('active', b.dataset.mode === savedTheme));
  }
  if (savedColor) {
    document.documentElement.setAttribute('data-color', savedColor);
    $$('.swatch').forEach(s => s.classList.toggle('active', s.dataset.color === savedColor));
  }
  if (savedFont) {
    fontSize = parseInt(savedFont);
    document.documentElement.style.fontSize = fontSize + 'px';
    if (fontVal) fontVal.textContent = fontSize + 'px';
  }
}

/* ─────────────────────────────────────────
   PROJECT FILTER
───────────────────────────────────────── */
function initProjectFilter() {
  const btns  = $$('.filter-btn');
  const cards = $$('.proj-card');
  if (!btns.length) return;

  btns.forEach(btn => {
    on(btn, 'click', () => {
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;

      cards.forEach(card => {
        const cats = card.dataset.cat || '';
        const show = filter === 'all' || cats.includes(filter);
        card.classList.toggle('hide', !show);
        card.style.animation = show ? 'fadeIn 0.4s ease' : '';
      });
    });
  });
}

/* ─────────────────────────────────────────
   SKILL TABS
───────────────────────────────────────── */
function initSkillTabs() {
  const tabs = $$('.stab');
  if (!tabs.length) return;

  function animateBars(tab) {
    $$('.skill-bar-fill').forEach(bar => {
      const row = bar.closest('.skill-row');
      if (!row) return;
      const group = row.dataset.tabGroup;
      if (group === tab) {
        bar.style.width = bar.dataset.w + '%';
      }
    });
  }

  tabs.forEach(tab => {
    on(tab, 'click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const name = tab.dataset.tab;
      $$('.skill-row').forEach(row => {
        row.classList.toggle('hidden', row.dataset.tabGroup !== name);
        if (row.dataset.tabGroup === name) {
          // Reset and re-animate
          const bar = $('.skill-bar-fill', row);
          if (bar) { bar.style.width = '0%'; }
        }
      });
      setTimeout(() => animateBars(name), 50);
    });
  });

  // Animate initial bars on scroll into view
  const panel = $('.skills-panel');
  if (!panel) return;
  const io = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
      animateBars('dev');
      io.disconnect();
    }
  }, { threshold: 0.3 });
  io.observe(panel);
}

/* ─────────────────────────────────────────
   TESTIMONIAL SLIDER
───────────────────────────────────────── */
function initTestimonialSlider() {
  const track   = $('#testi-track');
  const dotsWrap = $('#testi-dots');
  const prev    = $('#tctl-prev');
  const next    = $('#tctl-next');
  if (!track) return;

  const cards = $$('.tcard', track);
  let current = 0;
  let autoTimer;

  // Build dots
  cards.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.className = 'tdot' + (i === 0 ? ' active' : '');
    dot.setAttribute('aria-label', `Slide ${i+1}`);
    on(dot, 'click', () => goTo(i));
    if (dotsWrap) dotsWrap.appendChild(dot);
  });

  function goTo(idx) {
    current = (idx + cards.length) % cards.length;
    track.style.transform = `translateX(-${current * 100}%)`;
    $$('.tdot', dotsWrap).forEach((d, i) => d.classList.toggle('active', i === current));
    resetAuto();
  }

  function resetAuto() {
    clearInterval(autoTimer);
    autoTimer = setInterval(() => goTo(current + 1), 5500);
  }

  on(prev, 'click', () => goTo(current - 1));
  on(next, 'click', () => goTo(current + 1));

  // Swipe support
  let touchStartX = 0;
  on(track, 'touchstart', e => { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
  on(track, 'touchend', e => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 50) goTo(dx < 0 ? current + 1 : current - 1);
  });

  resetAuto();
}

/* ─────────────────────────────────────────
   BACK TO TOP
───────────────────────────────────────── */
function initBackToTop() {
  const btn = $('#back-top');
  if (!btn) return;
  on(btn, 'click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ─────────────────────────────────────────
   CONTACT FORM
───────────────────────────────────────── */
function initContactForm() {
  const form    = $('#contact-form');
  const submitBtn = $('#form-submit');
  const success = $('#form-success');
  const charCount = $('#char-count');
  const msgArea = $('#c-msg');
  if (!form) return;

  // Character counter
  on(msgArea, 'input', () => {
    const len = msgArea.value.length;
    if (charCount) {
      charCount.textContent = `${len} / 1000`;
      charCount.style.color = len > 900 ? '#F43F5E' : '';
    }
    if (len > 1000) msgArea.value = msgArea.value.slice(0, 1000);
  });

  // Validation helpers
  function setError(fieldId, errId, msg) {
    const field = $(`#${fieldId}`);
    const err   = $(`#${errId}`);
    if (field) field.classList.toggle('error', !!msg);
    if (err)   err.textContent = msg || '';
    return !!msg;
  }

  function validateEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  // Clear errors on input
  [['c-name','err-name'],['c-email','err-email'],['c-msg','err-msg']].forEach(([fid, eid]) => {
    const f = $(`#${fid}`);
    on(f, 'input', () => setError(fid, eid, ''));
  });

  // Submit
  on(form, 'submit', e => {
    e.preventDefault();
    let hasError = false;

    const name = $('#c-name').value.trim();
    const email = $('#c-email').value.trim();
    const msg  = msgArea ? msgArea.value.trim() : '';

    if (!name)              hasError = setError('c-name',  'err-name',  'Name is required.');
    if (!email)             hasError = setError('c-email', 'err-email', 'Email is required.') || hasError;
    else if (!validateEmail(email)) hasError = setError('c-email', 'err-email', 'Enter a valid email address.') || hasError;
    if (!msg)               hasError = setError('c-msg',   'err-msg',   'Message is required.') || hasError;

    if (hasError) {
      shakeForm(form);
      return;
    }

    // Simulate send
    const btnText    = $('.fs-text', submitBtn);
    const btnLoading = $('.fs-loading', submitBtn);
    submitBtn.disabled = true;
    if (btnText)    btnText.style.display    = 'none';
    if (btnLoading) btnLoading.style.display = 'inline';

    setTimeout(() => {
      form.reset();
      submitBtn.style.display = 'none';
      if (success) success.style.display = 'flex';
      if (charCount) charCount.textContent = '0 / 1000';
    }, 1800);
  });
}

function shakeForm(form) {
  form.style.animation = 'none';
  void form.offsetHeight;
  // Inject shake once
  if (!document.getElementById('shake-style')) {
    const s = document.createElement('style');
    s.id = 'shake-style';
    s.textContent = '@keyframes formShake{0%,100%{transform:translateX(0)}20%{transform:translateX(-10px)}40%{transform:translateX(10px)}60%{transform:translateX(-7px)}80%{transform:translateX(7px)}}';
    document.head.appendChild(s);
  }
  form.style.animation = 'formShake 0.45s ease';
  setTimeout(() => form.style.animation = '', 500);
}

/* ─────────────────────────────────────────
   3D TILT on PROJECT CARDS
───────────────────────────────────────── */
function initCardTilt() {
  $$('.proj-card, .srv, .bcard').forEach(card => {
    on(card, 'mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width  - 0.5;
      const y = (e.clientY - rect.top)  / rect.height - 0.5;
      card.style.transform = `translateY(-6px) perspective(800px) rotateX(${-y*6}deg) rotateY(${x*6}deg)`;
    });
    on(card, 'mouseleave', () => {
      card.style.transform = '';
    });
  });
}

/* ─────────────────────────────────────────
   TYPING EFFECT — Hero eyebrow
───────────────────────────────────────── */
function initTypingEffect() {
  const phrases = [
    'Flutter Developer & Mobile App Engineer',
    'Software Engineering Student',
    'Java, Python, and C++ Learner',
    'Web Developer (HTML, CSS, JavaScript)',
    'Figma UI Design Enthusiast',
  ];
  const target = $('.hero-eyebrow');
  if (!target) return;
  const prefix = target.querySelector('.mono');

  let phraseIdx = 0, charIdx = 0, deleting = false;
  const textNode = document.createTextNode('');
  // Insert text after the mono span
  if (prefix && prefix.nextSibling) {
    target.insertBefore(textNode, prefix.nextSibling);
  } else if (prefix) {
    target.appendChild(textNode);
  }

  // Clear original text
  if (prefix && prefix.nextSibling && prefix.nextSibling.nodeType === 3) {
    prefix.nextSibling.textContent = ' ';
  }

  function type() {
    const phrase = phrases[phraseIdx];
    if (!deleting) {
      charIdx++;
      textNode.textContent = ' ' + phrase.slice(0, charIdx);
      if (charIdx === phrase.length) {
        deleting = true;
        setTimeout(type, 2200);
        return;
      }
    } else {
      charIdx--;
      textNode.textContent = ' ' + phrase.slice(0, charIdx);
      if (charIdx === 0) {
        deleting = false;
        phraseIdx = (phraseIdx + 1) % phrases.length;
      }
    }
    setTimeout(type, deleting ? 40 : 65);
  }

  setTimeout(type, 2000);
}

/* ─────────────────────────────────────────
   MAGNETIC BUTTONS
───────────────────────────────────────── */
function initMagneticButtons() {
  $$('.btn-glow, .nav-cta, .form-submit').forEach(btn => {
    on(btn, 'mousemove', e => {
      const rect = btn.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width  / 2;
      const y = e.clientY - rect.top  - rect.height / 2;
      btn.style.transform = `translate(${x * 0.18}px, ${y * 0.18}px) scale(1.04)`;
    });
    on(btn, 'mouseleave', () => {
      btn.style.transform = '';
    });
  });
}

/* ─────────────────────────────────────────
   PAGE ENTRANCE
───────────────────────────────────────── */
function initPageEntrance() {
  document.body.style.opacity = '0';
  document.body.style.transition = 'opacity 0.5s ease';
  raf(() => raf(() => { document.body.style.opacity = '1'; }));
}

/* ─────────────────────────────────────────
   BOOT
───────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initPageEntrance();
  initPreloader();
  initHeroCanvas();
  initCursor();
  initScrollProgress();
  initNav();
  initSmoothScroll();
  initReveal();
  initCounters();
  initThemeSwitcher();
  initProjectFilter();
  initSkillTabs();
  initTestimonialSlider();
  initBackToTop();
  initContactForm();
  initCardTilt();
  initTypingEffect();
  initMagneticButtons();
});
