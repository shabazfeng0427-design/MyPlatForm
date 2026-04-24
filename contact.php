<?php
// ============================================================
//  contact.php — Contact Form Handler (AJAX endpoint)
//  Accepts POST, validates, saves to DB, sends email.
//  Returns JSON response.
// ============================================================
require_once 'config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// ── CSRF Check ───────────────────────────────────────────────
$token = trim($_POST['csrf_token'] ?? '');
if (!verifyCsrf($token)) {
    jsonResponse(false, 'Invalid request. Please reload the page and try again.');
}

// ── Rate Limit ────────────────────────────────────────────────
if (!checkRateLimit('contact_form')) {
    jsonResponse(false, 'Too many submissions. Please wait an hour before trying again.');
}

// ── Input Sanitization ───────────────────────────────────────
$name          = trim($_POST['name']    ?? '');
$email         = trim($_POST['email']   ?? '');
$budget        = trim($_POST['budget']  ?? '');
$message       = trim($_POST['message'] ?? '');
$projectTypes  = $_POST['project_types'] ?? [];

// ── Validation ───────────────────────────────────────────────
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Name is required.';
} elseif (mb_strlen($name) > 200) {
    $errors['name'] = 'Name is too long (max 200 characters).';
}

if (empty($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!isValidEmail($email)) {
    $errors['email'] = 'Please enter a valid email address.';
} elseif (mb_strlen($email) > 200) {
    $errors['email'] = 'Email is too long.';
}

if (empty($message)) {
    $errors['message'] = 'Message is required.';
} elseif (mb_strlen($message) > 1000) {
    $errors['message'] = 'Message is too long (max 1000 characters).';
} elseif (mb_strlen($message) < 10) {
    $errors['message'] = 'Message is too short (at least 10 characters).';
}

// Honeypot spam check (hidden field in form)
if (!empty($_POST['website'])) {
    // Bot filled in honeypot — silently accept but don't save
    jsonResponse(true, 'Message sent successfully! I\'ll be in touch soon.');
}

if (!empty($errors)) {
    jsonResponse(false, 'Please fix the errors below.', ['errors' => $errors]);
}

// ── Sanitize project types ───────────────────────────────────
$allowedTypes = ['Web App','UI/UX Design','Mobile App','AI Integration','Other'];
$cleanTypes   = array_filter($projectTypes, fn($t) => in_array($t, $allowedTypes));
$typesStr     = implode(', ', $cleanTypes);

// ── Save to Database ─────────────────────────────────────────
try {
    $messageId = DB::insert(
        'INSERT INTO contact_messages
            (name, email, budget, project_types, message, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [
            $name,
            $email,
            $budget,
            $typesStr,
            $message,
            getClientIP(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]
    );
} catch (Exception $e) {
    error_log('Contact form DB error: ' . $e->getMessage());
    jsonResponse(false, 'A database error occurred. Please try again later.');
}

// ── Send notification email ───────────────────────────────────
$siteName  = DB::setting('site_name', 'shabaz yassen');
$subject   = $siteName . ' — New Contact Message';
$mailTo    = DB::setting('site_email', MAIL_TO);
$mailFrom  = MAIL_FROM;

$emailBody = "New contact form submission (#$messageId)\n\n"
    . "Name:          $name\n"
    . "Email:         $email\n"
    . "Budget:        " . ($budget ?: 'Not specified') . "\n"
    . "Project Types: " . ($typesStr ?: 'Not specified') . "\n"
    . "Date:          " . date('Y-m-d H:i:s') . "\n"
    . "IP Address:    " . getClientIP() . "\n\n"
    . "Message:\n"
    . str_repeat('-', 60) . "\n"
    . $message . "\n"
    . str_repeat('-', 60) . "\n\n"
    . "Reply to this sender at: $email";

$headers = implode("\r\n", [
    "From: $siteName <$mailFrom>",
    "Reply-To: $name <$email>",
    "X-Mailer: PHP/" . PHP_VERSION,
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
]);

@mail($mailTo, $subject, $emailBody, $headers);

// ── Send auto-reply to sender ─────────────────────────────────
$autoReply = "Hi $name,\n\n"
    . "Thanks for reaching out! I've received your message and will be in touch within 24 hours.\n\n"
    . "Here's a copy of what you sent:\n\n"
    . str_repeat('-', 60) . "\n"
    . $message . "\n"
    . str_repeat('-', 60) . "\n\n"
    . "Talk soon,\n$siteName";

$autoHeaders = implode("\r\n", [
    "From: $siteName <$mailFrom>",
    "X-Mailer: PHP/" . PHP_VERSION,
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
]);

@mail($email, "Thanks for your message — $siteName", $autoReply, $autoHeaders);

// ── Success ───────────────────────────────────────────────────
jsonResponse(true, "Message sent! I'll be in touch within 24 hours.", ['id' => $messageId]);
