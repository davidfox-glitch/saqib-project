<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
Auth::requireAdmin();

$heroDataPath = __DIR__ . '/../data/hero.json';
$success = '';
$error = '';

// Load current hero images
$currentHero = [];
if (file_exists($heroDataPath)) {
    $currentHero = json_decode(file_get_contents($heroDataPath), true) ?? [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedImages = $_POST['hero_images'] ?? [];
    
    // Process Uploads
    if (!empty($_FILES['new_hero_images']['name'][0])) {
        $uploadDir = __DIR__ . '/../public/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['new_hero_images']['tmp_name'] as $idx => $tmpName) {
            if ($tmpName) {
                $originalName = $_FILES['new_hero_images']['name'][$idx];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'])) {
                    $safeName = uniqid('hero_') . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $safeName)) {
                        // Automatically select the newly uploaded image
                        $selectedImages[] = 'public/images/' . $safeName;
                    }
                }
            }
        }
    }

    // Save to JSON
    if (file_put_contents($heroDataPath, json_encode(array_values(array_unique($selectedImages)), JSON_PRETTY_PRINT))) {
        $success = "Hero images updated successfully!";
        $currentHero = $selectedImages;
    } else {
        $error = "Failed to save hero images configuration.";
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 serif-title">Hero Section Manager</h1>
        <p class="text-muted small">Select the images to display in the homepage slider</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success py-2 rounded-0 small fw-bold tracking-wider"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2 rounded-0 small fw-bold tracking-wider"><?= $error ?></div>
<?php endif; ?>

<div class="bg-white border rounded shadow-sm p-4 p-md-5">
    <form action="hero.php" method="POST" enctype="multipart/form-data">
        
        <div class="mb-5">
            <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.12em;">Upload New Hero Images</label>
            <input type="file" name="new_hero_images[]" multiple accept="image/*" class="form-control rounded-0 border-secondary-subtle py-2">
            <div class="form-text mt-2 small text-muted">Upload high-resolution landscape images (e.g., 1920x1080) for the best results.</div>
        </div>

        <h3 class="h5 mb-3 fw-bold text-uppercase text-dark" style="font-size: 0.8rem; letter-spacing: 0.1em;">Select Slider Images</h3>
        
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-5">
            <style>
                .hero-img-label input[type="checkbox"]:checked + .img-container {
                    border-color: #3B82F6 !important;
                }
                .hero-img-label input[type="checkbox"]:checked + .img-container .check-badge {
                    display: flex !important;
                }
            </style>
            <?php
            // Get all images in public/images
            $galleryPath = __DIR__ . '/../public/images/';
            $files = [];
            if (is_dir($galleryPath)) {
                $files = array_diff(scandir($galleryPath), ['.', '..']);
            }
            
            // Sort to put selected ones first, roughly
            $selectedFiles = [];
            $unselectedFiles = [];
            foreach ($files as $file) {
                if (preg_match('/\.(jpe?g|png|gif|avif|webp)$/i', $file)) {
                    if (in_array('public/images/'.$file, $currentHero)) {
                        $selectedFiles[] = $file;
                    } else {
                        $unselectedFiles[] = $file;
                    }
                }
            }
            $allFiles = array_merge($selectedFiles, $unselectedFiles);

            foreach ($allFiles as $file):
                $fileRelPath = 'public/images/' . $file;
                $isChecked = in_array($fileRelPath, $currentHero);
            ?>
                <div class="col">
                    <label class="hero-img-label d-block w-100" style="cursor: pointer;">
                        <input type="checkbox" name="hero_images[]" value="<?= $fileRelPath ?>" style="display:none;" <?= $isChecked ? 'checked' : '' ?>>
                        <div class="img-container position-relative ratio ratio-16x9 border" style="border-width: 3px !important; border-color: transparent !important; transition: border-color 0.2s;">
                            <img src="../<?= $fileRelPath ?>" alt="<?= $file ?>" class="w-100 h-100 object-fit-cover">
                            
                            <!-- Check badge -->
                            <div class="check-badge position-absolute top-0 end-0 m-2 bg-primary text-white rounded-circle align-items-center justify-content-center" style="width: 24px; height: 24px; display: none !important;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-admin-dark px-5 py-3">SAVE HERO SLIDER</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
