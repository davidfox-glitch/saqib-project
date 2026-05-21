<?php
// login.php – existing login page with an extra Google OAuth button
session_start();                                   // start the session early
require_once __DIR__ . '/includes/header.php';     // your regular header (keeps CSS, layout, etc.)

/**
 * Build the Google authorization URL.
 *
 * We use the environment variables you already set:
 *   GOOGLE_CLIENT_ID
 *   GOOGLE_CLIENT_SECRET   (not needed here but kept for completeness)
 *
 * The redirect_uri must exactly match the one you registered in Google:
 *   https://saqib-project-production.up.railway.app/callback.php
 */
$clientId     = getenv('GOOGLE_CLIENT_ID');
$redirectUri  = 'https://saqib-project-production.up.railway.app/callback.php';
$scope        = rawurlencode('openid email profile');
$state        = bin2hex(random_bytes(16));          // CSRF protection
$_SESSION['oauth2state'] = $state;                 // store to validate later

$authUrl = sprintf(
    'https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id=%s&redirect_uri=%s&scope=%s&state=%s&access_type=online&prompt=select_account',
    $clientId,
    rawurlencode($redirectUri),
    $scope,
    $state
);
?>

<div class="d-flex align-items-center justify-content-center px-3" style="min-height:80vh;">
    <div class="w-100 bg-white border shadow-lg p-4 p-sm-5" style="max-width:440px;">
        <!-- Existing login form (unchanged) -->
        <?php
        // --------------------------------------------------------------------
        // INCLUDE YOUR ORIGINAL login form HTML here (the code you already have)
        // --------------------------------------------------------------------
        ?>

        <!-- -------------------------------------------------------------- -->
        <!--   Google Sign‑In button (modern, reachable, fully styled)       -->
        <!-- -------------------------------------------------------------- -->
        <div class="text-center mt-4">
            <a href="<?= htmlspecialchars($authUrl) ?>"
               class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center"
               style="gap:0.5rem; font-weight:600; font-size:0.9rem; padding:0.6rem 1rem; border-radius:0.4rem;">
                <!-- Google “G” logo (SVG) -->
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
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
