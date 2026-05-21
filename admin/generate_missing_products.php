// Auto-generate product entries for images not yet in products.json
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
Auth::requireAdmin();

$productsPath = __DIR__ . '/data/products.json';
$products = [];
if (file_exists($productsPath)) {
    $products = json_decode(file_get_contents($productsPath), true) ?? [];
}

$existingImages = [];
foreach ($products as $p) {
    if (!empty($p['images'])) {
        foreach ($p['images'] as $img) {
            $existingImages[] = $img;
        }
    }
}
$existingImages = array_unique($existingImages);

$galleryPath = __DIR__ . '/public/images/';
$files = [];
if (is_dir($galleryPath)) {
    $files = array_diff(scandir($galleryPath), ['.', '..']);
}

$nextId = count($products) > 0 ? max(array_column($products, 'id')) + 1 : 1;
$newProducts = [];
foreach ($files as $file) {
    $relPath = 'public/images/' . $file;
    if (!in_array($relPath, $existingImages) && preg_match('/\.(jpe?g|png|gif|avif|webp)$/i', $file)) {
        // Generate a simple product entry
        $name = pathinfo($file, PATHINFO_FILENAME);
        $name = ucwords(str_replace(['-', '_'], ' ', $name));
        $newProducts[] = [
            'id' => $nextId++,
            'name' => $name,
            'price' => 49.99,
            'category' => 'Misc',
            'description' => 'Auto-generated product for image ' . $file,
            'image' => $relPath,
            'images' => [$relPath],
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['#EFEFEF'],
            'stock' => 10,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

if (!empty($newProducts)) {
    $allProducts = array_merge($products, $newProducts);
    file_put_contents($productsPath, json_encode($allProducts, JSON_PRETTY_PRINT));
    echo "Added " . count($newProducts) . " new product entries.";
} else {
    echo "No new images to add as products.";
}
?>
