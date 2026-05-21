<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize session cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- CART ACTIONS HANDLING ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    $product = JsonDB::findProductById($productId);
    if ($product && $quantity > 0) {
        $cartKey = $productId . '_' . str_replace(' ', '_', $size) . '_' . str_replace('#', '', $color);
        
        // Check if item already in cart
        if (isset($_SESSION['cart'][$cartKey])) {
            $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
            // Cap at available stock
            $_SESSION['cart'][$cartKey]['quantity'] = min($newQty, $product['stock']);
        } else {
            $_SESSION['cart'][$cartKey] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'category' => $product['category'],
                'image' => $product['image'],
                'size' => $size,
                'color' => $color,
                'quantity' => min($quantity, $product['stock'])
            ];
        }
    }
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $cartKey = isset($_POST['cart_key']) ? $_POST['cart_key'] : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if (isset($_SESSION['cart'][$cartKey])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $product = JsonDB::findProductById($_SESSION['cart'][$cartKey]['id']);
            if ($product) {
                $_SESSION['cart'][$cartKey]['quantity'] = min($quantity, $product['stock']);
            }
        }
    }
    header("Location: cart.php");
    exit;
}

if ($action === 'remove') {
    $cartKey = isset($_GET['key']) ? $_GET['key'] : '';
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'checkout') {
    Auth::requireLogin();
    $currentUser = Auth::getCurrentUser();
    
    if (empty($_SESSION['cart'])) {
        header("Location: cart.php");
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $postal = trim($_POST['postal'] ?? '');

    if (empty($name) || empty($address) || empty($city) || empty($country) || empty($postal)) {
        $_SESSION['checkout_error'] = "All shipping fields are required.";
        header("Location: cart.php");
        exit;
    }

    // Calculate total
    $total = 0;
    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $items[] = $item;
    }

    $shippingInfo = [
        "name" => $name,
        "address" => $address,
        "city" => $city,
        "country" => $country,
        "postal" => $postal
    ];

    // Write to orders db
    $order = JsonDB::createOrder($currentUser['id'], $items, $total, $shippingInfo);
    
    if ($order) {
        // Clear cart
        $_SESSION['cart'] = [];
        header("Location: cart.php?action=success&order_id=" . $order['id']);
        exit;
    } else {
        $_SESSION['checkout_error'] = "Order placement failed. Please try again.";
        header("Location: cart.php");
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Cart Layout Container -->
<div class="container py-5" style="max-width: 1140px;">

    <!-- Success View -->
    <?php if ($action === 'success'): ?>
        <div class="mx-auto text-center py-5" style="max-width: 480px;">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: rgba(143, 151, 121, 0.1);">
                <svg style="width: 40px; height: 40px; color: #8F9779;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="serif-title text-uppercase mb-2" style="font-size: 2.2rem; letter-spacing: 0.08em; font-weight: 300;">ORDER PLACED</h1>
            <p class="small fw-semibold text-muted text-uppercase mb-4" style="letter-spacing: 0.12em; font-size: 0.65rem;">YOUR ORDER HAS BEEN RECORDED SUCCESSFULLY</p>
            
            <div class="bg-light border p-4 text-start mb-4" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em;">
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2 text-muted">
                    <span>Order Reference</span>
                    <span class="text-dark">#<?= htmlspecialchars($_GET['order_id'] ?? '1000') ?></span>
                </div>
                <div class="d-flex justify-content-between text-muted pt-1">
                    <span>Status</span>
                    <span class="text-uppercase" style="color: #8F9779;">Processing</span>
                </div>
            </div>
            
            <p class="small text-muted mb-4 mx-auto" style="max-width: 350px; line-height: 1.6;">
                Thank you for shopping with Exaltia. We will email you with your tracking details as soon as the package is dispatched.
            </p>
            <div class="pt-2">
                <a href="index.php" class="btn btn-brand-dark px-5 py-3">RETURN TO STORE</a>
            </div>
        </div>

    <!-- Empty/Standard View -->
    <?php else: ?>
        <div class="mb-4">
            <h1 class="serif-title text-uppercase mb-2" style="font-size: 2.2rem; letter-spacing: 0.08em; font-weight: 300;">SHOPPING BAG</h1>
            <p class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.12em; font-size: 0.65rem;">REVIEW YOUR ITEMS AND COMPLETE CHECKOUT</p>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-5 border border-dashed" style="border-style: dashed !important; border-color: #dee2e6;">
                <svg class="mx-auto mb-3" style="width: 64px; height: 64px; color: #d4d4d4;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                </svg>
                <p class="small fw-semibold text-muted text-uppercase mb-4" style="letter-spacing: 0.12em; font-size: 0.65rem;">YOUR SHOPPING BAG IS CURRENTLY EMPTY</p>
                <a href="index.php" class="btn btn-brand-dark px-5 py-3">START SHOPPING</a>
            </div>
        <?php else: ?>
            <div class="row g-5 align-items-start">
                
                <!-- Cart Items List (2/3 width) -->
                <div class="col-lg-8">
                    <?php 
                    $subtotal = 0;
                    foreach ($_SESSION['cart'] as $key => $item): 
                        $itemTotal = $item['price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                    ?>
                        <div class="d-flex align-items-start border-bottom pb-4 mb-4 gap-3 gap-md-4">
                            <!-- Image -->
                            <div class="flex-shrink-0 bg-light border overflow-hidden d-flex align-items-center justify-content-center" style="width: 96px; height: 128px;">
                                <?php if (file_exists(__DIR__ . '/' . $item['image'])): ?>
                                    <img src="<?= $item['image'] ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 img-placeholder"></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Information -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">
                                    <h3 class="text-uppercase text-dark mb-0" style="font-size: 0.75rem;"><a href="product.php?id=<?= $item['id'] ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($item['name']) ?></a></h3>
                                    <p class="text-dark mb-0">$<?= number_format($itemTotal, 2) ?></p>
                                </div>
                                <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.6rem; letter-spacing: 0.12em;"><?= htmlspecialchars($item['category']) ?></p>
                                
                                <div class="d-flex align-items-center gap-3 mb-2" style="font-size: 0.6rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted me-1">SIZE:</span>
                                        <span class="text-dark"><?= htmlspecialchars($item['size']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted me-1">COLOR:</span>
                                        <span class="rounded-circle border shadow-sm" style="background-color: <?= $item['color'] ?>; width: 12px; height: 12px; display: inline-block;"></span>
                                    </div>
                                </div>

                                <!-- Quantity update form -->
                                <div class="d-flex align-items-center justify-content-between pt-1">
                                    <form action="cart.php?action=update" method="POST" class="d-flex align-items-center border">
                                        <input type="hidden" name="cart_key" value="<?= $key ?>">
                                        <button 
                                            type="submit" 
                                            name="quantity" 
                                            value="<?= $item['quantity'] - 1 ?>"
                                            class="btn btn-sm border-0 px-2 py-1 text-muted fw-bold"
                                            aria-label="Decrease quantity"
                                        >−</button>
                                        <span class="px-2 text-center fw-bold text-dark" style="font-size: 0.75rem; min-width: 32px;"><?= $item['quantity'] ?></span>
                                        <button 
                                            type="submit" 
                                            name="quantity" 
                                            value="<?= $item['quantity'] + 1 ?>"
                                            class="btn btn-sm border-0 px-2 py-1 text-muted fw-bold"
                                            aria-label="Increase quantity"
                                        >+</button>
                                    </form>

                                    <!-- Remove trigger -->
                                    <a 
                                        href="cart.php?action=remove&key=<?= $key ?>" 
                                        class="text-danger fw-bold text-uppercase text-decoration-none" 
                                        style="font-size: 0.6rem; letter-spacing: 0.1em;"
                                    >REMOVE</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Checkout Summary Panel (1/3 width) -->
                <div class="col-lg-4">
                    <div class="bg-light border p-4 p-md-5">
                        <h2 class="serif-title text-uppercase mb-4" style="font-size: 1.2rem; letter-spacing: 0.12em; font-weight: 300;">ORDER SUMMARY</h2>
                        
                        <!-- Checkout details -->
                        <div class="border-bottom pb-4 mb-4" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em;">
                            <div class="d-flex justify-content-between text-muted mb-3">
                                <span>Subtotal</span>
                                <span class="text-dark">$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between text-muted mb-3">
                                <span>Shipping</span>
                                <?php if ($subtotal >= 150): ?>
                                    <span class="text-uppercase" style="color: #8F9779;">FREE</span>
                                <?php else: ?>
                                    <span class="text-dark">$15.00</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span>Taxes (Estimated)</span>
                                <span class="text-dark">$0.00</span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="d-flex justify-content-between fw-bold text-uppercase text-dark mb-4" style="font-size: 0.85rem; letter-spacing: 0.1em;">
                            <span>Total Bag Value</span>
                            <span>$<?= number_format($subtotal >= 150 ? $subtotal : $subtotal + 15.00, 2) ?></span>
                        </div>

                        <!-- Checkout Authorization Gate -->
                        <div class="pt-2">
                            <?php if (!Auth::isLoggedIn()): ?>
                                <!-- Must log in -->
                                <div class="text-center">
                                    <div class="bg-white border p-3 mb-3">
                                        <p class="small fw-bold text-uppercase text-muted mb-0" style="font-size: 0.6rem; letter-spacing: 0.1em; line-height: 1.5;">
                                            Please log in to your account to complete checkout and record your order.
                                        </p>
                                    </div>
                                    <a href="login.php" class="btn btn-brand-dark w-100 py-3">LOG IN TO CHECKOUT</a>
                                </div>
                            <?php else: ?>
                                <!-- Checkout Form -->
                                <form action="cart.php?action=checkout" method="POST">
                                    <h3 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 0.6rem; letter-spacing: 0.12em;">Shipping Details</h3>
                                    
                                    <?php if (isset($_SESSION['checkout_error'])): ?>
                                        <div class="alert alert-danger py-2 px-3 small fw-bold" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($_SESSION['checkout_error']) ?>
                                        </div>
                                        <?php unset($_SESSION['checkout_error']); ?>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.55rem; letter-spacing: 0.12em;">Recipient Name</label>
                                        <input 
                                            type="text" 
                                            name="name" 
                                            required 
                                            class="form-control rounded-0 border-secondary-subtle py-2 small"
                                            placeholder="John Doe"
                                            value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"
                                        >
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.55rem; letter-spacing: 0.12em;">Shipping Address</label>
                                        <input 
                                            type="text" 
                                            name="address" 
                                            required 
                                            class="form-control rounded-0 border-secondary-subtle py-2 small"
                                            placeholder="123 Fashion Blvd"
                                        >
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.55rem; letter-spacing: 0.12em;">City</label>
                                            <input 
                                                type="text" 
                                                name="city" 
                                                required 
                                                class="form-control rounded-0 border-secondary-subtle py-2 small"
                                                placeholder="New York"
                                            >
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.55rem; letter-spacing: 0.12em;">Postal Code</label>
                                            <input 
                                                type="text" 
                                                name="postal" 
                                                required 
                                                class="form-control rounded-0 border-secondary-subtle py-2 small"
                                                placeholder="10001"
                                            >
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.55rem; letter-spacing: 0.12em;">Country</label>
                                        <input 
                                            type="text" 
                                            name="country" 
                                            required 
                                            class="form-control rounded-0 border-secondary-subtle py-2 small"
                                            placeholder="United States"
                                        >
                                    </div>

                                    <button 
                                        type="submit" 
                                        class="btn btn-brand-dark w-100 py-3 d-flex align-items-center justify-content-center gap-2"
                                    >
                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>COMPLETE PURCHASE</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
