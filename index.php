<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all products
$products = JsonDB::getProducts();

// Filter products for category focus sections
$polos = array_filter($products, function($p) { return $p['category'] === 'Polos'; });
$shirts = array_filter($products, function($p) { return $p['category'] === 'Shirts'; });
$denim = array_filter($products, function($p) { return $p['category'] === 'Denim'; });

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="position-relative bg-light flex-column justify-content-between overflow-hidden d-flex" style="min-h: 90vh;">
    <!-- Image Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 z-0">
        <?php if (file_exists(__DIR__ . '/public/images/hero_model.jpg')): ?>
            <img src="public/images/hero_model.jpg" class="w-100 h-100 object-fit-cover opacity-90" style="object-fit: cover;" alt="Artisanal Collection Hero">
        <?php else: ?>
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-5 img-placeholder opacity-50">
                <svg class="mb-2 stroke-1" style="width: 48px; height: 48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                </svg>
                <span class="small font-bold text-uppercase tracking-widest text-center">Place Hero Image Here<br><code>public/images/hero_model.jpg</code></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Centered Header Titles -->
    <div class="position-relative z-1 flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center px-4" style="padding-top: 10rem; padding-bottom: 5rem;">
        <h1 class="serif-title text-uppercase font-light text-dark mb-0" style="font-size: calc(2.2rem + 3.5vw); letter-spacing: 0.3em; line-height: 1;">
            ARTISANAL
        </h1>
        <h1 class="serif-title text-uppercase font-light text-dark mt-2 mb-4" style="font-size: calc(2.2rem + 3.5vw); letter-spacing: 0.3em; line-height: 1;">
            COLLECTION
        </h1>
        <div class="mt-4">
            <a href="#must-haves" class="btn btn-brand-outline px-5 py-3">
                DISCOVER MORE
            </a>
        </div>
    </div>

    <!-- Horizontal Strip of items on Hero Bottom (As in screenshot mockup) -->
    <div class="position-relative z-1 bg-white border-top border-light py-3 overflow-x-auto whitespace-nowrap scrollbar-none" style="background-color: rgba(255, 255, 255, 0.75); backdrop-filter: blur(4px);">
        <div class="container max-w-7xl mx-auto px-4 px-md-5">
            <div class="d-flex align-items-center justify-content-around gap-4" style="min-width: max-content;">
                <?php foreach (array_slice($products, 0, 6) as $p): ?>
                    <!-- Clicking goes to product.php details page -->
                    <a href="product.php?id=<?= $p['id'] ?>" class="d-flex align-items-center text-dark text-decoration-none hover:opacity-75 transition-opacity">
                        <div class="bg-light border border-light overflow-hidden me-3 flex-shrink-0" style="width: 48px; height: 56px;">
                            <?php if (file_exists(__DIR__ . '/' . $p['image'])): ?>
                                <img src="<?= $p['image'] ?>" class="w-100 h-100 object-fit-cover" style="object-fit: cover;" alt="Thumbnail">
                            <?php else: ?>
                                <div class="w-100 h-100 img-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="small font-bold text-uppercase tracking-wider mb-0" style="font-size: 0.65rem;"><?= htmlspecialchars($p['name']) ?></p>
                            <p class="small text-muted font-bold mb-0" style="font-size: 0.65rem;">$<?= number_format($p['price'], 2) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Categories Focus Section -->
<section id="categories" class="container max-w-7xl mx-auto py-5 px-4 px-md-5 my-5">
    <div class="mb-5" style="max-width: 450px;">
        <h2 class="small font-bold text-muted text-uppercase tracking-widest mb-3">CATEGORIES IN FOCUS</h2>
        <div class="d-flex gap-4 small font-bold text-uppercase tracking-widest text-dark">
            <span class="border-bottom border-dark pb-1 cursor-pointer">POLOS</span>
            <span class="text-muted pb-1 cursor-pointer hover:text-dark transition-colors">SHIRTS</span>
            <span class="text-muted pb-1 cursor-pointer hover:text-dark transition-colors">DENIM</span>
        </div>
    </div>

    <!-- 3 Column grid list -->
    <div class="row g-4">
        <!-- Polo Column -->
        <?php $pPolo = reset($polos); if ($pPolo): ?>
        <div class="col-md-4">
            <div class="card border-0 rounded-0 bg-transparent space-y-3">
                <div class="position-relative overflow-hidden bg-light ratio ratio-3x4 border border-light">
                    <!-- Image click goes to product.php -->
                    <a href="product.php?id=<?= $pPolo['id'] ?>">
                        <?php if (file_exists(__DIR__ . '/' . $pPolo['image'])): ?>
                            <img src="<?= $pPolo['image'] ?>" class="w-100 h-100 object-fit-cover card-img-top rounded-0" style="object-fit: cover;" alt="<?= htmlspecialchars($pPolo['name']) ?>">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 img-placeholder text-secondary text-center">
                                <span class="small font-bold text-uppercase">Place photo here<br><code class="d-block mt-1"><?= $pPolo['image'] ?></code></span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="card-body p-0 mt-3 d-flex justify-content-between align-items-start small font-bold tracking-wider uppercase">
                    <div>
                        <h3 class="mb-0 text-dark" style="font-size: 0.8rem;"><a href="product.php?id=<?= $pPolo['id'] ?>" class="text-dark hover:underline"><?= htmlspecialchars($pPolo['name']) ?></a></h3>
                        <p class="text-muted small mt-1 font-semibold"><?= htmlspecialchars($pPolo['category']) ?></p>
                    </div>
                    <p class="text-dark mb-0">$<?= number_format($pPolo['price'], 2) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Shirt Column -->
        <?php $pShirt = reset($shirts); if ($pShirt): ?>
        <div class="col-md-4">
            <div class="card border-0 rounded-0 bg-transparent space-y-3">
                <div class="position-relative overflow-hidden bg-light ratio ratio-3x4 border border-light">
                    <!-- Image click goes to product.php -->
                    <a href="product.php?id=<?= $pShirt['id'] ?>">
                        <?php if (file_exists(__DIR__ . '/' . $pShirt['image'])): ?>
                            <img src="<?= $pShirt['image'] ?>" class="w-100 h-100 object-fit-cover card-img-top rounded-0" style="object-fit: cover;" alt="<?= htmlspecialchars($pShirt['name']) ?>">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 img-placeholder text-secondary text-center">
                                <span class="small font-bold text-uppercase">Place photo here<br><code class="d-block mt-1"><?= $pShirt['image'] ?></code></span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="card-body p-0 mt-3 d-flex justify-content-between align-items-start small font-bold tracking-wider uppercase">
                    <div>
                        <h3 class="mb-0 text-dark" style="font-size: 0.8rem;"><a href="product.php?id=<?= $pShirt['id'] ?>" class="text-dark hover:underline"><?= htmlspecialchars($pShirt['name']) ?></a></h3>
                        <p class="text-muted small mt-1 font-semibold"><?= htmlspecialchars($pShirt['category']) ?></p>
                    </div>
                    <p class="text-dark mb-0">$<?= number_format($pShirt['price'], 2) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Denim Column -->
        <?php $pDenim = reset($denim); if ($pDenim): ?>
        <div class="col-md-4">
            <div class="card border-0 rounded-0 bg-transparent space-y-3">
                <div class="position-relative overflow-hidden bg-light ratio ratio-3x4 border border-light">
                    <!-- Image click goes to product.php -->
                    <a href="product.php?id=<?= $pDenim['id'] ?>">
                        <?php if (file_exists(__DIR__ . '/' . $pDenim['image'])): ?>
                            <img src="<?= $pDenim['image'] ?>" class="w-100 h-100 object-fit-cover card-img-top rounded-0" style="object-fit: cover;" alt="<?= htmlspecialchars($pDenim['name']) ?>">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 img-placeholder text-secondary text-center">
                                <span class="small font-bold text-uppercase">Place photo here<br><code class="d-block mt-1"><?= $pDenim['image'] ?></code></span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="card-body p-0 mt-3 d-flex justify-content-between align-items-start small font-bold tracking-wider uppercase">
                    <div>
                        <h3 class="mb-0 text-dark" style="font-size: 0.8rem;"><a href="product.php?id=<?= $pDenim['id'] ?>" class="text-dark hover:underline"><?= htmlspecialchars($pDenim['name']) ?></a></h3>
                        <p class="text-muted small mt-1 font-semibold"><?= htmlspecialchars($pDenim['category']) ?></p>
                    </div>
                    <p class="text-dark mb-0">$<?= number_format($pDenim['price'], 2) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Denim Capsule Section -->
<section id="denim" class="bg-light py-5 border-top border-bottom border-light my-5">
    <div class="container max-w-7xl mx-auto px-4 px-md-5 py-4">
        <div class="mb-5" style="max-width: 500px;">
            <h2 class="serif-title text-uppercase font-light mb-3" style="font-size: 2.2rem; letter-spacing: 0.1em;">DENIM</h2>
            <p class="small text-secondary font-medium tracking-wide leading-relaxed mb-4">
                Japanese rigid denim cut in structured, modern silhouettes. Designed to withstand seasons and fade individually, creating a tailored second skin tailored unique to your posture. Explore straight, wide-leg, and outerwear staples.
            </p>
            <a href="#must-haves" class="small font-bold text-uppercase text-dark tracking-widest text-decoration-none border-bottom border-dark pb-1 hover:text-secondary hover:border-secondary transition-colors">
                SHOP THE EDIT →
            </a>
        </div>

        <!-- Horizontal Outfit grid lists -->
        <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-3">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col">
                    <div class="ratio ratio-3x5 bg-light border border-secondary border-opacity-10 rounded-sm position-relative img-placeholder overflow-hidden d-flex flex-column justify-content-end p-3">
                        <!-- If looks files dropped -->
                        <?php if (file_exists(__DIR__ . "/public/images/denim_outfit_{$i}.jpg")): ?>
                            <img src="public/images/denim_outfit_<?= $i ?>.jpg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" style="object-fit: cover;" alt="Denim look">
                        <?php endif; ?>
                        <span class="position-relative z-1 mx-auto small font-bold tracking-widest text-uppercase text-muted bg-white bg-opacity-75 px-2 py-1 rounded shadow-sm text-center" style="font-size: 0.6rem; width: max-content;">
                            Denim Look <?= $i ?>
                        </span>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Must Haves Section (Interactive Tab Grid) -->
<section id="must-haves" x-data="{ category: 'all' }" class="container max-w-7xl mx-auto py-5 px-4 px-md-5 my-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-4 border-bottom border-light pb-4 mb-5">
        <div>
            <h2 class="serif-title text-uppercase font-light text-dark mb-1" style="font-size: 2.2rem; letter-spacing: 0.1em;">MUST HAVES</h2>
            <p class="small text-muted font-bold tracking-wider mb-0 text-uppercase">CURATED PIECES FOR AN ELEVATED CAPSULE WARDROBE</p>
        </div>
        
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-4 small font-bold tracking-widest text-uppercase">
            <button 
                @click="category = 'all'" 
                :class="category === 'all' ? 'text-dark border-bottom border-dark' : 'text-muted border-bottom border-transparent hover:text-dark'"
                class="btn rounded-0 p-0 pb-2 border-0 transition-all font-bold"
            >
                ALL
            </button>
            <button 
                @click="category = 'Polos'" 
                :class="category === 'Polos' ? 'text-dark border-bottom border-dark' : 'text-muted border-bottom border-transparent hover:text-dark'"
                class="btn rounded-0 p-0 pb-2 border-0 transition-all font-bold"
            >
                POLOS
            </button>
            <button 
                @click="category = 'Shirts'" 
                :class="category === 'Shirts' ? 'text-dark border-bottom border-dark' : 'text-muted border-bottom border-transparent hover:text-dark'"
                class="btn rounded-0 p-0 pb-2 border-0 transition-all font-bold"
            >
                SHIRTS
            </button>
            <button 
                @click="category = 'Denim'" 
                :class="category === 'Denim' ? 'text-dark border-bottom border-dark' : 'text-muted border-bottom border-transparent hover:text-dark'"
                class="btn rounded-0 p-0 pb-2 border-0 transition-all font-bold"
            >
                DENIM
            </button>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row row-cols-2 row-cols-md-4 g-4 g-lg-5">
        <?php foreach ($products as $p): ?>
            <div 
                x-show="category === 'all' || category === '<?= htmlspecialchars($p['category']) ?>'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-3"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="col"
            >
                <div class="card h-100 border-0 rounded-0 bg-transparent space-y-3 text-start">
                    <!-- Image Card Container -->
                    <div class="position-relative overflow-hidden bg-light ratio ratio-3x4 border border-light">
                        <!-- Clicking product pic takes them to product.php details page -->
                        <a href="product.php?id=<?= $p['id'] ?>">
                            <?php if (file_exists(__DIR__ . '/' . $p['image'])): ?>
                                <img src="<?= $p['image'] ?>" class="w-100 h-100 object-fit-cover card-img-top rounded-0" style="object-fit: cover;" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php else: ?>
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 img-placeholder text-secondary text-center">
                                    <span class="small font-bold text-uppercase">No Photo<br><code class="d-block text-[9px] mt-1"><?= $p['image'] ?></code></span>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-0 mt-3 d-flex flex-column gap-1 small text-dark">
                        <div class="d-flex justify-content-between align-items-start font-bold tracking-wide uppercase">
                            <h3 class="mb-0 text-dark text-truncate pe-2" style="font-size: 0.75rem;"><a href="product.php?id=<?= $p['id'] ?>" class="text-dark hover:underline"><?= htmlspecialchars($p['name']) ?></a></h3>
                            <p class="mb-0 flex-shrink-0 font-semibold">$<?= number_format($p['price'], 2) ?></p>
                        </div>
                        <p class="text-muted small font-bold tracking-widest text-uppercase mb-1" style="font-size: 0.6rem;"><?= htmlspecialchars($p['category']) ?></p>
                        
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <!-- Swatches -->
                            <div class="d-flex gap-1.5">
                                <?php foreach ($p['colors'] as $color): ?>
                                    <span class="rounded-circle border border-gray-300 shadow-sm" style="background-color: <?= $color ?>; width: 10px; height: 10px;"></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Stock Status -->
                            <?php if ($p['stock'] <= 0): ?>
                                <span class="small font-bold tracking-widest text-danger text-uppercase" style="font-size: 0.55rem;">OUT OF STOCK</span>
                            <?php elseif ($p['stock'] < 5): ?>
                                <span class="small font-bold tracking-widest text-warning text-uppercase" style="font-size: 0.55rem;">ONLY <?= $p['stock'] ?> LEFT</span>
                            <?php else: ?>
                                <span class="small font-bold tracking-widest text-muted text-uppercase" style="font-size: 0.55rem;">IN STOCK</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Community campaign banners -->
<section class="container max-w-7xl mx-auto py-4 px-4 px-md-5 mb-5 border-top border-light">
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="ratio ratio-4x3 bg-light border border-light rounded-sm img-placeholder text-secondary flex align-items-center justify-content-center">
                <?php if (file_exists(__DIR__ . '/public/images/campaign_1.jpg')): ?>
                    <img src="public/images/campaign_1.jpg" class="w-100 h-100 object-fit-cover" style="object-fit: cover;" alt="Campaign 1">
                <?php else: ?>
                    <span class="small font-bold tracking-widest text-uppercase">Campaign Photo 1</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ratio ratio-4x3 bg-light border border-light rounded-sm img-placeholder text-secondary flex align-items-center justify-content-center">
                <?php if (file_exists(__DIR__ . '/public/images/campaign_2.jpg')): ?>
                    <img src="public/images/campaign_2.jpg" class="w-100 h-100 object-fit-cover" style="object-fit: cover;" alt="Campaign 2">
                <?php else: ?>
                    <span class="small font-bold tracking-widest text-uppercase">Campaign Photo 2</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
