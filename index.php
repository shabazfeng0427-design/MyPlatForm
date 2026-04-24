<?php
// ============================================================
//  index.php — Main Portfolio Page
// ============================================================
require_once 'config.php';

// Log visit
logPageView('/');

// ── Load all data from database ──────────────────────────────
$settings     = DB::query('SELECT `key`, value FROM settings');
$cfg = [];
foreach ($settings as $s) $cfg[$s['key']] = $s['value'];

$projects     = DB::query('SELECT * FROM projects WHERE active = 1 ORDER BY sort_order ASC');
$services     = DB::query('SELECT * FROM services WHERE active = 1 ORDER BY sort_order ASC');
$testimonials = DB::query('SELECT * FROM testimonials WHERE active = 1 ORDER BY sort_order ASC');
$experience   = DB::query('SELECT * FROM experience WHERE active = 1 ORDER BY sort_order ASC');
$blog_posts   = DB::query('SELECT * FROM blog_posts WHERE published = 1 ORDER BY sort_order ASC LIMIT 3');
$skills       = DB::query('SELECT * FROM skills WHERE active = 1 ORDER BY tab_group, sort_order ASC');
$process      = DB::query('SELECT * FROM process_steps WHERE active = 1 ORDER BY sort_order ASC');

// Group skills by tab
$skillsByTab = [];
foreach ($skills as $sk) {
    $skillsByTab[$sk['tab_group']][] = $sk;
}

// CSRF token for contact form
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" data-color="violet">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($cfg['site_name'] ?? 'shabaz yassen') ?> — Creative Developer &amp; Designer</title>
  <meta name="description" content="<?= e($cfg['site_tagline'] ?? '') ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- PRELOADER -->
<div class="preloader" id="preloader">
  <div class="preloader-inner">
    <div class="preloader-logo"><?= strtoupper(substr($cfg['site_name'] ?? 'AC', 0, 2)) ?></div>
    <div class="preloader-bar"><div class="preloader-fill" id="preloader-fill"></div></div>
    <div class="preloader-pct" id="preloader-pct">0%</div>
  </div>
</div>

<!-- CURSOR -->
<div class="cursor" id="cursor">
  <div class="cursor-dot"></div>
  <div class="cursor-ring"></div>
  <div class="cursor-label" id="cursor-label"></div>
</div>

<!-- COLOR THEME PANEL -->
<div class="theme-panel" id="theme-panel">
  <button class="theme-toggle-btn" id="theme-toggle-btn" title="Customize theme">◑</button>
  <div class="theme-drawer" id="theme-drawer">
    <div class="tdrawer-title">Customize</div>
    <div class="theme-section-label">Mode</div>
    <div class="mode-btns">
      <button class="mode-btn active" data-mode="dark">Dark</button>
      <button class="mode-btn" data-mode="light">Light</button>
    </div>
    <div class="theme-section-label">Accent Color</div>
    <div class="color-swatches">
      <button class="swatch active" data-color="violet" style="background:#8B5CF6" title="Violet"></button>
      <button class="swatch" data-color="cyan"   style="background:#06B6D4" title="Cyan"></button>
      <button class="swatch" data-color="rose"   style="background:#F43F5E" title="Rose"></button>
      <button class="swatch" data-color="amber"  style="background:#F59E0B" title="Amber"></button>
      <button class="swatch" data-color="lime"   style="background:#84CC16" title="Lime"></button>
      <button class="swatch" data-color="orange" style="background:#F97316" title="Orange"></button>
    </div>
    <div class="theme-section-label">Font Size</div>
    <div class="font-controls">
      <button class="font-btn" id="font-down">A−</button>
      <span class="font-val" id="font-val">16px</span>
      <button class="font-btn" id="font-up">A+</button>
    </div>
  </div>
</div>

<!-- SCROLL PROGRESS -->
<div class="scroll-progress" id="scroll-progress"></div>

<!-- NAV -->
<header class="nav" id="nav">
  <a href="#hero" class="nav-logo">
    <span class="logo-mark"><?= strtoupper(substr($cfg['site_name'] ?? 'AC', 0, 2)) ?></span>
    <span class="logo-text"><?= e($cfg['site_name'] ?? 'shabaz yassen') ?></span>
  </a>
  <nav class="nav-center">
    <a href="#work"         class="nav-link">Work</a>
    <a href="#about"        class="nav-link">About</a>
    <a href="#services"     class="nav-link">Services</a>
    <a href="#process"      class="nav-link">Process</a>
    <a href="#testimonials" class="nav-link">Clients</a>
    <a href="#blog"         class="nav-link">Blog</a>
    <a href="#contact"      class="nav-link">Contact</a>
  </nav>
  <div class="nav-right">
    <?php if (!empty($cfg['site_available']) && $cfg['site_available'] == '1'): ?>
    <span class="nav-status"><span class="status-dot"></span>Available</span>
    <?php endif; ?>
    <a href="#contact" class="nav-cta">Hire Me ↗</a>
  </div>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
</header>

<!-- MOBILE MENU -->
<div class="mobile-overlay" id="mobile-overlay">
  <div class="mobile-menu">
    <a href="#work"         class="m-link">Work</a>
    <a href="#about"        class="m-link">About</a>
    <a href="#services"     class="m-link">Services</a>
    <a href="#process"      class="m-link">Process</a>
    <a href="#testimonials" class="m-link">Clients</a>
    <a href="#blog"         class="m-link">Blog</a>
    <a href="#contact"      class="m-link">Contact</a>
    <a href="#contact" class="m-cta">Hire Me ↗</a>
  </div>
</div>

<!-- ════════════════ HERO ════════════════ -->
<section class="hero" id="hero">
  <canvas class="hero-canvas" id="hero-canvas"></canvas>
  <div class="hero-grid-overlay"></div>
  <div class="hero-content">
    <div class="hero-eyebrow fade-up">
      <span class="mono">01 /</span> <?= e($cfg['site_tagline'] ?? 'Full-Stack Developer & UI Designer') ?>
    </div>
    <h1 class="hero-title fade-up">
      <span class="title-line">CRAFTING</span>
      <span class="title-line accent">BOLD</span>
      <span class="title-line">DIGITAL</span>
      <span class="title-line outline">WORLDS.</span>
    </h1>
    <p class="hero-desc fade-up">
      <?= e($cfg['hero_subtitle'] ?? 'I design and engineer high-performance web experiences.') ?><br/>
      Based in <?= e($cfg['site_location'] ?? 'San Francisco') ?> — working globally.
    </p>
    <div class="hero-actions fade-up">
      <a href="#work" class="btn-glow">Explore Work ↓</a>
      <a href="#contact" class="btn-outline-hero">Let's Talk →</a>
      <a href="#" class="btn-text-link">↓ Download CV</a>
    </div>
    <div class="hero-scroll-hint fade-up">
      <div class="scroll-line"></div>
      <span class="mono">scroll</span>
    </div>
  </div>
  <div class="hero-stats fade-up">
    <div class="hstat"><span class="hstat-num counter" data-target="<?= count($projects) ?>">0</span><span class="hstat-suf">+</span><span class="hstat-lbl">Projects</span></div>
    <div class="hstat-div"></div>
    <div class="hstat"><span class="hstat-num counter" data-target="5">0</span><span class="hstat-suf">yr</span><span class="hstat-lbl">Experience</span></div>
    <div class="hstat-div"></div>
    <div class="hstat"><span class="hstat-num counter" data-target="98">0</span><span class="hstat-suf">%</span><span class="hstat-lbl">Satisfaction</span></div>
    <div class="hstat-div"></div>
    <div class="hstat"><span class="hstat-num counter" data-target="<?= count($testimonials) ?>">0</span><span class="hstat-suf">★</span><span class="hstat-lbl">Reviews</span></div>
  </div>
  <div class="hero-marquee">
    <div class="marquee-inner">
      <?php
      $techStack = ['React','TypeScript','Next.js','Node.js','Figma','Python','GraphQL','PostgreSQL','AWS','Docker'];
      $doubles = array_merge($techStack, $techStack);
      foreach ($doubles as $i => $tech):
      ?>
        <span><?= e($tech) ?></span><span class="bull">◆</span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════ WORK ════════════════ -->
<section class="section" id="work">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-tag mono">02 / Selected Work</span>
      <h2 class="sec-title">Projects that <em>matter.</em></h2>
      <div class="filter-bar">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="web">Web App</button>
        <button class="filter-btn" data-filter="mobile">Mobile</button>
        <button class="filter-btn" data-filter="design">Design</button>
        <button class="filter-btn" data-filter="oss">Open Source</button>
      </div>
    </div>
    <div class="projects-grid" id="projects-grid">
      <?php foreach ($projects as $i => $p): ?>
      <article class="proj-card <?= $p['featured'] ? 'featured' : '' ?> reveal" data-cat="<?= e($p['category']) ?>">
        <div class="proj-img <?= e($p['thumb_class']) ?>">
          <div class="proj-icon"><?= e($p['thumb_icon']) ?></div>
          <div class="proj-year mono"><?= e($p['year']) ?></div>
          <div class="proj-hover-cta"><?= e($p['link_label'] ?? 'View Project ↗') ?></div>
        </div>
        <div class="proj-body">
          <div class="proj-tags">
            <?php foreach (explode(',', $p['tags']) as $tag): ?>
            <span class="ptag"><?= e(trim($tag)) ?></span>
            <?php endforeach; ?>
          </div>
          <h3 class="proj-title"><?= e($p['title']) ?></h3>
          <p class="proj-desc"><?= e($p['description']) ?></p>
          <?php if (!empty($p['metrics'])): ?>
          <?php $metrics = json_decode($p['metrics'], true) ?? []; ?>
          <?php if ($metrics): ?>
          <div class="proj-metrics">
            <?php foreach ($metrics as $m): ?>
            <span class="metric"><strong><?= e($m['val']) ?></strong> <?= e($m['label']) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php endif; ?>
          <a href="<?= e($p['link_url'] ?? '#') ?>" class="proj-link"><?= e($p['link_label'] ?? 'View →') ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════ ABOUT ════════════════ -->
<section class="section sec-about" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-left reveal">
        <span class="sec-tag mono">03 / About Me</span>
        <h2 class="sec-title">I build things<br/><em>people love.</em></h2>
        <div class="about-bio">
          <?php foreach (['about_bio_1','about_bio_2','about_bio_3'] as $k): ?>
          <?php if (!empty($cfg[$k])): ?><p><?= $cfg[$k] ?></p><?php endif; ?>
          <?php endforeach; ?>
        </div>
        <div class="about-facts">
          <div class="fact"><span class="fact-icon">📍</span><?= e($cfg['site_location'] ?? '') ?></div>
          <div class="fact"><span class="fact-icon">🌐</span>Open to remote worldwide</div>
          <div class="fact"><span class="fact-icon">⏱</span>Usually responds within 24h</div>
          <div class="fact"><span class="fact-icon">🎓</span>CS — Stanford University, 2019</div>
        </div>
        <div class="about-socials">
          <?php if (!empty($cfg['site_github'])):   ?><a href="<?= e($cfg['site_github']) ?>"   class="social-btn" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
          <?php if (!empty($cfg['site_linkedin'])): ?><a href="<?= e($cfg['site_linkedin']) ?>" class="social-btn" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
          <?php if (!empty($cfg['site_twitter'])):  ?><a href="<?= e($cfg['site_twitter']) ?>"  class="social-btn" target="_blank" rel="noopener">Twitter</a><?php endif; ?>
          <?php if (!empty($cfg['site_dribbble'])): ?><a href="<?= e($cfg['site_dribbble']) ?>" class="social-btn" target="_blank" rel="noopener">Dribbble</a><?php endif; ?>
          <a href="#" class="social-btn">Resume ↗</a>
        </div>
      </div>
      <div class="about-right reveal">
        <div class="avatar-wrap">
          <div class="avatar-frame"><div class="avatar-emoji">👩‍💻</div></div>
          <div class="avatar-ring-deco r1"></div>
          <div class="avatar-ring-deco r2"></div>
          <div class="floating-badge fb1"><span class="fb-dot"></span>Open to work</div>
          <div class="floating-badge fb2">🏆 Top Rated</div>
          <div class="floating-badge fb3">5★ Reviews</div>
        </div>
        <div class="skills-panel">
          <div class="skills-header">
            <span>Technical Skills</span>
            <div class="skills-tabs">
              <button class="stab active" data-tab="dev">Dev</button>
              <button class="stab" data-tab="design">Design</button>
              <button class="stab" data-tab="tools">Tools</button>
            </div>
          </div>
          <div class="skills-content" id="skills-content">
            <?php foreach ($skillsByTab as $tab => $tabSkills): ?>
            <?php foreach ($tabSkills as $i => $sk): ?>
            <div class="skill-row <?= ($tab !== 'dev') ? 'hidden' : '' ?>" data-tab-group="<?= e($tab) ?>">
              <span class="skill-name"><?= e($sk['name']) ?></span>
              <div class="skill-bar-wrap"><div class="skill-bar-fill" data-w="<?= (int)$sk['percentage'] ?>"></div></div>
              <span class="skill-pct mono"><?= (int)$sk['percentage'] ?>%</span>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline -->
    <div class="timeline-wrap reveal">
      <h3 class="timeline-title">Experience</h3>
      <div class="timeline">
        <?php foreach ($experience as $ex): ?>
        <div class="tl-item">
          <div class="tl-dot"></div>
          <div class="tl-year mono"><?= e($ex['period']) ?></div>
          <div class="tl-content">
            <h4><?= e($ex['role']) ?><?= $ex['company'] ? ' — ' . e($ex['company']) : '' ?></h4>
            <p><?= e($ex['description']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════ SERVICES ════════════════ -->
<section class="section sec-services" id="services">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-tag mono">04 / Services</span>
      <h2 class="sec-title">What I can do<br/><em>for you.</em></h2>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $srv): ?>
      <div class="srv reveal">
        <div class="srv-num mono"><?= e($srv['num']) ?></div>
        <div class="srv-icon"><?= e($srv['icon']) ?></div>
        <h3 class="srv-title"><?= e($srv['title']) ?></h3>
        <p class="srv-desc"><?= e($srv['description']) ?></p>
        <ul class="srv-list">
          <?php foreach (explode('|', $srv['items']) as $item): ?>
          <li><?= e(trim($item)) ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="srv-price">From <strong><?= e($srv['price_from']) ?></strong> / project</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════ PROCESS ════════════════ -->
<section class="section sec-process" id="process">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-tag mono">05 / How It Works</span>
      <h2 class="sec-title">My process,<br/><em>simplified.</em></h2>
    </div>
    <div class="process-steps">
      <?php foreach ($process as $i => $step): ?>
      <div class="pstep reveal">
        <div class="pstep-num mono"><?= e($step['num']) ?></div>
        <div class="pstep-icon"><?= e($step['icon']) ?></div>
        <h3 class="pstep-title"><?= e($step['title']) ?></h3>
        <p class="pstep-desc"><?= e($step['description']) ?></p>
        <div class="pstep-dur mono"><?= e($step['duration']) ?></div>
      </div>
      <?php if ($i < count($process) - 1): ?>
      <div class="pstep-arrow reveal">→</div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════ TESTIMONIALS ════════════════ -->
<section class="section sec-testi" id="testimonials">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-tag mono">06 / Client Love</span>
      <h2 class="sec-title">What they say<br/><em>about working with me.</em></h2>
    </div>
    <div class="testi-slider" id="testi-slider">
      <div class="testi-track" id="testi-track">
        <?php foreach ($testimonials as $t): ?>
        <div class="tcard">
          <div class="tcard-stars"><?= str_repeat('★', (int)($t['rating'] ?? 5)) ?></div>
          <blockquote class="tcard-quote">"<?= e($t['quote']) ?>"</blockquote>
          <div class="tcard-author">
            <div class="tcard-av" style="background:<?= e($t['avatar_color'] ?? 'var(--accent)') ?>"><?= e($t['avatar_initials']) ?></div>
            <div>
              <div class="tcard-name"><?= e($t['name']) ?></div>
              <div class="tcard-role mono"><?= e($t['role']) ?>, <?= e($t['company']) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="testi-controls">
        <button class="tctl" id="tctl-prev">←</button>
        <div class="testi-dots" id="testi-dots"></div>
        <button class="tctl" id="tctl-next">→</button>
      </div>
    </div>
    <div class="logos-row reveal">
      <span class="logo-label mono">Trusted by teams at</span>
      <div class="logos-list">
        <span class="co-logo">Stripe</span>
        <span class="co-logo">Vercel</span>
        <span class="co-logo">Linear</span>
        <span class="co-logo">Notion</span>
        <span class="co-logo">Figma</span>
        <span class="co-logo">Loom</span>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════ BLOG ════════════════ -->
<section class="section sec-blog" id="blog">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-tag mono">07 / Writing</span>
      <h2 class="sec-title">Thoughts &amp;<br/><em>Insights.</em></h2>
    </div>
    <div class="blog-grid">
      <?php foreach ($blog_posts as $i => $post): ?>
      <article class="bcard <?= $post['featured'] ? 'featured-post' : '' ?> reveal">
        <div class="bcard-img <?= e($post['img_class']) ?>">
          <span class="bcard-cat"><?= e($post['category']) ?></span>
        </div>
        <div class="bcard-body">
          <span class="bcard-date mono"><?= date('M j, Y', strtotime($post['published_at'] ?? 'now')) ?></span>
          <h3 class="bcard-title"><?= e($post['title']) ?></h3>
          <p class="bcard-excerpt"><?= e($post['excerpt']) ?></p>
          <a href="blog.php?slug=<?= urlencode($post['slug']) ?>" class="bcard-link">Read article →</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════ CONTACT ════════════════ -->
<section class="section sec-contact" id="contact">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-left reveal">
        <span class="sec-tag mono">08 / Get In Touch</span>
        <h2 class="sec-title">Let's build<br/><em>something great.</em></h2>
        <p class="contact-sub">Have a project? A question? Or just want to say hello? I reply to every message, usually within a day.</p>
        <div class="contact-info-list">
          <div class="ci-item">
            <span class="ci-icon">✉</span>
            <div><div class="ci-label mono">Email</div>
            <a href="mailto:<?= e($cfg['site_email'] ?? '') ?>" class="ci-val"><?= e($cfg['site_email'] ?? '') ?></a></div>
          </div>
          <div class="ci-item">
            <span class="ci-icon">📍</span>
            <div><div class="ci-label mono">Location</div>
            <span class="ci-val"><?= e($cfg['site_location'] ?? '') ?> — remote friendly</span></div>
          </div>
          <div class="ci-item">
            <span class="ci-icon">⏰</span>
            <div><div class="ci-label mono">Availability</div>
            <span class="ci-val available"><?= ($cfg['site_available'] ?? '0') == '1' ? 'Open to new projects now' : 'Currently fully booked' ?></span></div>
          </div>
        </div>
        <div class="contact-socials">
          <?php if (!empty($cfg['site_github'])):   ?><a href="<?= e($cfg['site_github']) ?>"   class="cs-btn" target="_blank" rel="noopener">GH</a><?php endif; ?>
          <?php if (!empty($cfg['site_linkedin'])): ?><a href="<?= e($cfg['site_linkedin']) ?>" class="cs-btn" target="_blank" rel="noopener">LI</a><?php endif; ?>
          <?php if (!empty($cfg['site_twitter'])):  ?><a href="<?= e($cfg['site_twitter']) ?>"  class="cs-btn" target="_blank" rel="noopener">TW</a><?php endif; ?>
          <?php if (!empty($cfg['site_dribbble'])): ?><a href="<?= e($cfg['site_dribbble']) ?>" class="cs-btn" target="_blank" rel="noopener">DR</a><?php endif; ?>
        </div>
      </div>

      <!-- Contact Form — submits to contact.php via AJAX -->
      <form class="contact-form reveal" id="contact-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
        <div class="form-header">Send a message</div>
        <div class="form-row-2">
          <div class="fg">
            <label for="c-name">Name <span class="req">*</span></label>
            <input type="text" id="c-name" name="name" placeholder="Your name" autocomplete="name" required maxlength="200" />
            <span class="ferr" id="err-name"></span>
          </div>
          <div class="fg">
            <label for="c-email">Email <span class="req">*</span></label>
            <input type="email" id="c-email" name="email" placeholder="you@email.com" autocomplete="email" required maxlength="200" />
            <span class="ferr" id="err-email"></span>
          </div>
        </div>
        <div class="fg">
          <label for="c-budget">Budget Range</label>
          <select id="c-budget" name="budget">
            <option value="">Select a range...</option>
            <option>Under $2,000</option>
            <option>$2,000 – $5,000</option>
            <option>$5,000 – $15,000</option>
            <option>$15,000+</option>
            <option>Let's discuss</option>
          </select>
        </div>
        <div class="fg">
          <label>Project Type</label>
          <div class="checkbox-group">
            <label class="chk"><input type="checkbox" name="project_types[]" value="Web App" /> Web App</label>
            <label class="chk"><input type="checkbox" name="project_types[]" value="UI/UX Design" /> UI/UX Design</label>
            <label class="chk"><input type="checkbox" name="project_types[]" value="Mobile App" /> Mobile App</label>
            <label class="chk"><input type="checkbox" name="project_types[]" value="AI Integration" /> AI Integration</label>
            <label class="chk"><input type="checkbox" name="project_types[]" value="Other" /> Other</label>
          </div>
        </div>
        <div class="fg">
          <label for="c-msg">Message <span class="req">*</span></label>
          <textarea id="c-msg" name="message" rows="5" placeholder="Tell me about your project, timeline, and goals..." required maxlength="1000"></textarea>
          <span class="char-count mono" id="char-count">0 / 1000</span>
          <span class="ferr" id="err-msg"></span>
        </div>
        <div class="form-footer-row">
          <button type="submit" class="form-submit" id="form-submit">
            <span class="fs-text">Send Message ↗</span>
            <span class="fs-loading" style="display:none">Sending...</span>
          </button>
          <span class="form-note mono">I reply within 24h</span>
        </div>
        <div class="form-success-msg" id="form-success" style="display:none">
          <span class="success-icon">✓</span>
          <div><strong>Message sent!</strong><p>Thanks for reaching out. I'll be in touch within 24 hours.</p></div>
        </div>
        <div class="form-error-msg" id="form-error" style="display:none">
          <span>⚠</span>
          <div><strong>Something went wrong.</strong><p id="form-error-text">Please try again.</p></div>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- ════════════════ FOOTER ════════════════ -->
<footer class="footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <div class="footer-logo">
          <span class="logo-mark"><?= strtoupper(substr($cfg['site_name'] ?? 'AC', 0, 2)) ?></span>
          <span class="logo-text"><?= e($cfg['site_name'] ?? 'shabaz yassen') ?></span>
        </div>
        <p class="footer-tagline">Designing &amp; building the web,<br/>one bold project at a time.</p>
      </div>
      <div class="footer-nav-cols">
        <div class="fnav-col">
          <div class="fnav-label mono">Navigation</div>
          <a href="#work">Work</a><a href="#about">About</a>
          <a href="#services">Services</a><a href="#process">Process</a>
        </div>
        <div class="fnav-col">
          <div class="fnav-label mono">More</div>
          <a href="#testimonials">Clients</a><a href="#blog">Blog</a>
          <a href="#contact">Contact</a><a href="#">Resume ↗</a>
        </div>
        <div class="fnav-col">
          <div class="fnav-label mono">Social</div>
          <?php if (!empty($cfg['site_github'])):   ?><a href="<?= e($cfg['site_github']) ?>"   target="_blank" rel="noopener">GitHub</a><?php endif; ?>
          <?php if (!empty($cfg['site_linkedin'])): ?><a href="<?= e($cfg['site_linkedin']) ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
          <?php if (!empty($cfg['site_twitter'])):  ?><a href="<?= e($cfg['site_twitter']) ?>"  target="_blank" rel="noopener">Twitter</a><?php endif; ?>
          <?php if (!empty($cfg['site_dribbble'])): ?><a href="<?= e($cfg['site_dribbble']) ?>" target="_blank" rel="noopener">Dribbble</a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span class="mono footer-copy">© <?= date('Y') ?> <?= e($cfg['site_name'] ?? 'shabaz yassen') ?>. All rights reserved.</span>
      <button class="back-top" id="back-top">↑ Back to top</button>
      <span class="mono footer-copy">Built with ♥ &amp; PHP</span>
    </div>
  </div>
</footer>

<script src="main.js"></script>
</body>
</html>
