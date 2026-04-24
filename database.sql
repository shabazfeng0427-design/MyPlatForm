-- ============================================================
--  PORTFOLIO DATABASE — database.sql
--  Run this file first to create all tables and seed data.
--  MySQL 5.7+ / MariaDB 10.3+
--  Usage: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portfolio_db;

-- ─── SETTINGS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  `key`      VARCHAR(100) NOT NULL UNIQUE,
  value      TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings (`key`, value) VALUES
  ('site_name',        'shabaz yassen'),
  ('site_tagline',     'Full-Stack Developer & UI Designer'),
  ('site_email',       'alex@alexchen.dev'),
  ('site_location',    'San Francisco, CA'),
  ('site_available',   '1'),
  ('site_github',      'https://github.com/alexchen'),
  ('site_linkedin',    'https://linkedin.com/in/alexchen'),
  ('site_twitter',     'https://twitter.com/alexchen'),
  ('site_dribbble',    'https://dribbble.com/alexchen'),
  ('hero_subtitle',    'I design and engineer high-performance web experiences that convert visitors into believers.'),
  ('about_bio_1',      'I''m a full-stack developer & product designer with 5+ years shipping products people actually use. I care about the whole stack — from pixel-perfect UI to clean, maintainable architecture.'),
  ('about_bio_2',      'Previously at <strong>Stripe</strong>, <strong>Vercel</strong>, and two venture-backed startups. Now independent and taking on select projects globally.'),
  ('about_bio_3',      'Outside of work: trail runner, coffee nerd, and occasional open-source contributor.')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ─── PROJECTS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  slug        VARCHAR(200) NOT NULL UNIQUE,
  description TEXT,
  category    VARCHAR(100),           -- comma-separated: web,mobile,design,oss
  tags        VARCHAR(500),           -- comma-separated tag list
  metrics     VARCHAR(500),           -- JSON string of metric objects
  thumb_class VARCHAR(50),            -- CSS class for gradient bg: pi-1..pi-6
  thumb_icon  VARCHAR(10),            -- emoji
  link_url    VARCHAR(500),
  link_label  VARCHAR(100),
  year        YEAR,
  featured    TINYINT(1) DEFAULT 0,
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO projects (title, slug, description, category, tags, metrics, thumb_class, thumb_icon, link_url, link_label, year, featured, sort_order) VALUES
('ShopFlow',   'shopflow',   'Next-gen e-commerce platform with real-time inventory, AI recommendations, and 3× faster checkout. Scaled to 200k monthly users in 6 months.',
  'web', 'React,Node.js,AI,AWS',
  '[{"val":"3×","label":"faster checkout"},{"val":"200k","label":"monthly users"},{"val":"40%","label":"revenue lift"}]',
  'pi-1','🛍️','#','Case study →', 2024, 1, 1),
('DataViz Pro','dataviz-pro','Real-time analytics dashboard trusted by 200+ companies. Beautiful charts, team collab, and one-click export.',
  'web,design', 'D3.js,TypeScript,WebSockets',
  NULL, 'pi-2','📊','#','Case study →', 2024, 0, 2),
('Palette UI', 'palette-ui', 'Open-source design system used by 800+ developers. 120+ components, fully accessible, dark-mode ready.',
  'oss,design', 'Figma,CSS,OSS',
  NULL, 'pi-3','🎨','#','View on GitHub →', 2023, 0, 3),
('Fintrack',   'fintrack',   'Finance app for 50k+ users. Bank integration with 200+ institutions. Featured in App Store Best of 2023.',
  'mobile', 'React Native,Python,Plaid',
  NULL, 'pi-4','💰','#','Case study →', 2023, 0, 4),
('MindOS',     'mindos',     'AI second brain — search across notes, docs, and emails via custom RAG pipeline and vector search.',
  'web', 'LLMs,RAG,Next.js',
  NULL, 'pi-5','🤖','#','Case study →', 2024, 0, 5),
('Blitz CLI',  'blitz-cli',  'Developer CLI scaffolding tool. 4k GitHub stars, 12k weekly npm downloads. Blazing fast.',
  'oss', 'CLI,Rust,OSS',
  NULL, 'pi-6','⚡','#','View on GitHub →', 2024, 0, 6);

-- ─── SERVICES ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS services (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  num         VARCHAR(5),
  icon        VARCHAR(10),
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  items       TEXT,                   -- pipe-separated list items
  price_from  VARCHAR(100),
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO services (num, icon, title, description, items, price_from, sort_order) VALUES
('01','⚡','Web Development',
  'Fast, accessible, and scalable web applications engineered with modern stacks and best-in-class architecture.',
  'React / Next.js / Remix|REST & GraphQL APIs|Database design & optimization|Cloud deployment (AWS, GCP)|Performance & Core Web Vitals',
  '$4,500', 1),
('02','🎯','UI / UX Design',
  'User-centered designs that look stunning and convert visitors. From research to high-fidelity prototypes.',
  'User research & personas|Wireframes & prototypes|Design systems & tokens|Responsive & mobile-first|Usability testing',
  '$2,800', 2),
('03','📱','Mobile Apps',
  'Cross-platform apps with native feel, offline support, and App Store-ready polish for iOS & Android.',
  'React Native / Expo|App Store publishing|Push notifications|Offline-first architecture|OTA updates',
  '$6,500', 3),
('04','🤖','AI Integration',
  'Integrate cutting-edge AI into your product. LLM chat, RAG pipelines, vision, and custom fine-tuning.',
  'LLM API integration|RAG & vector search|AI chat interfaces|Data pipelines|Model fine-tuning',
  '$3,500', 4);

-- ─── TESTIMONIALS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonials (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  role        VARCHAR(200),
  company     VARCHAR(200),
  quote       TEXT NOT NULL,
  avatar_initials VARCHAR(5),
  avatar_color VARCHAR(20) DEFAULT 'var(--accent)',
  rating      TINYINT DEFAULT 5,
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO testimonials (name, role, company, quote, avatar_initials, avatar_color, rating, sort_order) VALUES
('Sarah Lin','CEO','NovaTech',
  'Alex delivered something that exceeded every expectation. The design was jaw-dropping, the code was immaculate, and they hit every single deadline. Our conversion rate jumped 40% in the first month.',
  'SL','var(--accent)', 5, 1),
('Marcus Reid','CTO','Forma Labs',
  'I\'ve worked with dozens of freelancers. Alex is in a completely different league. They understood our vision on day one, asked exactly the right questions, and shipped something beautiful in record time.',
  'MR','#06B6D4', 5, 2),
('Jen Kim','Product Lead','Pulse',
  'Alex rebuilt our entire dashboard and the team fell in love instantly. Clear communication, meticulous attention to detail, and delivered 2 weeks ahead of schedule.',
  'JK','#F43F5E', 5, 3),
('David Wang','Founder','HelpDesk AI',
  'The AI integration Alex built reduced our support ticket volume by 60%. Their ability to translate complex requirements into a polished, usable product is remarkable.',
  'DW','#F59E0B', 5, 4);

-- ─── EXPERIENCE / TIMELINE ───────────────────────────────────
CREATE TABLE IF NOT EXISTS experience (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  period      VARCHAR(100),
  role        VARCHAR(200) NOT NULL,
  company     VARCHAR(200),
  description TEXT,
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO experience (period, role, company, description, sort_order) VALUES
('2022 – Now', 'Independent Consultant', NULL,
  'Working with global startups and agencies on design systems, web apps, and AI integrations.', 1),
('2021 – 2022','Senior Engineer','Vercel',
  'Worked on core Next.js tooling and the dashboard. Shipped Analytics v2 to 500k+ users.', 2),
('2019 – 2021','Frontend Engineer','Stripe',
  'Built the Stripe Dashboard UI. Led the design system migration to a modern token-based approach.', 3),
('2019','B.S. Computer Science','Stanford University',
  'Focus on HCI and distributed systems. Dean\'s List. Senior thesis on design system accessibility.', 4);

-- ─── BLOG POSTS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS blog_posts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(500) NOT NULL,
  slug        VARCHAR(500) NOT NULL UNIQUE,
  excerpt     TEXT,
  content     LONGTEXT,
  category    VARCHAR(100),
  img_class   VARCHAR(50),            -- CSS class: bi-1, bi-2, bi-3
  published   TINYINT(1) DEFAULT 0,
  featured    TINYINT(1) DEFAULT 0,
  sort_order  INT DEFAULT 0,
  published_at DATE,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO blog_posts (title, slug, excerpt, category, img_class, published, featured, sort_order, published_at) VALUES
('Building RAG pipelines that actually work in production',
  'building-rag-pipelines',
  'Lessons learned after shipping three LLM-powered products — the surprising bottlenecks, eval strategies, and tools I wish I\'d used earlier.',
  'Engineering', 'bi-1', 1, 1, 1, '2025-04-12'),
('The design system mistakes I made so you don\'t have to',
  'design-system-mistakes',
  'Tokens, naming conventions, and component APIs that scale. A retrospective on Palette UI\'s architecture.',
  'Design', 'bi-2', 1, 0, 2, '2025-03-28'),
('Getting a 100 Lighthouse score on a React app',
  'lighthouse-100-react',
  'Every optimization that made the difference: lazy loading, font strategies, critical CSS, and modern image formats.',
  'Performance', 'bi-3', 1, 0, 3, '2025-02-14');

-- ─── CONTACT MESSAGES ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  email       VARCHAR(200) NOT NULL,
  budget      VARCHAR(100),
  project_types VARCHAR(300),         -- comma-separated checked values
  message     TEXT NOT NULL,
  ip_address  VARCHAR(45),
  user_agent  VARCHAR(500),
  is_read     TINYINT(1) DEFAULT 0,
  is_replied  TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── PAGE VIEWS / ANALYTICS ──────────────────────────────────
CREATE TABLE IF NOT EXISTS page_views (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  page        VARCHAR(200) DEFAULT '/',
  ip_address  VARCHAR(45),
  user_agent  VARCHAR(500),
  referer     VARCHAR(1000),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── SKILLS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS skills (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  percentage  TINYINT NOT NULL DEFAULT 80,
  tab_group   ENUM('dev','design','tools') DEFAULT 'dev',
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO skills (name, percentage, tab_group, sort_order) VALUES
('React / Next.js', 95, 'dev', 1),
('Node.js / APIs',  90, 'dev', 2),
('TypeScript',      88, 'dev', 3),
('Python / ML',     78, 'dev', 4),
('Cloud / DevOps',  72, 'dev', 5),
('Figma / Prototyping', 92, 'design', 1),
('UI / UX Design',  88, 'design', 2),
('Design Systems',  85, 'design', 3),
('Motion Design',   70, 'design', 4),
('Git / GitHub',    97, 'tools', 1),
('Docker / K8s',    75, 'tools', 2),
('AWS / GCP',       80, 'tools', 3);

-- ─── PROCESS STEPS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS process_steps (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  num         VARCHAR(5),
  icon        VARCHAR(10),
  title       VARCHAR(200),
  description TEXT,
  duration    VARCHAR(100),
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO process_steps (num, icon, title, description, duration, sort_order) VALUES
('01','💬','Discovery Call',   'We discuss your goals, timeline, and budget. I ask the right questions to understand what success looks like for you.',   '~ 1 hour',      1),
('02','📋','Proposal & Scope', 'A detailed proposal: deliverables, timeline, cost, and payment terms. No vague estimates — everything in writing.',          '2–3 days',      2),
('03','🎨','Design & Build',   'Weekly check-ins, shared Figma boards, and a live staging link. You''re always in the loop.',                               '2–8 weeks',     3),
('04','🚀','Launch & Support', 'I handle deployment and go-live. Then 30 days free support to catch anything post-launch.',                                  '30-day support', 4);

-- ─── ADMIN USER ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email         VARCHAR(200),
  last_login    TIMESTAMP NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=Admin@1234 (CHANGE THIS!)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@alexchen.dev')
ON DUPLICATE KEY UPDATE id = id;

-- ─── RATE LIMIT / SPAM PROTECTION ────────────────────────────
CREATE TABLE IF NOT EXISTS rate_limits (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  action     VARCHAR(100) NOT NULL,
  attempts   INT DEFAULT 1,
  window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_action (ip_address, action)
) ENGINE=InnoDB;

-- ─── DONE ────────────────────────────────────────────────────
SELECT 'Database setup complete. All tables created and seeded.' AS status;
