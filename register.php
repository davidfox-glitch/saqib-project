<?php
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $result = Auth::register($username, $email, $password);
        if ($result === true) {
            header("Location: index.php");
            exit;
        } else {
            $error = $result; // Show string error message
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center px-3" style="min-height: 80vh;">
    <div class="w-100 bg-white border shadow-lg p-4 p-sm-5" style="max-width: 440px;">
        
        <div class="text-center mb-4">
            <h2 class="serif-title text-uppercase mb-2" style="font-size: 1.8rem; letter-spacing: 0.12em; font-weight: 300;">CREATE ACCOUNT</h2>
            <p class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.1em; font-size: 0.65rem;">JOIN EXALTIA TO TRACK YOUR BAG AND ORDERS</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center py-2 px-3 small fw-semibold" style="font-size: 0.75rem; border-left: 3px solid #dc3545;">
                <svg class="me-2 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Full Name</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="John Doe"
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                    style="font-size: 0.8rem;"
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="name@example.com"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    style="font-size: 0.8rem;"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="Min. 6 characters"
                    style="font-size: 0.8rem;"
                >
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="••••••••"
                    style="font-size: 0.8rem;"
                >
            </div>

            <button type="submit" class="btn btn-brand-dark w-100 py-3 mb-3">CREATE ACCOUNT</button>
        </form>

        <div class="text-center pt-2">
            <p class="small text-muted mb-0">
                Already have an account? 
                <a href="login.php" class="text-dark fw-bold text-decoration-underline">Log In</a>
            </p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
