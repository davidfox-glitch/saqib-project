<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Force admin role protection
Auth::requireAdmin();

$adminUser = Auth::getCurrentUser();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exaltia Admin Panel</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .serif-title {
            font-family: 'Playfair Display', serif;
        }
        /* Admin sidebar colors */
        .admin-sidebar {
            background-color: #111827;
        }
        .admin-accent {
            color: #8F9779;
        }
        .bg-admin-accent {
            background-color: #8F9779;
        }
        .btn-admin-accent {
            background-color: #8F9779;
            color: #fff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-admin-accent:hover {
            background-color: #1a1a1a;
            color: #fff;
        }
        .btn-admin-dark {
            background-color: #1a1a1a;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-admin-dark:hover {
            background-color: #333;
            color: #fff;
        }
        /* Active sidebar link */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9ca3af;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            background-color: #1f2937;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #8F9779;
            color: #ffffff;
        }
        /* Custom image checkerboard for admin preview */
        .img-placeholder-admin {
            background-color: #f3f4f6;
            background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), 
                              linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), 
                              linear-gradient(45deg, transparent 75%, #e5e7eb 75%), 
                              linear-gradient(-45deg, transparent 75%, #e5e7eb 75%);
            background-size: 10px 10px;
            background-position: 0 0, 0 5px, 5px -5px, -5px 0px;
        }
        /* Status badge colors */
        .badge-pending { background-color: #fefce8; color: #ca8a04; }
        .badge-processing { background-color: #eff6ff; color: #2563eb; }
        .badge-shipped { background-color: #faf5ff; color: #9333ea; }
        .badge-completed { background-color: #f0fdf4; color: #16a34a; }
        /* Stock badges */
        .stock-ok { background-color: #f0fdf4; color: #16a34a; }
        .stock-low { background-color: #fff7ed; color: #ea580c; }
        .stock-out { background-color: #fef2f2; color: #dc2626; }
    </style>
</head>
<body class="min-vh-100 d-flex">

    <!-- Sidebar Navigation (Desktop) -->
    <aside class="d-none d-md-flex flex-column justify-content-between flex-shrink-0 admin-sidebar text-secondary" style="width: 256px; border-right: 1px solid #1f2937;">
        <div class="p-4">
            <!-- Brand -->
            <div class="d-flex align-items-center gap-3 pb-4 mb-4" style="border-bottom: 1px solid #1f2937;">
                <span class="serif-title text-white fw-bold" style="font-size: 1.2rem; letter-spacing: 0.2em;">EXALTIA</span>
                <span class="fw-bold text-uppercase px-2 py-1 rounded" style="font-size: 0.55rem; background-color: #27303f; color: #9ca3af; letter-spacing: 0.08em;">ADMIN</span>
            </div>
            
            <!-- Menu Links -->
            <nav class="d-flex flex-column gap-2">
                <a href="/admin/index.php" class="sidebar-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                    <svg class="me-3 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                    </svg>
                    Dashboard
                </a>

                <a href="/admin/products.php" class="sidebar-link <?= $current_page === 'products.php' ? 'active' : '' ?>">
                    <svg class="me-3 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Products
                </a>

                <a href="/admin/hero.php" class="sidebar-link <?= $current_page === 'hero.php' ? 'active' : '' ?>">
                    <svg class="me-3 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Hero Slider
                </a>

                <a href="/admin/orders.php" class="sidebar-link <?= $current_page === 'orders.php' ? 'active' : '' ?>">
                    <svg class="me-3 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Orders
                </a>

                <a href="/admin/dashboard.php" class="sidebar-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                    <svg class="me-3 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer Info -->
        <div class="p-4" style="border-top: 1px solid #1f2937;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold text-uppercase bg-admin-accent" style="width: 32px; height: 32px; font-size: 0.7rem;">
                    <?= htmlspecialchars(substr($adminUser['name'], 0, 2)) ?>
                </div>
                <div class="text-truncate" style="font-size: 0.75rem;">
                    <p class="fw-bold text-white mb-0"><?= htmlspecialchars($adminUser['name']) ?></p>
                    <p class="text-secondary text-truncate mb-0" style="font-size: 0.65rem;"><?= htmlspecialchars($adminUser['email']) ?></p>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <a href="/index.php" class="btn btn-sm w-100 text-center fw-bold text-uppercase py-2 text-secondary" style="font-size: 0.6rem; background-color: #27303f; border-radius: 4px;">Store</a>
                </div>
                <div class="col-6">
                    <a href="/login.php?action=logout" class="btn btn-sm w-100 text-center fw-bold text-uppercase py-2" style="font-size: 0.6rem; background-color: #450a0a; color: #f87171; border-radius: 4px;">Logout</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        
        <!-- Mobile Top Nav -->
        <header class="admin-sidebar text-white p-3 d-flex align-items-center justify-content-between d-md-none shadow">
            <span class="serif-title" style="font-size: 1.1rem; letter-spacing: 0.2em;">EXALTIA</span>
            <div class="d-flex align-items-center gap-2" style="font-size: 0.7rem;">
                <a href="/admin/index.php" class="text-secondary text-decoration-none px-2 py-1">Home</a>
                <a href="/admin/products.php" class="text-secondary text-decoration-none px-2 py-1">Products</a>
                <a href="/admin/hero.php" class="text-secondary text-decoration-none px-2 py-1">Hero</a>
                <a href="/admin/orders.php" class="text-secondary text-decoration-none px-2 py-1">Orders</a>
                <a href="/admin/dashboard.php" class="text-secondary text-decoration-none px-2 py-1">Users</a>
                <a href="../login.php?action=logout" class="text-decoration-none px-2 py-1" style="color: #f87171;">Logout</a>
            </div>
        </header>

        <!-- Main Inner Panel -->
        <main class="flex-grow-1 p-4 p-md-5" style="max-width: 1140px; width: 100%; margin: 0 auto;">
