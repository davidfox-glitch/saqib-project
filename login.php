<?php
require_once __DIR__ . '/includes/auth.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    Auth::logout();
    header("Location: index.php");
    exit;
}

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    if (Auth::isAdmin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if (Auth::login($email, $password)) {
            // Redirect to admin panel or shop
            if (Auth::isAdmin()) {
                header("Location: admin/index.php");
            } else {
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                header("Location: " . $redirect);
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center px-3" style="min-height: 80vh;">
    <div class="w-100 bg-white border shadow-lg p-4 p-sm-5" style="max-width: 440px;" 
         x-data="{ email: '', password: '' }">
        
        <div class="text-center mb-4">
            <h2 class="serif-title text-uppercase mb-2" style="font-size: 1.8rem; letter-spacing: 0.12em; font-weight: 300;">LOG IN</h2>
            <p class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.1em; font-size: 0.65rem;">ENTER YOUR CREDENTIALS TO ACCESS YOUR ACCOUNT</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center py-2 px-3 small fw-semibold" style="font-size: 0.75rem; border-left: 3px solid #dc3545;">
                <svg class="me-2 flex-shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="email" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    x-model="email"
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="name@example.com"
                    style="font-size: 0.8rem;"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.12em;">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    x-model="password"
                    class="form-control rounded-0 border-secondary-subtle py-2 small"
                    placeholder="••••••••"
                    style="font-size: 0.8rem;"
                >
            </div>

            <button type="submit" class="btn btn-brand-dark w-100 py-3">LOG IN</button>
        </form>



        <div class="text-center pt-2">
            <p class="small text-muted mb-0">
                Don't have an account? 
                <a href="register.php" class="text-dark fw-bold text-decoration-underline">Register</a>
            </p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
