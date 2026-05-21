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

// Handle Delete from library
if (isset($_GET['delete_img'])) {
    $imgToDelete = urldecode($_GET['delete_img']);
    $filePath = __DIR__ . '/../' . $imgToDelete;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    // Also remove from hero.json if it was selected
    $currentHero = array_values(array_filter($currentHero, function($h) use ($imgToDelete) {
        return $h !== $imgToDelete;
    }));
    file_put_contents($heroDataPath, json_encode($currentHero, JSON_PRETTY_PRINT));
    header("Location: hero.php?msg=deleted");
    exit;
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
                        $selectedImages[] = 'public/images/' . $safeName;
                    }
                }
            }
        }
    }

    // Save to JSON
    $uniqueImages = array_values(array_unique($selectedImages));
    if (file_put_contents($heroDataPath, json_encode($uniqueImages, JSON_PRETTY_PRINT))) {
        $success = "Hero images updated successfully!";
        $currentHero = $uniqueImages;
    } else {
        $error = "Failed to save hero images configuration.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success = "Image deleted from library.";
}

require_once __DIR__ . '/header.php';
?>

<style>
    .hero-card {
        position: relative;
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 6px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .hero-card.selected {
        border-color: #3B82F6;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.3);
    }
    .hero-card .check-icon {
        display: none;
        position: absolute;
        top: 6px; right: 6px;
        width: 26px; height: 26px;
        background: #3B82F6;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        z-index: 3;
    }
    .hero-card.selected .check-icon {
        display: flex;
    }
    .hero-card .delete-btn {
        position: absolute;
        bottom: 6px; right: 6px;
        width: 26px; height: 26px;
        background: #dc2626;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 4;
        opacity: 0;
        transition: opacity 0.2s;
        text-decoration: none;
    }
    .hero-card:hover .delete-btn {
        opacity: 1;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 serif-title">Hero Section Manager</h1>
        <p class="text-muted small">Select the images to display in the homepage slider. Click to select/deselect.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success py-2 rounded-0 small fw-bold"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2 rounded-0 small fw-bold"><?= $error ?></div>
<?php endif; ?>

<!-- Warning about small images -->
<div class="alert alert-warning py-2 rounded-0 small mb-4">
    <strong>Tip:</strong> For best results, upload images that are at least <strong>1920×800 pixels</strong>. Small or thumbnail-sized images will appear blurry when stretched across the full homepage banner.
</div>

<div class="bg-white border rounded shadow-sm p-4 p-md-5">
    <form action="hero.php" method="POST" enctype="multipart/form-data" id="heroForm">
        
        <div class="mb-5">
            <label class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.12em;">Upload New Hero Images</label>
            <input type="file" name="new_hero_images[]" multiple accept="image/*" class="form-control rounded-0 border-secondary-subtle py-2">
            <div class="form-text mt-2 small text-muted">Upload high-resolution landscape images (1920×800 or larger).</div>
        </div>

        <h3 class="h5 mb-3 fw-bold text-uppercase text-dark" style="font-size: 0.8rem; letter-spacing: 0.1em;">
            Image Library — Click to Select for Hero Slider
        </h3>
        
        <!-- Hidden inputs container for selected images -->
        <div id="selectedInputs"></div>
        
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-5">
            <?php
            $galleryPath = __DIR__ . '/../public/images/';
            $files = [];
            if (is_dir($galleryPath)) {
                $files = array_diff(scandir($galleryPath), ['.', '..']);
            }
            
            foreach ($files as $file):
                if (!preg_match('/\.(jpe?g|png|gif|avif|webp)$/i', $file)) continue;
                $fileRelPath = 'public/images/' . $file;
                $isChecked = in_array($fileRelPath, $currentHero);
                $fileSize = filesize($galleryPath . $file);
                $isSmall = $fileSize < 50000; // Less than 50KB = probably too small for hero
            ?>
                <div class="col">
                    <div class="hero-card <?= $isChecked ? 'selected' : '' ?>" 
                         data-path="<?= htmlspecialchars($fileRelPath) ?>"
                         onclick="toggleHeroCard(this)">
                        
                        <!-- Check icon -->
                        <div class="check-icon">
                            <svg style="width:14px;height:14px;" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        
                        <!-- Image -->
                        <div class="ratio ratio-16x9">
                            <img src="../<?= htmlspecialchars($fileRelPath) ?>" alt="<?= htmlspecialchars($file) ?>" style="object-fit: cover; width:100%; height:100%;">
                        </div>
                        
                        <!-- Delete button -->
                        <a href="hero.php?delete_img=<?= urlencode($fileRelPath) ?>" 
                           class="delete-btn" 
                           onclick="event.stopPropagation(); return confirm('Delete this image from library?');"
                           title="Delete image">
                            <svg style="width:12px;height:12px;" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    </div>
                    <!-- File info -->
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted" style="font-size:0.6rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%;"><?= htmlspecialchars($file) ?></span>
                        <span class="<?= $isSmall ? 'text-danger' : 'text-success' ?>" style="font-size:0.6rem; font-weight:600;">
                            <?= number_format($fileSize / 1024, 0) ?>KB
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-admin-dark px-5 py-3">SAVE HERO SLIDER</button>
    </form>
</div>

<script>
function toggleHeroCard(card) {
    card.classList.toggle('selected');
    updateHiddenInputs();
}

function updateHiddenInputs() {
    const container = document.getElementById('selectedInputs');
    container.innerHTML = '';
    document.querySelectorAll('.hero-card.selected').forEach(function(card) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'hero_images[]';
        input.value = card.dataset.path;
        container.appendChild(input);
    });
}

// Initialize on page load
updateHiddenInputs();
// Highlight cards that are already part of the hero slider
const currentHero = <?php echo json_encode($currentHero); ?>;
document.querySelectorAll('.hero-card').forEach(card => {
    if (currentHero.includes(card.dataset.path)) {
        card.classList.add('selected');
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
