<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// Only admins can access this page
Auth::requireAdmin();

// Initialize SQLite connection (same path as callback)
$sqlitePath = __DIR__ . '/../database.sqlite';
if (!file_exists($sqlitePath)) {
    // If the DB does not exist, create an empty file (unlikely as users already exist)
    touch($sqlitePath);
}
$pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Ensure the users table has a role column (in case of older DB)
try {
    $pdo->exec('ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT "user"');
} catch (Throwable $e) {
    // Column likely already exists – ignore
}

// Handle promotion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_id'])) {
    $promoteId = (int)$_POST['promote_id'];
    // Update role to admin
    $stmt = $pdo->prepare('UPDATE users SET role = "admin" WHERE id = :id');
    $stmt->execute([':id' => $promoteId]);
    // Optionally set a flash message (simple implementation)
    $_SESSION['flash'] = 'User promoted to admin.';
    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch latest 5 users (by created_at, newest first)
$stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5');
$users = $stmt->fetchAll();

// Simple HTML output – you can style it with your existing CSS classes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Recent Users</title>
    <style>
        body {font-family: Arial, sans-serif; background:#f4f7f9; padding:2rem;}
        h1 {color:#333;}
        table {width:100%; border-collapse:collapse; margin-top:1rem;}
        th, td {padding:0.75rem; text-align:left; border-bottom:1px solid #ddd;}
        th {background:#e9ecef;}
        .btn {display:inline-block; padding:0.4rem 0.8rem; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; cursor:pointer; border:none;}
        .admin {font-weight:bold; color:#28a745;}
        .flash {background:#d4edda; color:#155724; padding:0.75rem; margin-bottom:1rem; border:1px solid #c3e6cb; border-radius:4px;}
    </style>
</head>
<body>
    <h1>Recent Users</h1>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo $u['role'] === 'admin' ? '<span class="admin">Admin</span>' : 'User'; ?></td>
                    <td><?php echo $u['created_at']; ?></td>
                    <td>
                        <?php if ($u['role'] !== 'admin'): ?>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="promote_id" value="<?php echo $u['id']; ?>" />
                                <button class="btn" type="submit">Make Admin</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
