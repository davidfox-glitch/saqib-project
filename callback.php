<?php
declare(strict_types=1);

/**
 * callback.php – Google OAuth 2.0 response handler (SQLite version)
 *
 *  • Validates the CSRF state token stored in the session.
 *  • Exchanges the authorization code for an access token (cURL).
 *  • Retrieves the verified Google profile (email, name, id).
 *  • Uses a PDO‑SQLite connection to look‑up / insert the user.
 *  • Stores login data in the session and redirects to index.php.
 *
 * All code is inside a single try/catch.  Any exception is logged
 * (Railway will capture it) and a user‑friendly message is shown.
 */

session_start();

try {
    /* -----------------------------------------------------------------
     * 1️⃣  Configuration – env vars + redirect URI
     * ----------------------------------------------------------------- */
    $clientId     = getenv('GOOGLE_CLIENT_ID');
    $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
    $redirectUri  = 'https://saqib-project-production.up.railway.app/callback.php';

    if ($clientId === false || $clientSecret === false) {
        throw new RuntimeException('Google client credentials are not set in the environment.');
    }

    /* -----------------------------------------------------------------
     * 2️⃣  Basic validation – code & state
     * ----------------------------------------------------------------- */
    if (isset($_GET['error'])) {
        // User denied consent or another OAuth error occurred
        throw new RuntimeException(
            'Google OAuth error: ' . htmlspecialchars($_GET['error']) .
            (isset($_GET['error_description']) ? ' – ' . htmlspecialchars($_GET['error_description']) : '')
        );
    }

    if (!isset($_GET['code'])) {
        throw new RuntimeException('No authorization code received from Google.');
    }

    if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? '')) {
        throw new RuntimeException('Invalid state parameter – possible CSRF attack.');
    }

    $authCode = $_GET['code'];

    /* -----------------------------------------------------------------
     * 3️⃣  Exchange the code for an access token (cURL POST)
     * ----------------------------------------------------------------- */
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postFields = http_build_query([
        'code'          => $authCode,
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $tokenResponse = curl_exec($ch);
    if ($tokenResponse === false) {
        throw new RuntimeException('cURL error while fetching access token: ' . curl_error($ch));
    }
    curl_close($ch);

    $tokenData = json_decode($tokenResponse, true);
    if (empty($tokenData['access_token'])) {
        throw new RuntimeException('Failed to obtain access token. Response: ' . $tokenResponse);
    }
    $accessToken = $tokenData['access_token'];

    /* -----------------------------------------------------------------
     * 4️⃣  Fetch verified user profile from Google
     * ----------------------------------------------------------------- */
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $ch = curl_init($userInfoUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $userResponse = curl_exec($ch);
    if ($userResponse === false) {
        throw new RuntimeException('cURL error while fetching user info: ' . curl_error($ch));
    }
    curl_close($ch);

    $user = json_decode($userResponse, true);
    if (empty($user['email']) || empty($user['id'])) {
        throw new RuntimeException('Invalid user data returned from Google.');
    }

    /* -----------------------------------------------------------------
     * 5️⃣  SQLite PDO connection + up‑sert logic
     * ----------------------------------------------------------------- */
    // Path to your SQLite file – adjust if you keep it elsewhere.
    $sqlitePath = __DIR__ . '/database.sqlite';

    // Ensure the file exists so PDO can open it (Railway allows write in repo root).
    if (!file_exists($sqlitePath)) {
        touch($sqlitePath);
    }

    $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Create the `users` table if this is the first run.
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL,
            email       TEXT NOT NULL UNIQUE,
            google_id   TEXT NOT NULL,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        );'
    );

    // Look for an existing user with the same email.
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $user['email']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Existing user – log them in.
        $userId = (int)$existing['id'];
    } else {
        // New user – insert a row.
        $insert = $pdo->prepare(
            'INSERT INTO users (name, email, google_id) VALUES (:name, :email, :gid)'
        );
        $insert->execute([
            ':name'  => $user['name'] ?? $user['given_name'] ?? '',
            ':email' => $user['email'],
            ':gid'   => $user['id'],
        ]);
        $userId = (int)$pdo->lastInsertId();
    }

    /* -----------------------------------------------------------------
     * 6️⃣  Session handling – store login state
     * ----------------------------------------------------------------- */
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id']         = $userId;
    $_SESSION['user_name']       = $user['name'] ?? '';
    $_SESSION['user_email']      = $user['email'];

    /* -----------------------------------------------------------------
     * 7️⃣  Final redirect
     * ----------------------------------------------------------------- */
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    // Log the precise error to Railway’s logs (stderr) for debugging.
    error_log('Google OAuth callback error: ' . $e->getMessage());

    // Show a simple, user‑friendly message – no stack trace.
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Login error</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f8f9fa;color:#333;padding:2rem;}</style>';
    echo '</head><body>';
    echo '<h1>Something went wrong</h1>';
    echo '<p>We were unable to complete your Google sign‑in. Please try again later or contact support.</p>';
    echo '</body></html>';
    exit;
}
?>
