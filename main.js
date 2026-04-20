/* ============================================================
   PORTFOLIO — main.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Custom Cursor ─── */
  const cursor = document.getElementById('cursor');
  const follower = document.getElementById('cursor-follower');

  let mouseX = 0, mouseY = 0;
  let followerX = 0, followerY = 0;

  if (cursor && follower) {
    document.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
      cursor.style.left = mouseX + 'px';
      cursor.style.top  = mouseY + 'px';
    });

    // Smooth follower with RAF
    function animateFollower() {
      followerX += (mouseX - followerX) * 0.12;
      followerY += (mouseY - followerY) * 0.12;
      follower.style.left = followerX + 'px';
      follower.style.top  = followerY + 'px';
      requestAnimationFrame(animateFollower);
    }
    animateFollower();

    // Cursor scale on interactive elements
    const interactives = document.querySelectorAll('a, button, .project-row, .service-card, .testimonial-card');
    interactives.forEach(el => {
      el.addEventListener('mouseenter', () => {
        cursor.style.transform = 'translate(-50%, -50%) scale(2.5)';
        cursor.style.background = 'var(--purple-dark)';
        follower.style.transform = 'translate(-50%, -50%) scale(0.5)';
        follower.style.opacity = '0.3';
      });
      el.addEventListener('mouseleave', () => {
        cursor.style.transform = 'translate(-50%, -50%) scale(1)';
        cursor.style.background = 'var(--purple)';
        follower.style.transform = 'translate(-50%, -50%) scale(1)';
        follower.style.opacity = '0.5';
      });
    });
  }

  /* ─── Navbar Scroll Effect ─── */
  const nav = document.getElementById('nav');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 60) {
      nav.classList.add('scrolled');
    } else {
      nav.classList.remove('scrolled');
    }
  }, { passive: true });

  /* ─── Mobile Menu Toggle ─── */
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobile-menu');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('open');
      mobileMenu.classList.toggle('open');
    });

    // Close menu on link click
    mobileMenu.querySelectorAll('.mobile-link').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('open');
        mobileMenu.classList.remove('open');
      });
    });
  }

  /* ─── Scroll Reveal ─── */
  const revealEls = document.querySelectorAll('.reveal');
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        // Stagger sibling reveals
        const siblings = Array.from(entry.target.parentElement.querySelectorAll('.reveal'));
        const idx = siblings.indexOf(entry.target);
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, idx * 80);
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

  revealEls.forEach(el => revealObserver.observe(el));

  /* ─── Animated Counter ─── */
  const statNums = document.querySelectorAll('.stat-num');

  function animateCounter(el) {
    const target = parseInt(el.dataset.target, 10);
    const duration = 1800;
    const startTime = performance.now();

    function update(now) {
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(eased * target);
      if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  }

  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        statNums.forEach(num => animateCounter(num));
        statsObserver.disconnect();
      }
    });
  }, { threshold: 0.5 });

  const heroStats = document.querySelector('.hero-stats');
  if (heroStats) statsObserver.observe(heroStats);

  /* ─── Skill Bars Animation ─── */
  const skillFills = document.querySelectorAll('.skill-fill');

  const skillObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        skillFills.forEach((fill, i) => {
          setTimeout(() => {
            fill.style.width = fill.dataset.width + '%';
          }, i * 150);
        });
        skillObserver.disconnect();
      }
    });
  }, { threshold: 0.3 });

  const skillsBlock = document.querySelector('.skills-block');
  if (skillsBlock) skillObserver.observe(skillsBlock);

  /* ─── Smooth Scroll for Nav Links ─── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const navHeight = nav.offsetHeight;
        const targetY = target.getBoundingClientRect().top + window.scrollY - navHeight - 20;
        window.scrollTo({ top: targetY, behavior: 'smooth' });
      }
    });
  });

  /* ─── Active Nav Link Highlight ─── */
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  const activeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(link => link.style.color = '');
        const activeLink = document.querySelector(`.nav-link[href="#${entry.target.id}"]`);
        if (activeLink) activeLink.style.color = 'var(--purple)';
      }
    });
  }, { threshold: 0.4 });

  sections.forEach(s => activeObserver.observe(s));

  /* ─── Contact Form ─── */
  const form = document.getElementById('contact-form');
  const formBtn = document.getElementById('form-btn');
  const formSuccess = document.getElementById('form-success');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const name    = form.name.value.trim();
      const email   = form.email.value.trim();
      const message = form.message.value.trim();

      if (!name || !email || !message) {
        shakeForm();
        return;
      }

      if (!isValidEmail(email)) {
        form.email.style.borderColor = '#E24B4A';
        form.email.focus();
        return;
      }

      // Simulate sending
      formBtn.querySelector('.btn-text').style.display = 'none';
      formBtn.querySelector('.btn-sending').style.display = 'inline';
      formBtn.disabled = true;

      setTimeout(() => {
        formBtn.style.display = 'none';
        if (formSuccess) {
          formSuccess.style.display = 'block';
        }
        form.reset();
      }, 1800);
    });

    // Reset field error styling on input
    form.querySelectorAll('input, textarea').forEach(field => {
      field.addEventListener('input', () => {
        field.style.borderColor = '';
      });
    });
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function shakeForm() {
    if (!form) return;
    form.style.animation = 'none';
    form.offsetHeight; // reflow
    form.style.animation = 'shake 0.4s var(--ease)';
    setTimeout(() => { form.style.animation = ''; }, 500);
  }

  // Inject shake keyframes dynamically
  const shakeStyle = document.createElement('style');
  shakeStyle.textContent = `
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20% { transform: translateX(-8px); }
      40% { transform: translateX(8px); }
      60% { transform: translateX(-6px); }
      80% { transform: translateX(6px); }
    }
  `;
  document.head.appendChild(shakeStyle);

  /* ─── Hire Me Button → Contact ─── */
  const hireBtn = document.getElementById('hire-btn');
  if (hireBtn) {
    hireBtn.addEventListener('click', () => {
      const contact = document.getElementById('contact');
      if (contact) {
        const navHeight = nav.offsetHeight;
        const targetY = contact.getBoundingClientRect().top + window.scrollY - navHeight - 20;
        window.scrollTo({ top: targetY, behavior: 'smooth' });
      }
    });
  }

  /* ─── Project Row — cursor text tracking ─── */
  document.querySelectorAll('.project-row').forEach(row => {
    row.addEventListener('mousemove', (e) => {
      const thumb = row.querySelector('.project-thumb');
      if (!thumb) return;
      const rect = thumb.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const rotX = ((y / rect.height) - 0.5) * 8;
      const rotY = ((x / rect.width) - 0.5) * -8;
      thumb.style.transform = `scale(1.02) perspective(600px) rotateX(${rotX}deg) rotateY(${rotY}deg)`;
    });
    row.addEventListener('mouseleave', () => {
      const thumb = row.querySelector('.project-thumb');
      if (thumb) thumb.style.transform = '';
    });
  });

  /* ─── Page Load Entrance ─── */
  document.body.style.opacity = '0';
  document.body.style.transition = 'opacity 0.4s ease';
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      document.body.style.opacity = '1';
    });
  });

});
