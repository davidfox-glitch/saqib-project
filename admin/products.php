<?php
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$error = '';
$success = '';

// Handle Delete Action
if ($action === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (JsonDB::deleteProduct($id)) {
        $success = "Product deleted successfully.";
    } else {
        $error = "Failed to delete product.";
    }
}

// Handle Single Image Deletion
if ($action === 'edit' && isset($_GET['delete_image'])) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $deleteIdx = intval($_GET['delete_image']);
    $product = JsonDB::findProductById($id);
    
    if ($product && isset($product['images']) && isset($product['images'][$deleteIdx])) {
        // Optional: Remove physical file if you don't want it kept
        // $fileToDelete = __DIR__ . '/../' . $product['images'][$deleteIdx];
        // if (file_exists($fileToDelete)) { unlink($fileToDelete); }
        
        array_splice($product['images'], $deleteIdx, 1);
        $product['image'] = !empty($product['images']) ? $product['images'][0] : '';
        
        JsonDB::updateProduct($id, ['images' => $product['images'], 'image' => $product['image']]);
        $success = "Image removed.";
        
        // Prevent fallthrough if we only meant to delete the image
        header("Location: products.php?action=edit&id=" . $id);
        exit;
    }
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $sizes_input = trim($_POST['sizes'] ?? '');
    $colors_input = trim($_POST['colors'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);

    // Initialize image handling
    $imagePaths = [];
    $allowedExt = ['jpg','jpeg','png','gif'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    // Process newly uploaded files (multiple)
    if (!empty($_FILES['image_files']['name'][0])) {
        foreach ($_FILES['image_files']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['image_files']['error'][$idx] !== UPLOAD_ERR_OK) { continue; }
            $originalName = basename($_FILES['image_files']['name'][$idx]);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $error = "Invalid file type for $originalName.";
                continue;
            }
            if ($_FILES['image_files']['size'][$idx] > $maxSize) {
                $error = "File $originalName exceeds the 2 MB limit.";
                continue;
            }
            $safeName = uniqid('img_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../public/images/';
            if (move_uploaded_file($tmpName, $uploadDir . $safeName)) {
                $imagePaths[] = 'public/images/' . $safeName;
            } else {
                $error = "Failed to move uploaded file $originalName.";
            }
        }
    }

    // Process gallery selection (checkboxes)
    if (!empty($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
        foreach ($_POST['gallery_images'] as $gImg) {
            $gImg = trim($gImg);
            if ($gImg !== '' && file_exists(__DIR__ . '/../' . $gImg)) {
                $imagePaths[] = $gImg;
            }
        }
    }

    // If editing and no new images provided, retain existing images
    if ($action === 'edit' && empty($imagePaths)) {
        $existingProduct = JsonDB::findProductById($id);
        if ($existingProduct && !empty($existingProduct['images'])) {
            $imagePaths = $existingProduct['images'];
        }
    }

    // Primary image is first in the array (or empty)
    $primaryImage = $imagePaths[0] ?? '';



    // Basic Validation
    if (empty($name) || $price <= 0 || empty($category)) {
        $error = "Name, price, and category are required.";
    } else {
        // Parse sizes and colors
        $sizes = array_filter(array_map('trim', explode(',', $sizes_input)));
        $colors = array_filter(array_map('trim', explode(',', $colors_input)));

        $data = [
            'name' => $name,
            'price' => $price,
            'category' => $category,
            'description' => $description,
            'image' => $imagePaths[0] ?? '', // primary image (first)
            'images' => $imagePaths,
            'sizes' => $sizes,
            'colors' => $colors,
            'stock' => $stock
        ];

        // If editing and no new image provided, retain existing image
        if ($action === 'edit' && empty($image)) {
            $existingProduct = JsonDB::findProductById($id);
            if ($existingProduct) {
                $data['image'] = $existingProduct['image'];
            }
        }

        if ($action === 'edit') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (JsonDB::updateProduct($id, $data)) {
                $success = "Product updated successfully.";
                $action = ''; // Redirect back to list
            } else {
                $error = "Failed to update product.";
            }
        } elseif ($action === 'add') {
            if (JsonDB::createProduct($data)) {
                $success = "Product added successfully.";
                $action = ''; // Redirect back to list
            } else {
                $error = "Failed to add product.";
            }
        }
    }
}

// Fetch single product if editing
$editProduct = null;
if ($action === 'edit') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $editProduct = JsonDB::findProductById($id);
    if (!$editProduct) {
        $error = "Product not found.";
        $action = '';
    }
}

// Fetch all products for the list
$products = JsonDB::getProducts();
?>

<div class="mb-5">

    <!-- Top Header Navigation -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 pb-4 mb-4 border-bottom">
        <div>
            <h1 class="serif-title mb-1" style="font-size: 1.8rem; font-weight: 300; letter-spacing: 0.05em; color: #1a1a1a;">Products Catalog</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.05em;">Manage storefront items, quantities, and pricing.</p>
        </div>
        
        <?php if (empty($action)): ?>
            <a href="products.php?action=add" class="btn btn-admin-accent px-4 py-2 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em; border-radius: 2px;">ADD NEW PRODUCT</a>
        <?php else: ?>
            <a href="products.php" class="btn btn-outline-secondary px-4 py-2 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em; border-radius: 2px;">CANCEL & VIEW CATALOG</a>
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

    <!-- ADD / EDIT FORM VIEW -->
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="bg-white border rounded shadow-sm p-4 p-md-5 mx-auto" style="max-width: 680px;">
            <h3 class="serif-title border-bottom pb-3 mb-4" style="font-size: 1.2rem; font-weight: 300; letter-spacing: 0.05em;">
                <?= $action === 'edit' ? 'Edit Product: #' . $editProduct['id'] : 'Create New Catalog Item' ?>
            </h3>

            <form action="products.php?action=<?= $action ?><?= $action === 'edit' ? '&id=' . $editProduct['id'] : '' ?>" method="POST" enctype="multipart/form-data">
                
                <div class="row g-3 mb-3">
                    <!-- Product Title -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Product Name</label>
                        <input 
                            type="text" name="name" required 
                            class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            placeholder="e.g. Artisanal Polo Knit"
                            value="<?= $action === 'edit' ? htmlspecialchars($editProduct['name']) : '' ?>"
                        >
                    </div>
                    <!-- Price -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Price ($ USD)</label>
                        <input 
                            type="number" step="0.01" name="price" required 
                            class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            placeholder="89.00"
                            value="<?= $action === 'edit' ? $editProduct['price'] : '' ?>"
                        >
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Category Select -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Category</label>
                        <select name="category" required class="form-select rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;">
                            <option value="Polos" <?= ($action === 'edit' && $editProduct['category'] === 'Polos') ? 'selected' : '' ?>>Polos</option>
                            <option value="Shirts" <?= ($action === 'edit' && $editProduct['category'] === 'Shirts') ? 'selected' : '' ?>>Shirts</option>
                            <option value="Denim" <?= ($action === 'edit' && $editProduct['category'] === 'Denim') ? 'selected' : '' ?>>Denim</option>
                        </select>
                    </div>
                    <!-- Stock Count -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Inventory Stock</label>
                        <input 
                            type="number" name="stock" required 
                            class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            placeholder="15"
                            value="<?= $action === 'edit' ? $editProduct['stock'] : '10' ?>"
                        >
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Description</label>
                    <textarea 
                        name="description" rows="4" 
                        class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                        placeholder="Describe the fabric weave, fit cuts, styling recommendations..."
                    ><?= $action === 'edit' ? htmlspecialchars($editProduct['description']) : '' ?></textarea>
                </div>

                <!-- Image Path -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Product Images</label>
                    <!-- Current images with delete option (edit mode) -->
                    <?php if ($action === 'edit' && !empty($editProduct['images'])): ?>
                        <div class="mb-2 d-flex flex-wrap gap-2">
                            <?php foreach ($editProduct['images'] as $idx => $img): ?>
                                <div class="position-relative" style="width:80px; height:80px;">
                                    <img src="../<?= htmlspecialchars($img) ?>" alt="Image" style="width:100%; height:100%; object-fit:cover;" />
                                    <a href="products.php?action=edit&id=<?= $editProduct['id'] ?>&delete_image=<?= $idx ?>" class="position-absolute top-0 end-0 btn btn-sm btn-danger" style="padding:0 4px; line-height:1;" title="Delete">×</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Upload new images -->
                    <input type="file" name="image_files[]" multiple accept="image/*" class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;" />
                    <p class="form-text text-muted mt-1" style="font-size: 0.65rem;">Upload new images or select from the gallery below.</p>
                    <!-- Gallery selection (checkboxes) -->
                    <div class="gallery mt-2" style="display:flex; flex-wrap:wrap; gap:8px;">
                        <?php
                        $galleryPath = __DIR__ . '/../public/images/';
                        $files = array_diff(scandir($galleryPath), ['.', '..']);
                        foreach ($files as $file) {
                            if (preg_match('/\.(jpe?g|png|gif)$/i', $file)) {
                                $fileUrl = '../public/images/' . $file;
                                echo "<label style='cursor:pointer;'>";
                                echo "<input type='checkbox' name='gallery_images[]' value='public/images/{$file}' style='display:none;'" . (in_array('public/images/'.$file, $editProduct['images'] ?? []) ? ' checked' : '') . " />";
                                echo "<img src='{$fileUrl}' alt='{$file}' style='max-height:60px; border:1px solid #ccc; padding:2px;' />";
                                echo "</label>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Sizes -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Sizes (Comma-Separated)</label>
                        <input 
                            type="text" name="sizes" 
                            class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            placeholder="S, M, L, XL"
                            value="<?= $action === 'edit' ? implode(',', $editProduct['sizes']) : 'S,M,L,XL' ?>"
                        >
                    </div>
                    <!-- Colors -->
                    <div class="col-sm-6">
                        <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Colors (Comma-Separated HEX)</label>
                        <input 
                            type="text" name="colors" 
                            class="form-control rounded-0 border-secondary-subtle py-2" style="font-size: 0.8rem;"
                            placeholder="#8F9779, #1A1A1A, #EFEFEF"
                            value="<?= $action === 'edit' ? implode(',', $editProduct['colors']) : '#8F9779,#D2C9B1,#EFEFEF' ?>"
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-dark w-100 py-3">
                    <?= $action === 'edit' ? 'SAVE CHANGES' : 'CREATE PRODUCT' ?>
                </button>
            </form>
        </div>

    <!-- LIST CATALOG VIEW -->
    <?php else: ?>
        <div class="bg-white border rounded shadow-sm p-4 p-md-5">
            <?php if (empty($products)): ?>
                <div class="text-center py-5 text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                    Your product catalog is empty.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.1em;">
                                <th class="border-bottom pb-3">Image</th>
                                <th class="border-bottom pb-3">Item Name</th>
                                <th class="border-bottom pb-3">Category</th>
                                <th class="border-bottom pb-3">Price</th>
                                <th class="border-bottom pb-3 text-center">Stock</th>
                                <th class="border-bottom pb-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <!-- Image Preview Thumbnail -->
                                    <td>
                                        <div class="border overflow-hidden d-flex align-items-center justify-content-center rounded bg-light flex-shrink-0" style="width: 40px; height: 48px;">
                                            <?php if (file_exists(__DIR__ . '/../' . $p['image'])): ?>
                                                <img src="../<?= $p['image'] ?>" class="w-100 h-100" style="object-fit: cover;" alt="Thumb">
                                            <?php else: ?>
                                                <div class="w-100 h-100 img-placeholder-admin"></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Name -->
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></span>
                                        <div class="d-flex align-items-center gap-1 mt-1 text-muted text-uppercase" style="font-size: 0.55rem; font-weight: 500;">
                                            <span>Sizes: <?= implode(', ', $p['sizes']) ?></span>
                                            <span>•</span>
                                            <div class="d-flex gap-1">
                                                <?php foreach ($p['colors'] as $col): ?>
                                                    <span class="rounded-circle border" style="background-color: <?= $col ?>; width: 8px; height: 8px; display: inline-block;"></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Category -->
                                    <td class="text-secondary"><?= htmlspecialchars($p['category']) ?></td>
                                    
                                    <!-- Price -->
                                    <td class="text-dark fw-semibold">$<?= number_format($p['price'], 2) ?></td>
                                    
                                    <!-- Stock Status -->
                                    <td class="text-center">
                                        <?php if ($p['stock'] <= 0): ?>
                                            <span class="badge stock-out fw-bold text-uppercase px-2 py-1" style="font-size: 0.5rem;">SOLD OUT</span>
                                        <?php elseif ($p['stock'] <= 5): ?>
                                            <span class="badge stock-low fw-bold text-uppercase px-2 py-1" style="font-size: 0.5rem;">LOW (<?= $p['stock'] ?>)</span>
                                        <?php else: ?>
                                            <span class="badge stock-ok fw-bold text-uppercase px-2 py-1" style="font-size: 0.5rem;">OK (<?= $p['stock'] ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Controls -->
                                    <td class="text-end">
                                        <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="fw-bold text-uppercase text-decoration-none text-primary me-2" style="font-size: 0.6rem; letter-spacing: 0.08em;">Edit</a>
                                        <a 
                                            href="products.php?action=delete&id=<?= $p['id'] ?>" 
                                            onclick="return confirm('Are you sure you want to delete this product?')" 
                                            class="fw-bold text-uppercase text-decoration-none text-danger"
                                            style="font-size: 0.6rem; letter-spacing: 0.08em;"
                                        >Delete</a>
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
