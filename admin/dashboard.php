<?php
declare(strict_types=1);

require_once __DIR__ . '/header.php'; // Includes Auth::requireAdmin() and session check

// Initialize SQLite connection
$sqlitePath = __DIR__ . '/../database.sqlite';
if (!file_exists($sqlitePath)) {
    touch($sqlitePath);
}
$pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Ensure the users table has a role column
try {
    $pdo->exec('ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT "user"');
} catch (Throwable $e) {
    // Ignore if already exists
}

// Handle promotion request (promotes in BOTH databases)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_email'])) {
    $promoteEmail = trim($_POST['promote_email']);

    // 1. Promote in SQLite
    $stmt = $pdo->prepare('UPDATE users SET role = "admin" WHERE LOWER(email) = LOWER(:email)');
    $stmt->execute([':email' => $promoteEmail]);

    // 2. Promote in JSON
    $jsonUsers = JsonDB::getUsers();
    foreach ($jsonUsers as &$ju) {
        if (strtolower($ju['email']) === strtolower($promoteEmail)) {
            $ju['role'] = 'admin';
        }
    }
    unset($ju);
    // Write back via reflection (direct file write since JsonDB doesn't expose a bulk-save)
    $usersFilePath = __DIR__ . '/../data/users.json';
    file_put_contents($usersFilePath, json_encode($jsonUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $_SESSION['flash'] = 'User promoted to admin.';
    header('Location: /admin/dashboard.php');
    exit;
}

// ---------------------
// Merge users from both databases
// ---------------------
$jsonUsers = JsonDB::getUsers();

// Fetch SQLite users
$sqliteUserRows = [];
try {
    $stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
    $sqliteUserRows = $stmt->fetchAll();
} catch (Throwable $e) {
    // table might not exist yet
}

// Build merged array keyed by lowercase email
$merged = [];

// Add JSON users first
foreach ($jsonUsers as $ju) {
    $key = strtolower($ju['email']);
    $merged[$key] = [
        'name'       => $ju['username'] ?? $ju['name'] ?? 'Unknown',
        'email'      => $ju['email'],
        'role'       => $ju['role'] ?? 'user',
        'created_at' => $ju['created_at'] ?? '2026-05-21 12:00:00',
        'source'     => 'Email',
    ];
}

// Merge SQLite users
foreach ($sqliteUserRows as $su) {
    $key = strtolower($su['email']);
    if (isset($merged[$key])) {
        // User exists in both — mark as dual source, promote role if either is admin
        $merged[$key]['source'] = 'Email & Google';
        if ($su['role'] === 'admin') {
            $merged[$key]['role'] = 'admin';
        }
        // Use the earliest created_at
        if (!empty($su['created_at']) && $su['created_at'] < $merged[$key]['created_at']) {
            $merged[$key]['created_at'] = $su['created_at'];
        }
    } else {
        $merged[$key] = [
            'name'       => $su['name'] ?? 'Unknown',
            'email'      => $su['email'],
            'role'       => $su['role'] ?? 'user',
            'created_at' => $su['created_at'] ?? '2026-05-21 12:00:00',
            'source'     => 'Google',
        ];
    }
}

// Sort by created_at descending (newest first)
$users = array_values($merged);
usort($users, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>

<div class="mb-5">
    
    <!-- Top Header Navigation -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 pb-4 mb-4 border-bottom">
        <div>
            <h1 class="serif-title mb-1" style="font-size: 1.8rem; font-weight: 300; letter-spacing: 0.05em; color: #1a1a1a;">Registered Users</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.05em;">View registered customers, administrators, and manage permissions.</p>
        </div>
        <a href="/admin/index.php" class="btn btn-outline-secondary px-4 py-2 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em; border-radius: 2px;">Back to Dashboard</a>
    </div>

    <!-- Flash message -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-success py-2 px-3 fw-semibold mb-4" style="font-size: 0.75rem; border-left: 3px solid #198754;">
            <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <!-- Users Table Card -->
    <div class="bg-white border rounded shadow-sm p-4 p-md-5">
        <?php if (empty($users)): ?>
            <div class="text-center py-5 text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                No registered users found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.75rem;">
                    <thead>
                        <tr class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.1em;">
                            <th class="border-bottom pb-3">Name</th>
                            <th class="border-bottom pb-3">Email Address</th>
                            <th class="border-bottom pb-3">Source</th>
                            <th class="border-bottom pb-3">Role</th>
                            <th class="border-bottom pb-3">Joined Date</th>
                            <th class="border-bottom pb-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="text-dark fw-semibold"><?= htmlspecialchars($u['name']) ?></td>
                                <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['source'] === 'Google'): ?>
                                        <span class="badge bg-info-subtle text-info fw-bold text-uppercase px-3 py-1" style="font-size: 0.55rem; letter-spacing: 0.1em;">Google</span>
                                    <?php elseif ($u['source'] === 'Email & Google'): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1" style="font-size: 0.55rem; letter-spacing: 0.1em;">Email & Google</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning fw-bold text-uppercase px-3 py-1" style="font-size: 0.55rem; letter-spacing: 0.1em;">Email</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-success-subtle text-success fw-bold text-uppercase px-3 py-1" style="font-size: 0.55rem; letter-spacing: 0.1em;">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary fw-bold text-uppercase px-3 py-1" style="font-size: 0.55rem; letter-spacing: 0.1em;">User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('M d, Y H:i', strtotime($u['created_at'])) ?></td>
                                <td class="text-end">
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <form method="post" action="/admin/dashboard.php" style="margin:0; display:inline-block;" onsubmit="return confirm('Are you sure you want to make this user an Admin?');">
                                            <input type="hidden" name="promote_email" value="<?= htmlspecialchars($u['email']) ?>" />
                                            <button class="btn btn-admin-accent px-3 py-1 fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.08em; border-radius: 2px;" type="submit">Make Admin</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Full Access</span>
                                    <?php endif; ?>
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
