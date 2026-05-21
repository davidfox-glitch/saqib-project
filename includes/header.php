<?php
require_once __DIR__ . '/auth.php';

// Calculate cart count
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

$currentUser = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXALTIA | Artisanal Fashion Store</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS Overrides for a Premium Aesthetic -->
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #fafafa;
            color: #1a1a1a;
        }
        .serif-title {
            font-family: 'Playfair Display', serif;
        }
        
        /* Navbar customizations */
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem !important;
            letter-spacing: 0.25em;
            font-weight: 300;
        }
        .nav-link {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #1a1a1a !important;
            padding: 0.5rem 1rem !important;
        }
        .nav-link:hover {
            color: #8F9779 !important;
        }
        
        /* Button customizations */
        .btn-brand-dark {
            background-color: #1a1a1a;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 1rem 2rem;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-brand-dark:hover {
            background-color: #333333;
            color: #ffffff;
        }
        .btn-brand-outline {
            background-color: transparent;
            color: #1a1a1a;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            border: 1px solid #1a1a1a;
            border-radius: 0;
            padding: 1rem 2rem;
            transition: all 0.3s ease;
        }
        .btn-brand-outline:hover {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        /* Badge styling */
        .badge-cart {
            font-size: 0.65rem;
            background-color: #8F9779;
            color: #ffffff;
            border-radius: 50%;
            padding: 0.25em 0.5em;
        }
        
        /* Dropdown Styling */
        .dropdown-menu-brand {
            border-radius: 0;
            border: 1px solid #f0f0f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            font-size: 0.75rem;
            padding: 0.5rem 0;
        }
        .dropdown-item-brand {
            font-weight: 600;
            letter-spacing: 0.05em;
            padding: 0.6rem 1.2rem;
        }
        .dropdown-item-brand:hover {
            background-color: #f8f9fa;
            color: #8F9779;
        }
        
        /* Image checkerboard placeholder */
        .img-placeholder {
            background-color: #f3f4f6;
            background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), 
                              linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), 
                              linear-gradient(45deg, transparent 75%, #e5e7eb 75%), 
                              linear-gradient(-45deg, transparent 75%, #e5e7eb 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        
        /* General resets */
        a {
            text-decoration: none;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d4d4d4;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a3a3a3;
        }

        /* Custom aspect ratios Bootstrap doesn't have */
        .ratio-3x4 {
            --bs-aspect-ratio: 133.33%;
        }
        .ratio-3x5 {
            --bs-aspect-ratio: 166.67%;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

    <!-- Header Navigation -->
    <header class="fixed-top bg-white" style="box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); border-bottom: 1px solid rgba(0, 0, 0, 0.04);">
        <nav class="navbar navbar-expand-lg navbar-light py-3 px-3 px-md-5">
            <div class="container-fluid d-flex align-items-center justify-content-between" style="max-width: 1280px; margin: 0 auto;">
                
                <!-- Mobile Toggle button -->
                <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Brand (Logo) centered on large screen, left on mobile -->
                <a class="navbar-brand serif-title mx-auto mx-lg-0 order-first" href="index.php">EXALTIA</a>

                <!-- Collapsible Left Items -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-3 text-center mt-3 mt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#must-haves">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#categories">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#denim">Denim</a>
                        </li>
                    </ul>
                </div>

                <!-- Right Side Controls -->
                <div class="d-flex align-items-center gap-2 gap-md-3 order-last">
                    <!-- Search Icon Trigger -->
                    <button class="btn btn-link text-dark p-2" aria-label="Search">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Account Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-2 d-flex align-items-center gap-1 dropdown-toggle text-decoration-none" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <?php if ($currentUser): ?>
                                <span class="d-none d-md-inline text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.1em;"><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-brand" aria-labelledby="accountDropdown">
                            <?php if ($currentUser): ?>
                                <li class="dropdown-header text-muted border-bottom pb-2 mb-1">
                                    Signed in as<br>
                                    <span class="text-dark fw-bold"><?= htmlspecialchars($currentUser['email']) ?></span>
                                </li>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-brand d-flex align-items-center" href="admin/index.php">
                                            <svg class="me-2 text-success" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Admin Dashboard
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item dropdown-item-brand text-danger d-flex align-items-center" href="login.php?action=logout">
                                        <svg class="me-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Log Out
                                    </a>
                                </li>
                            <?php else: ?>
                                <li><a class="dropdown-item dropdown-item-brand" href="login.php">Log In</a></li>
                                <li><a class="dropdown-item dropdown-item-brand" href="register.php">Create Account</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Cart Bag Trigger -->
                    <a href="cart.php" class="btn btn-link text-dark p-2 position-relative text-decoration-none" aria-label="Shopping Bag">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-cart">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

            </div>
        </nav>
    </header>

    <!-- Main Container offset for navigation -->
    <main class="flex-shrink-0" style="padding-top: 80px;">
