/* ============================================================
   PORTFOLIO — main.js
   Full interactive JS — works with PHP backend
   ============================================================ */
'use strict';

const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];
const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);
const raf = requestAnimationFrame.bind(window);

/* ─── PRELOADER ─── */
function initPreloader() {
  const loader = $('#preloader'), fill = $('#preloader-fill'), pct = $('#preloader-pct');
  if (!loader) return;
  document.body.style.overflow = 'hidden';
  let p = 0;
  const iv = setInterval(() => {
    p += Math.random() * 18;
    if (p >= 100) {
      p = 100; clearInterval(iv);
      setTimeout(() => {
        loader.classList.add('hidden');
        document.body.style.overflow = '';
        initFadeUp();
      }, 350);
    }
    if (fill) fill.style.width = p + '%';
    if (pct)  pct.textContent  = Math.round(p) + '%';
  }, 80);
}

/* ─── HERO CANVAS ─── */
function initHeroCanvas() {
  const canvas = $('#hero-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, pts = [];

  function resize() {
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }
  function makePt() {
    return { x: Math.random()*W, y: Math.random()*H,
             vx:(Math.random()-.5)*.4, vy:(Math.random()-.5)*.4,
             r: Math.random()*1.5+.5, a: Math.random()*.5+.1 };
  }
  function draw() {
    ctx.clearRect(0,0,W,H);
    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#8B5CF6';
    pts.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x<0) p.x=W; if (p.x>W) p.x=0;
      if (p.y<0) p.y=H; if (p.y>H) p.y=0;
      ctx.beginPath(); ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle = accent; ctx.globalAlpha = p.a; ctx.fill();
    });
    ctx.globalAlpha = 1;
    for (let i=0; i<pts.length; i++) for (let j=i+1; j<pts.length; j++) {
      const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y, d=Math.hypot(dx,dy);
      if (d<90) {
        ctx.beginPath(); ctx.moveTo(pts[i].x,pts[i].y); ctx.lineTo(pts[j].x,pts[j].y);
        ctx.strokeStyle=accent; ctx.globalAlpha=(1-d/90)*.12; ctx.lineWidth=.5; ctx.stroke();
      }
    }
    ctx.globalAlpha=1; raf(draw);
  }
  resize(); pts = Array.from({length:120}, makePt); draw();
  on(window,'resize',() => { resize(); pts = Array.from({length:120},makePt); });
}

/* ─── CURSOR ─── */
function initCursor() {
  const cursor = $('#cursor');
  if (!cursor || window.innerWidth < 640) return;
  let mx=0, my=0;
  on(document,'mousemove', e => {
    mx=e.clientX; my=e.clientY;
    cursor.style.left=mx+'px'; cursor.style.top=my+'px';
  });
  $$('a,button,.proj-card,.srv,.bcard,.tcard').forEach(el => {
    on(el,'mouseenter',() => cursor.classList.add('expand'));
    on(el,'mouseleave',() => cursor.classList.remove('expand','labeled'));
  });
}

/* ─── SCROLL PROGRESS ─── */
function initScrollProgress() {
  const bar = $('#scroll-progress');
  if (!bar) return;
  on(window,'scroll',() => {
    bar.style.transform = `scaleX(${window.scrollY/(document.documentElement.scrollHeight-window.innerHeight)})`;
  },{passive:true});
}

/* ─── NAV ─── */
function initNav() {
  const nav = $('#nav'), burger = $('#hamburger'), overlay = $('#mobile-overlay');
  if (!nav) return;
  on(window,'scroll',() => nav.classList.toggle('scrolled', window.scrollY>60),{passive:true});

  const sections = $$('section[id]'), links = $$('.nav-link');
  new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        links.forEach(l => l.classList.remove('active'));
        const a = $(`a[href="#${e.target.id}"]`);
        if (a) a.classList.add('active');
      }
    });
  },{threshold:.35}).observe && sections.forEach(s =>
    new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) {
        links.forEach(l => l.classList.remove('active'));
        const a = $(`a[href="#${entries[0].target.id}"]`);
        if (a) a.classList.add('active');
      }
    },{threshold:.35}).observe(s)
  );

  if (burger && overlay) {
    on(burger,'click',() => { burger.classList.toggle('open'); overlay.classList.toggle('open'); });
    $$('.m-link,.m-cta',overlay).forEach(l => on(l,'click',() => {
      burger.classList.remove('open'); overlay.classList.remove('open');
    }));
  }
}

/* ─── SMOOTH SCROLL ─── */
function initSmoothScroll() {
  const nav = $('#nav');
  on(document,'click', e => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const t = $(a.getAttribute('href'));
    if (!t) return;
    e.preventDefault();
    window.scrollTo({top: t.offsetTop - (nav ? nav.offsetHeight : 70) - 20, behavior:'smooth'});
  });
}

/* ─── REVEAL ─── */
function initReveal() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const siblings = $$('.reveal', entry.target.parentElement);
      const idx = siblings.indexOf(entry.target);
      setTimeout(() => entry.target.classList.add('visible'), idx * 90);
      io.unobserve(entry.target);
    });
  },{threshold:.1,rootMargin:'0px 0px -50px 0px'});
  $$('.reveal').forEach(el => io.observe(el));
}

function initFadeUp() {
  $$('.fade-up').forEach((el,i) => setTimeout(() => el.classList.add('visible'), 300 + i*150));
}

/* ─── COUNTERS ─── */
function initCounters() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, target = parseInt(el.dataset.target,10), dur = 1800, t0 = performance.now();
      const upd = now => {
        const eased = 1 - Math.pow(1 - Math.min((now-t0)/dur,1), 3);
        el.textContent = Math.round(eased*target);
        if (eased < 1) raf(upd);
      };
      raf(upd); io.unobserve(el);
    });
  },{threshold:.5});
  $$('.counter').forEach(c => io.observe(c));
}

/* ─── THEME SWITCHER ─── */
function initThemeSwitcher() {
  const panel = $('#theme-panel'), btn = $('#theme-toggle-btn'), drawer = $('#theme-drawer');
  const fontUp = $('#font-up'), fontDown = $('#font-down'), fontVal = $('#font-val');
  if (!panel) return;
  let fontSize = parseInt(localStorage.getItem('pf-font') || '16', 10);
  document.documentElement.style.fontSize = fontSize + 'px';
  if (fontVal) fontVal.textContent = fontSize + 'px';

  on(btn,'click', e => { e.stopPropagation(); drawer.classList.toggle('open'); });
  on(document,'click', e => { if (!panel.contains(e.target)) drawer.classList.remove('open'); });

  $$('.mode-btn').forEach(b => on(b,'click',() => {
    $$('.mode-btn').forEach(x => x.classList.remove('active')); b.classList.add('active');
    document.documentElement.setAttribute('data-theme', b.dataset.mode);
    localStorage.setItem('pf-theme', b.dataset.mode);
  }));

  $$('.swatch').forEach(s => on(s,'click',() => {
    $$('.swatch').forEach(x => x.classList.remove('active')); s.classList.add('active');
    document.documentElement.setAttribute('data-color', s.dataset.color);
    localStorage.setItem('pf-color', s.dataset.color);
  }));

  on(fontUp,'click',() => { if (fontSize>=22) return; fontSize++; apply(); });
  on(fontDown,'click',() => { if (fontSize<=12) return; fontSize--; apply(); });
  function apply() {
    document.documentElement.style.fontSize = fontSize+'px';
    if (fontVal) fontVal.textContent = fontSize+'px';
    localStorage.setItem('pf-font', fontSize);
  }

  const t = localStorage.getItem('pf-theme'), c = localStorage.getItem('pf-color');
  if (t) { document.documentElement.setAttribute('data-theme',t); $$('.mode-btn').forEach(b => b.classList.toggle('active', b.dataset.mode===t)); }
  if (c) { document.documentElement.setAttribute('data-color',c); $$('.swatch').forEach(s => s.classList.toggle('active', s.dataset.color===c)); }
}

/* ─── PROJECT FILTER ─── */
function initProjectFilter() {
  const btns = $$('.filter-btn'), cards = $$('.proj-card');
  btns.forEach(btn => on(btn,'click',() => {
    btns.forEach(b => b.classList.remove('active')); btn.classList.add('active');
    const f = btn.dataset.filter;
    cards.forEach(c => c.classList.toggle('hide', f !== 'all' && !c.dataset.cat.split(' ').includes(f)));
  }));
}

/* ─── SKILL TABS ─── */
function initSkillTabs() {
  const tabs = $$('.stab');
  if (!tabs.length) return;

  function animateBars(tab) {
    $$('.skill-bar-fill').forEach(bar => {
      const row = bar.closest('.skill-row');
      if (row && row.dataset.tabGroup === tab) bar.style.width = bar.dataset.w + '%';
    });
  }

  tabs.forEach(tab => on(tab,'click',() => {
    tabs.forEach(t => t.classList.remove('active')); tab.classList.add('active');
    const name = tab.dataset.tab;
    $$('.skill-row').forEach(row => {
      const match = row.dataset.tabGroup === name;
      row.classList.toggle('hidden', !match);
      if (match) { const b = $('.skill-bar-fill',row); if (b) b.style.width='0%'; }
    });
    setTimeout(() => animateBars(name), 60);
  }));

  const panel = $('.skills-panel');
  if (panel) new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) { animateBars('dev'); }
  },{threshold:.3}).observe(panel);
}

/* ─── TESTIMONIAL SLIDER ─── */
function initSlider() {
  const track = $('#testi-track'), dotsWrap = $('#testi-dots');
  const prev = $('#tctl-prev'), next = $('#tctl-next');
  if (!track) return;
  const cards = $$('.tcard', track);
  let cur = 0, timer;

  cards.forEach((_,i) => {
    const d = document.createElement('button');
    d.className = 'tdot' + (i===0?' active':'');
    on(d,'click',() => go(i));
    dotsWrap && dotsWrap.appendChild(d);
  });

  function go(idx) {
    cur = (idx + cards.length) % cards.length;
    track.style.transform = `translateX(-${cur*100}%)`;
    $$('.tdot',dotsWrap).forEach((d,i) => d.classList.toggle('active',i===cur));
    reset();
  }
  function reset() { clearInterval(timer); timer = setInterval(() => go(cur+1), 5500); }
  on(prev,'click',() => go(cur-1)); on(next,'click',() => go(cur+1));
  let tx=0;
  on(track,'touchstart',e => tx=e.changedTouches[0].clientX,{passive:true});
  on(track,'touchend',e => { const dx=e.changedTouches[0].clientX-tx; if(Math.abs(dx)>50) go(dx<0?cur+1:cur-1); });
  reset();
}

/* ─── CONTACT FORM — Ajax POST to contact.php ─── */
function initContactForm() {
  const form = $('#contact-form'), submitBtn = $('#form-submit');
  const success = $('#form-success'), errorBox = $('#form-error'), errorText = $('#form-error-text');
  const charCount = $('#char-count'), msgArea = $('#c-msg');
  if (!form) return;

  on(msgArea,'input',() => {
    const len = msgArea.value.length;
    if (charCount) { charCount.textContent = `${len} / 1000`; charCount.style.color = len>900?'#F43F5E':''; }
    if (len > 1000) msgArea.value = msgArea.value.slice(0,1000);
  });

  function setErr(fid, eid, msg) {
    const f = $(`#${fid}`), e = $(`#${eid}`);
    if (f) f.classList.toggle('error', !!msg);
    if (e) e.textContent = msg || '';
  }

  [['c-name','err-name'],['c-email','err-email'],['c-msg','err-msg']].forEach(([fid,eid]) => {
    const f = $(`#${fid}`); on(f,'input',() => setErr(fid,eid,''));
  });

  on(form,'submit', async e => {
    e.preventDefault();
    let ok = true;
    const name = $('#c-name').value.trim(), email = $('#c-email').value.trim(), msg = msgArea?.value.trim() || '';
    if (!name) { setErr('c-name','err-name','Name is required.'); ok=false; }
    if (!email) { setErr('c-email','err-email','Email is required.'); ok=false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setErr('c-email','err-email','Enter a valid email.'); ok=false; }
    if (!msg)  { setErr('c-msg','err-msg','Message is required.'); ok=false; }
    if (!ok)   { shakeForm(form); return; }

    // Show loading
    const fsText = $('.fs-text',submitBtn), fsLoad = $('.fs-loading',submitBtn);
    submitBtn.disabled = true;
    if (fsText) fsText.style.display='none';
    if (fsLoad) fsLoad.style.display='inline';
    if (errorBox) errorBox.style.display='none';

    try {
      const fd = new FormData(form);
      const res = await fetch('contact.php', { method:'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        form.reset();
        if (charCount) charCount.textContent = '0 / 1000';
        submitBtn.style.display = 'none';
        if (success) success.style.display = 'flex';
        // Reset bars for any checked skills
        $$('.skill-bar-fill').forEach(b => { b.style.width = '0%'; });
      } else {
        // Field errors from server
        if (data.errors) {
          if (data.errors.name)    setErr('c-name','err-name',data.errors.name);
          if (data.errors.email)   setErr('c-email','err-email',data.errors.email);
          if (data.errors.message) setErr('c-msg','err-msg',data.errors.message);
          shakeForm(form);
        }
        if (errorBox && errorText) {
          errorText.textContent = data.message || 'Something went wrong.';
          errorBox.style.display = 'flex';
        }
        submitBtn.disabled = false;
        if (fsText) fsText.style.display='inline';
        if (fsLoad) fsLoad.style.display='none';
      }
    } catch (err) {
      submitBtn.disabled = false;
      if (fsText) fsText.style.display='inline';
      if (fsLoad) fsLoad.style.display='none';
      if (errorBox && errorText) {
        errorText.textContent = 'Network error. Please check your connection.';
        errorBox.style.display = 'flex';
      }
    }
  });
}

function shakeForm(form) {
  if (!document.getElementById('shake-kf')) {
    const s = document.createElement('style'); s.id='shake-kf';
    s.textContent = '@keyframes fShake{0%,100%{transform:translateX(0)}20%{transform:translateX(-10px)}40%{transform:translateX(10px)}60%{transform:translateX(-7px)}80%{transform:translateX(7px)}}';
    document.head.appendChild(s);
  }
  form.style.animation='none'; void form.offsetHeight;
  form.style.animation='fShake 0.45s ease';
  setTimeout(()=>form.style.animation='',500);
}

/* ─── CARD TILT ─── */
function initCardTilt() {
  $$('.proj-card,.srv,.bcard').forEach(card => {
    on(card,'mousemove',e=>{
      const r=card.getBoundingClientRect();
      const x=(e.clientX-r.left)/r.width-.5, y=(e.clientY-r.top)/r.height-.5;
      card.style.transform=`translateY(-6px) perspective(800px) rotateX(${-y*6}deg) rotateY(${x*6}deg)`;
    });
    on(card,'mouseleave',()=>card.style.transform='');
  });
}

/* ─── TYPING EFFECT ─── */
function initTyping() {
  const phrases=['Full-Stack Developer & UI Designer','React & Node.js Engineer','UI/UX Product Designer','Open Source Contributor','AI Integration Specialist'];
  const eyebrow = $('.hero-eyebrow');
  if (!eyebrow) return;
  const mono = eyebrow.querySelector('.mono');
  if (!mono) return;

  // Remove existing text nodes after mono
  while (mono.nextSibling) mono.nextSibling.remove();
  const textNode = document.createTextNode(' ');
  eyebrow.appendChild(textNode);

  let pi=0, ci=0, del=false;
  function type() {
    const phrase = phrases[pi];
    if (!del) { ci++; textNode.textContent=' '+phrase.slice(0,ci); if (ci===phrase.length){ del=true; return setTimeout(type,2200); } }
    else { ci--; textNode.textContent=' '+phrase.slice(0,ci); if (ci===0){ del=false; pi=(pi+1)%phrases.length; } }
    setTimeout(type, del?40:65);
  }
  setTimeout(type,2000);
}

/* ─── MAGNETIC BUTTONS ─── */
function initMagnetic() {
  $$('.btn-glow,.nav-cta,.form-submit').forEach(btn => {
    on(btn,'mousemove',e=>{
      const r=btn.getBoundingClientRect();
      const x=e.clientX-r.left-r.width/2, y=e.clientY-r.top-r.height/2;
      btn.style.transform=`translate(${x*.18}px,${y*.18}px) scale(1.04)`;
    });
    on(btn,'mouseleave',()=>btn.style.transform='');
  });
}

/* ─── BACK TO TOP ─── */
function initBackTop() {
  const btn = $('#back-top');
  on(btn,'click',()=>window.scrollTo({top:0,behavior:'smooth'}));
}

/* ─── PAGE ENTRANCE ─── */
function initEntrance() {
  document.body.style.opacity='0';
  document.body.style.transition='opacity 0.45s ease';
  raf(()=>raf(()=>document.body.style.opacity='1'));
}

/* ─── BOOT ─── */
document.addEventListener('DOMContentLoaded', () => {
  initEntrance();
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
  initSlider();
  initContactForm();
  initCardTilt();
  initTyping();
  initMagnetic();
  initBackTop();
});
