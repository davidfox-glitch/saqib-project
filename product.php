<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = JsonDB::findProductById($id);

if (!$product) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container max-w-7xl mx-auto px-4 px-md-5 py-5" 
     x-data="{ 
        selectedSize: '<?= reset($product['sizes']) ?>', 
        selectedColor: '<?= reset($product['colors']) ?>', 
        quantity: 1,
        maxStock: <?= $product['stock'] ?>,
        increment() { if (this.quantity < this.maxStock) this.quantity++ },
        decrement() { if (this.quantity > 1) this.quantity-- }
     }">
    
    <!-- Back to Shop Link -->
    <div class="mb-4">
        <a href="index.php" class="small font-bold text-muted text-uppercase text-decoration-none d-inline-flex align-items-center hover:text-dark transition-colors">
            <svg class="me-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            BACK TO SHOP
        </a>
    </div>

    <!-- Product Split Grid -->
    <div class="row g-5">
        
        <!-- Left Side: Image Gallery -->
        <div class="col-lg-6">
            <div class="ratio ratio-3x4 bg-light border border-light overflow-hidden rounded-sm flex align-items-center justify-content-center">
                <?php if (file_exists(__DIR__ . '/' . $product['image'])): ?>
                    <img src="<?= $product['image'] ?>" class="w-full h-full object-cover" style="object-fit: cover;" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="w-full h-full flex flex-column align-items-center justify-content-center p-5 img-placeholder text-secondary">
                        <svg class="mb-2 stroke-1" style="width: 48px; height: 48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                        </svg>
                        <span class="small font-bold text-uppercase">Place photo here</span>
                        <code class="small bg-white px-2 py-1 rounded shadow-sm mt-2 font-monospace"><?= $product['image'] ?></code>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Gallery Thumbnails -->
            <div class="row g-3 mt-2">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="col-3">
                        <div class="ratio ratio-1x1 bg-light border border-light rounded-sm img-placeholder cursor-pointer"></div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Right Side: Details Sticky Panel -->
        <div class="col-lg-6">
            <div class="sticky-top" style="top: 6rem; z-index: 10;">
                
                <!-- Category, Title & Price -->
                <div class="mb-4">
                    <p class="small font-bold text-muted text-uppercase tracking-wider mb-2"><?= htmlspecialchars($product['category']) ?></p>
                    <h1 class="serif-title text-uppercase font-light mb-3" style="font-size: 2.5rem; letter-spacing: 0.05em;"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="fs-4 font-semibold text-dark">$<?= number_format($product['price'], 2) ?></p>
                </div>

                <!-- Description -->
                <div class="border-top border-light pt-4 mb-4">
                    <h3 class="small font-bold text-uppercase text-muted mb-2">Description</h3>
                    <p class="small text-secondary leading-relaxed font-medium">
                        <?= htmlspecialchars($product['description']) ?>
                    </p>
                </div>

                <!-- Product Form Options -->
                <form action="cart.php?action=add" method="POST" class="border-top border-light pt-4 mb-4 space-y-4">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="size" :value="selectedSize">
                    <input type="hidden" name="color" :value="selectedColor">

                    <!-- Colors -->
                    <div class="mb-4">
                        <span class="small font-bold text-uppercase text-muted d-block mb-3">Color</span>
                        <div class="d-flex align-items-center gap-2">
                            <?php foreach ($product['colors'] as $color): ?>
                                <button 
                                    type="button"
                                    @click="selectedColor = '<?= $color ?>'"
                                    :class="selectedColor === '<?= $color ?>' ? 'border-2 border-dark' : 'border-light'"
                                    class="btn rounded-circle p-0 border shadow-sm transition-all"
                                    style="background-color: <?= $color ?>; width: 26px; height: 26px;"
                                    title="Select Color"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sizes -->
                    <div class="mb-4">
                        <span class="small font-bold text-uppercase text-muted d-block mb-3">Select Size</span>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($product['sizes'] as $size): ?>
                                <button 
                                    type="button"
                                    @click="selectedSize = '<?= $size ?>'"
                                    :class="selectedSize === '<?= $size ?>' ? 'btn-dark' : 'btn-outline-dark'"
                                    class="btn rounded-0 px-4 py-2 small font-bold tracking-widest text-uppercase transition-all"
                                    style="font-size: 0.7rem;"
                                >
                                    <?= htmlspecialchars($size) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity Counter and Checkout Trigger -->
                    <div class="row g-3 align-items-stretch">
                        <!-- Qty Selector -->
                        <div class="col-auto">
                            <div class="input-group border border-dark rounded-0" style="height: 100%;">
                                <button 
                                    type="button" 
                                    @click="decrement()"
                                    class="btn bg-transparent border-0 font-bold px-3 text-secondary"
                                >
                                    -
                                </button>
                                <input 
                                    type="text" 
                                    name="quantity" 
                                    readonly
                                    x-model="quantity"
                                    class="form-control bg-transparent border-0 text-center font-bold text-dark px-0" 
                                    style="width: 40px; font-size: 0.8rem;"
                                >
                                <button 
                                    type="button" 
                                    @click="increment()"
                                    class="btn bg-transparent border-0 font-bold px-3 text-secondary"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Button add to cart -->
                        <div class="col">
                            <?php if ($product['stock'] <= 0): ?>
                                <button 
                                    type="button" 
                                    disabled
                                    class="btn btn-secondary rounded-0 py-3 w-100 font-bold tracking-widest text-uppercase disabled"
                                    style="font-size: 0.75rem;"
                                >
                                    OUT OF STOCK
                                </button>
                            <?php else: ?>
                                <button 
                                    type="submit" 
                                    class="btn btn-brand-dark rounded-0 py-3 w-100 font-bold tracking-widest text-uppercase d-flex align-items-center justify-content-center gap-2"
                                    style="font-size: 0.75rem;"
                                >
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    ADD TO CART
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <!-- shipping guidelines -->
                <div class="border-top border-light pt-4 space-y-2 text-muted" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">
                    <div class="d-flex align-items-center mb-2">
                        <svg class="me-2 text-success" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Free Shipping on orders over $150</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <svg class="me-2 text-success" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>30-day Free and Easy Returns</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
