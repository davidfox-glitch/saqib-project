<?php
/**
 * callback.php – Google OAuth 2.0 response handler
 *
 * 1. Validates the "state" token stored in the session (CSRF protection).
 * 2. Exchanges the authorization code for an access token via a POST request
 *    to https://oauth2.googleapis.com/token using raw cURL.
 * 3. Retrieves the verified user profile (email, name, id) from Google.
 * 4. Looks up the e‑mail in a PDO‑based MySQL database. If the user does not
 *    exist, a new row is inserted. The user id is stored in the session.
 * 5. Finally redirects the user to index.php.
 *
 * No external Composer packages are required – everything uses native PHP.
 */

session_start();

// ---------------------------------------------------------------------
// 1️⃣  Configuration – environment variables and redirect URI
// ---------------------------------------------------------------------
$clientId     = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri  = 'https://saqib-project-production.up.railway.app/callback.php';

// ---------------------------------------------------------------------
// 2️⃣  Basic validation – we must have a code and a matching state token
// ---------------------------------------------------------------------
if (isset($_GET['error'])) {
    // User denied consent or another OAuth error occurred
    die('Google OAuth error: ' . htmlspecialchars($_GET['error']) .
        (isset($_GET['error_description']) ? ' – ' . htmlspecialchars($_GET['error_description']) : ''));
}

if (!isset($_GET['code'])) {
    die('Error: No authorization code received.');
}
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? '')) {
    die('Error: Invalid state parameter – possible CSRF attack.');
}
$authCode = $_GET['code'];

// ---------------------------------------------------------------------
// 3️⃣  Exchange the code for an access token (cURL POST request)
// ---------------------------------------------------------------------
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
if (curl_errno($ch)) {
    die('cURL error while obtaining access token: ' . curl_error($ch));
}
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (empty($tokenData['access_token'])) {
    die('Failed to obtain access token. Response: ' . htmlspecialchars($tokenResponse));
}
$accessToken = $tokenData['access_token'];

// ---------------------------------------------------------------------
// 4️⃣  Fetch the user's profile (email, name, id) from Google
// ---------------------------------------------------------------------
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
if (curl_errno($ch)) {
    die('cURL error while fetching user info: ' . curl_error($ch));
}
curl_close($ch);

$user = json_decode($userResponse, true);
if (empty($user['email']) || empty($user['id'])) {
    die('Invalid user data returned from Google.');
}

// ---------------------------------------------------------------------
// 5️⃣  Database – PDO connection + upsert logic (try/catch for safety)
// ---------------------------------------------------------------------
// Adjust these DSN/credentials to match your environment.
$dsn      = 'mysql:host=YOUR_HOST;dbname=YOUR_DB;charset=utf8mb4';
$dbUser   = 'YOUR_DB_USER';
$dbPass   = 'YOUR_DB_PASSWORD';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Look for an existing user with the same email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $user['email']]);
    $row = $stmt->fetch();

    if ($row) {
        // Existing user – log them in
        $userId = $row['id'];
    } else {
        // New user – insert a row and obtain the new id
        $insert = $pdo->prepare(
            'INSERT INTO users (name,email,google_id,created_at) VALUES (:name,:email,:gid,NOW())'
        );
        $insert->execute([
            'name'  => $user['name'] ?? $user['given_name'] ?? '',
            'email' => $user['email'],
            'gid'   => $user['id'],
        ]);
        $userId = $pdo->lastInsertId();
    }
} catch (PDOException $e) {
    // Log the error (you may want to write to a file) and display a friendly message
    error_log('Database error during Google login: ' . $e->getMessage());
    die('A server error occurred while processing your login. Please try again later.');
}

// ---------------------------------------------------------------------
// 6️⃣  Set session variables – you can store more data if you wish
// ---------------------------------------------------------------------
$_SESSION['user_logged_in'] = true;
$_SESSION['user_id']         = $userId;                // primary key from your users table
$_SESSION['user_name']       = $user['name'] ?? '';
$_SESSION['user_email']      = $user['email'];

// ---------------------------------------------------------------------
// 7️⃣  Redirect the authenticated user to the home page (or dashboard)
// ---------------------------------------------------------------------
header('Location: index.php');
exit;
?>
