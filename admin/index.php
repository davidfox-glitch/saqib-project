<?php
require_once __DIR__ . '/header.php';

// Fetch all database metrics
$orders = JsonDB::getOrders();
$products = JsonDB::getProducts();
$users = JsonDB::getUsers();

// Calculate total revenue from orders
$revenue = 0;
foreach ($orders as $order) {
    $revenue += $order['total'];
}

// Calculate low stock warnings
$lowStock = 0;
foreach ($products as $product) {
    if ($product['stock'] <= 5) {
        $lowStock++;
    }
}

// Sort orders by date descending
usort($orders, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

// Take 5 most recent orders
$recentOrders = array_slice($orders, 0, 5);
?>

<div class="mb-5">
    
    <!-- Top Greeting Header -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 mb-5">
        <div>
            <h1 class="serif-title mb-1" style="font-size: 1.8rem; font-weight: 300; letter-spacing: 0.05em; color: #1a1a1a;">Dashboard</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.05em;">Overview of Exaltia shop performance and sales activities.</p>
        </div>
        <div class="text-muted" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em;">
            System Local Time: <span class="text-dark fw-bold"><?= date('Y-m-d H:i') ?></span>
        </div>
    </div>

    <!-- Analytics Cards Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-5">
        <!-- Revenue Card -->
        <div class="col">
            <div class="bg-white border rounded p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.12em;">Total Revenue</span>
                    <svg class="admin-accent" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="fs-4 fw-bold text-dark mb-1">$<?= number_format($revenue, 2) ?></p>
                <p class="mb-0 fw-bold text-success" style="font-size: 0.6rem; letter-spacing: 0.05em;">All time earnings</p>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col">
            <div class="bg-white border rounded p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.12em;">Orders Completed</span>
                    <svg class="admin-accent" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <p class="fs-4 fw-bold text-dark mb-1"><?= count($orders) ?></p>
                <p class="mb-0 text-muted" style="font-size: 0.6rem; font-weight: 500; letter-spacing: 0.05em;">Purchased product baskets</p>
            </div>
        </div>

        <!-- Products Card -->
        <div class="col">
            <div class="bg-white border rounded p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.12em;">Active Catalog</span>
                    <svg class="admin-accent" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="fs-4 fw-bold text-dark mb-1"><?= count($products) ?></p>
                <p class="mb-0 text-muted" style="font-size: 0.6rem; font-weight: 500; letter-spacing: 0.05em;">Distinct items in catalog</p>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="col">
            <div class="bg-white border rounded p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.12em;">Low Stock Alerts</span>
                    <svg style="width: 20px; height: 20px; color: <?= $lowStock > 0 ? '#dc2626' : '#d4d4d4' ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="fs-4 fw-bold mb-1 <?= $lowStock > 0 ? 'text-danger' : 'text-dark' ?>"><?= $lowStock ?></p>
                <p class="mb-0 fw-bold" style="font-size: 0.6rem; letter-spacing: 0.05em; color: <?= $lowStock > 0 ? '#f87171' : '#9ca3af' ?>;">
                    <?= $lowStock > 0 ? 'Requires restocking immediately' : 'Inventory is healthy' ?>
                </p>
            </div>
        </div>
    </div>
        <!-- Users Card -->
        <div class="col">
            <div class="bg-white border rounded p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.12em;">Users</span>
                    <svg class="admin-accent" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="fs-4 fw-bold text-dark mb-1"><?= count($users) ?></p>
                <p class="mb-0 text-muted" style="font-size: 0.6rem; font-weight: 500; letter-spacing: 0.05em;">Registered Users</p>
                <a href="index.php" class="stretched-link"></a>
            </div>
        </div>

    <!-- Recent Orders Card -->
    <div class="bg-white border rounded shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="serif-title mb-0" style="font-size: 1.2rem; font-weight: 300; letter-spacing: 0.05em;">Recent Orders</h3>
            <a href="orders.php" class="fw-bold text-uppercase text-decoration-none admin-accent" style="font-size: 0.6rem; letter-spacing: 0.12em;">View All Orders</a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="text-center py-5 text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                No orders have been placed yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.75rem;">
                    <thead>
                        <tr class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.1em;">
                            <th class="border-bottom pb-3">Order ID</th>
                            <th class="border-bottom pb-3">Recipient</th>
                            <th class="border-bottom pb-3">Date</th>
                            <th class="border-bottom pb-3">Total</th>
                            <th class="border-bottom pb-3 text-center">Status</th>
                            <th class="border-bottom pb-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="fw-bold text-dark">#<?= $order['id'] ?></td>
                                <td class="text-secondary"><?= htmlspecialchars($order['shipping_info']['name']) ?></td>
                                <td class="text-muted"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                <td class="text-dark fw-semibold">$<?= number_format($order['total'], 2) ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill fw-bold text-uppercase px-3 py-1 badge-<?= $order['status'] ?>" style="font-size: 0.55rem; letter-spacing: 0.1em;">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="orders.php?order_id=<?= $order['id'] ?>" class="fw-bold text-uppercase text-decoration-none admin-accent" style="font-size: 0.6rem; letter-spacing: 0.1em;">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
