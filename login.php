<?php
/**
 * login.php – Google OAuth 2.0 entry point
 *
 * This page renders a standard login form (you can replace the placeholder
 * with your own markup) and a modern "Sign in with Google" button.
 * The button builds a secure authorization URL using the client ID fetched
 * from the environment via getenv().
 */

session_start();

// ---------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------
$clientId    = getenv('GOOGLE_CLIENT_ID'); // Must be set on the server
$redirectUri = 'https://saqib-project-production.up.railway.app/callback.php';
$scope       = 'openid email profile';

// Generate a random state token for CSRF protection and store it in the session
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;

// Build the Google authorization URL (RFC 6749)
$authUrl = sprintf(
    'https://accounts.google.com/o/oauth2/v2/auth?' .
    'response_type=code&' .
    'client_id=%s&' .
    'redirect_uri=%s&' .
    'scope=%s&' .
    'state=%s&' .
    'access_type=online&' .
    'prompt=select_account',
    urlencode($clientId),
    urlencode($redirectUri),
    urlencode($scope),
    $state
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Exaltia</title>
    <!-- You may already load Bootstrap or your own CSS elsewhere -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .google-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #444;
            border: 1px solid #dadce0;
            border-radius: 0.4rem;
            background-color: #fff;
            text-decoration: none;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .google-btn:hover {
            background-color: #f7f7f7;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background:#f4f4f4;">
    <div class="container" style="max-width: 460px;">
        <!-- Placeholder for your classic login form -->
        <div class="card p-4 mb-4">
            <h2 class="mb-3 text-center">Login</h2>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Log In</button>
            </form>
        </div>
        <!-- Google OAuth button -->
        <div class="text-center">
            <a href="<?= htmlspecialchars($authUrl) ?>" class="google-btn">
                <!-- Google "G" logo (SVG) -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 533.5 544.3" width="20" height="20">
                    <path fill="#4285F4" d="M533.5 278.4c0-18.5-1.6-36.2-4.5-53.4H272v100.9h146.9c-6.4 34.6-25.9 63.9-55 83.5v69h88.8c52-48 81.8-118.6 81.8-200z"/>
                    <path fill="#34A853" d="M272 544.3c73.5 0 135.2-24.4 180.2-66.2l-88.8-69c-24.5 16.4-55.9 26-91.4 26-70.4 0-130.1-47.5-151.6-111.4h-91v70.6c45 88.9 138.6 150 242.6 150z"/>
                    <path fill="#FBBC05" d="M120.4 323.7c-10.6-31.6-10.6-65.8 0-97.4v-70.6h-91c-38.5 75.8-38.5 165.6 0 241.4l91-73z"/>
                    <path fill="#EA4335" d="M272 107.5c39.9-.6 78.2 15.2 107.1 43.1l80.2-80.2C410.5 22 342.6-2.5 272 0 168 0 74.4 61.1 30.4 150l91 73c21.5-63.9 81.2-111.4 150.6-111.4z"/>
                </svg>
                Sign in with Google
            </a>
        </div>
    </div>
</body>
</html>
