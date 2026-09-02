<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_check.php';

$pageTitle = $pageTitle ?? 'CODE X - Personal Financial Management & AI Guidance';
$pageDescription = $pageDescription ?? 'CODE X is an intelligent AI-powered personal financial management system. Understand your money, track budgets, save for goals, and control your financial future.';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= sanitize($pageDescription) ?>">
    <meta name="author" content="Code X Technology">
    <title><?= sanitize($pageTitle) ?></title>
    
    <!-- Open Graph SEO -->
    <meta property="og:title" content="<?= sanitize($pageTitle) ?>">
    <meta property="og:description" content="<?= sanitize($pageDescription) ?>">
    <meta property="og:type" content="website">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Custom Modern Style -->
    <link href="<?= getBaseUrl() ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>

<!-- Flash Notification Area -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show border-0 shadow-lg text-white" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i><?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>
