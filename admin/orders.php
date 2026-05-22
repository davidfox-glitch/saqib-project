<?php
require_once __DIR__ . '/header.php';

$error = '';
$success = '';

// Handle Status Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $new_status = trim($_POST['status'] ?? '');

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed'];
    if (in_array($new_status, $valid_statuses)) {
        if (JsonDB::updateOrderStatus($order_id, $new_status)) {
            $success = "Order status updated successfully.";
        } else {
            $error = "Failed to update order status.";
        }
    } else {
        $error = "Invalid order status.";
    }
}

// Fetch single order details if specified
$selectedOrder = null;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id > 0) {
    $selectedOrder = JsonDB::findOrderById($order_id);
    if (!$selectedOrder) {
        $error = "Order #{$order_id} not found.";
    }
}

// Fetch all orders
$orders = JsonDB::getOrders();

// Sort orders by date descending
usort($orders, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>

<div class="mb-5">

    <!-- Top Header Navigation -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 pb-4 mb-4 border-bottom">
        <div>
            <h1 class="serif-title mb-1" style="font-size: 1.8rem; font-weight: 300; letter-spacing: 0.05em; color: #1a1a1a;">Orders Registry</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.05em;">Review purchase receipts, client details, and shipment status.</p>
        </div>
        
        <?php if ($selectedOrder): ?>
            <a href="/admin/orders.php" class="btn btn-outline-secondary px-4 py-2 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em; border-radius: 2px;">BACK TO LIST</a>
        <?php endif; ?>
    </div>

    <!-- Error/Success Feedback -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 px-3 fw-semibold" style="font-size: 0.75rem; border-left: 3px solid #dc3545;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success py-2 px-3 fw-semibold" style="font-size: 0.75rem; border-left: 3px solid #198754;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- ORDER DETAIL VIEW -->
    <?php if ($selectedOrder): ?>
        <div class="row g-4">
            
            <!-- Left Side: Order Items and Client Info (2/3 width) -->
            <div class="col-lg-8">
                
                <!-- Client & Shipping Box -->
                <div class="bg-white border rounded shadow-sm p-4 p-md-5 mb-4">
                    <h3 class="serif-title text-uppercase border-bottom pb-3 mb-4" style="font-size: 1.1rem; font-weight: 300; letter-spacing: 0.05em;">Order Details #<?= $selectedOrder['id'] ?></h3>
                    
                    <div class="row g-4" style="font-size: 0.75rem; line-height: 1.6;">
                        <div class="col-sm-6">
                            <p class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.6rem; letter-spacing: 0.12em;">Customer</p>
                            <p class="text-dark fw-bold mb-1" style="font-size: 0.9rem;"><?= htmlspecialchars($selectedOrder['shipping_info']['name']) ?></p>
                            <p class="text-muted mb-1">Account ID: #<?= $selectedOrder['user_id'] ?></p>
                            <p class="text-muted mb-0">Date Placed: <?= date('M d, Y H:i:s', strtotime($selectedOrder['created_at'])) ?></p>
                        </div>
                        
                        <div class="col-sm-6">
                            <p class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.6rem; letter-spacing: 0.12em;">Shipping Destination</p>
                            <p class="text-dark fw-semibold mb-1"><?= htmlspecialchars($selectedOrder['shipping_info']['address']) ?></p>
                            <p class="text-muted mb-1"><?= htmlspecialchars($selectedOrder['shipping_info']['city']) ?>, <?= htmlspecialchars($selectedOrder['shipping_info']['postal']) ?></p>
                            <p class="text-muted mb-0"><?= htmlspecialchars($selectedOrder['shipping_info']['country']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Order items table -->
                <div class="bg-white border rounded shadow-sm p-4 p-md-5">
                    <h4 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.6rem; letter-spacing: 0.12em;">Purchased Items</h4>
                    
                    <?php foreach ($selectedOrder['items'] as $item): ?>
                        <div class="d-flex align-items-center py-3 gap-3 border-bottom" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em;">
                            <!-- Thumb -->
                            <div class="border overflow-hidden d-flex align-items-center justify-content-center rounded bg-light flex-shrink-0" style="width: 48px; height: 64px;">
                                <?php if (file_exists(__DIR__ . '/../' . $item['image'])): ?>
                                    <img src="/<?= $item['image'] ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumb">
                                <?php else: ?>
                                    <div class="w-100 h-100 img-placeholder-admin"></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex-grow-1">
                                <p class="text-dark fw-bold text-uppercase mb-0"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-muted text-uppercase mb-0" style="font-size: 0.6rem;">Size: <?= htmlspecialchars($item['size']) ?> | Color: <?= htmlspecialchars($item['color']) ?></p>
                            </div>

                            <!-- Price x Quantity -->
                            <div class="text-end flex-shrink-0">
                                <p class="text-dark mb-0">$<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                <p class="text-muted mb-0" style="font-size: 0.6rem;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Side: Order Status Action Box (1/3 width) -->
            <div class="col-lg-4">
                <div class="bg-white border rounded shadow-sm p-4 p-md-5">
                    <h3 class="serif-title text-uppercase border-bottom pb-3 mb-4" style="font-size: 1.1rem; font-weight: 300; letter-spacing: 0.05em;">Order Status</h3>
                    
                    <div class="mb-3" style="font-size: 0.75rem;">
                        <div class="d-flex justify-content-between text-muted mb-2">
                            <span>Order Total</span>
                            <span class="text-dark fw-bold" style="font-size: 0.9rem;">$<?= number_format($selectedOrder['total'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-muted pt-1">
                            <span>Current Status</span>
                            <span class="badge rounded-pill fw-bold text-uppercase px-3 py-1 badge-<?= $selectedOrder['status'] ?>" style="font-size: 0.55rem; letter-spacing: 0.1em;">
                                <?= htmlspecialchars($selectedOrder['status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Change status form -->
                    <form action="/admin/orders.php?order_id=<?= $selectedOrder['id'] ?>" method="POST" class="pt-3 border-top">
                        <input type="hidden" name="order_id" value="<?= $selectedOrder['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Update Status</label>
                            <select 
                                name="status" required
                                class="form-select rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            >
                                <option value="pending" <?= $selectedOrder['status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                                <option value="processing" <?= $selectedOrder['status'] === 'processing' ? 'selected' : '' ?>>Processing Order</option>
                                <option value="shipped" <?= $selectedOrder['status'] === 'shipped' ? 'selected' : '' ?>>Shipped / Dispatched</option>
                                <option value="completed" <?= $selectedOrder['status'] === 'completed' ? 'selected' : '' ?>>Completed / Delivered</option>
                            </select>
                        </div>

                        <button type="submit" name="update_status" class="btn btn-admin-dark w-100 py-3">UPDATE STATUS</button>
                    </form>
                </div>
            </div>
        </div>

    <!-- LIST ORDERS VIEW -->
    <?php else: ?>
        <div class="bg-white border rounded shadow-sm p-4 p-md-5">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5 text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                    No orders have been registered.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.1em;">
                                <th class="border-bottom pb-3">Order ID</th>
                                <th class="border-bottom pb-3">Recipient</th>
                                <th class="border-bottom pb-3">Date Placed</th>
                                <th class="border-bottom pb-3">Items Count</th>
                                <th class="border-bottom pb-3">Total Amount</th>
                                <th class="border-bottom pb-3 text-center">Status</th>
                                <th class="border-bottom pb-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="fw-bold text-dark">#<?= $order['id'] ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($order['shipping_info']['name']) ?></td>
                                    <td class="text-muted"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="text-secondary"><?= count($order['items']) ?> items</td>
                                    <td class="text-dark fw-semibold">$<?= number_format($order['total'], 2) ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold text-uppercase px-3 py-1 badge-<?= $order['status'] ?>" style="font-size: 0.55rem; letter-spacing: 0.1em;">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="/admin/orders.php?order_id=<?= $order['id'] ?>" class="fw-bold text-uppercase text-decoration-none admin-accent" style="font-size: 0.6rem; letter-spacing: 0.1em;">Manage Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
