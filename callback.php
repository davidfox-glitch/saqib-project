<?php
// callback.php – receives ?code= from Google and logs the user in
session_start();

// -----------------------------------------------------------------
// 1️⃣  CONFIGURATION (environment vars, redirect URI, DB placeholder)
// -----------------------------------------------------------------
$clientId     = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri  = 'https://saqib-project-production.up.railway.app/callback.php';

// -----------------------------------------------------------------
// 2️⃣  BASIC VALIDATION – make sure we have a code and a matching state
// -----------------------------------------------------------------
if (!isset($_GET['code'])) {
    die('Error: No authorization code received.');
}
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? '')) {
    die('Error: Invalid state parameter (possible CSRF).');
}
$authCode = $_GET['code'];

// -----------------------------------------------------------------
// 3️⃣  EXCHANGE AUTH CODE FOR ACCESS TOKEN (POST request via cURL)
// -----------------------------------------------------------------
$tokenEndpoint = 'https://oauth2.googleapis.com/token';
$postFields = http_build_query([
    'code'          => $authCode,
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri'  => $redirectUri,
    'grant_type'    => 'authorization_code',
]);

$ch = curl_init($tokenEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    die('cURL error while fetching access token: ' . curl_error($ch));
}
curl_close($ch);

$tokenData = json_decode($response, true);
if (empty($tokenData['access_token'])) {
    die('Failed to obtain access token. Response: ' . htmlspecialchars($response));
}
$accessToken = $tokenData['access_token'];

// -----------------------------------------------------------------
// 4️⃣  GET USER INFO FROM GOOGLE (using the access token)
// -----------------------------------------------------------------
$userInfoEndpoint = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userInfoEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
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

// -----------------------------------------------------------------
// 5️⃣  DATABASE – check if the e‑mail exists, insert if new
// -----------------------------------------------------------------
// ---------------------------------------------------------------
// Replace the placeholder below with your real DB connection details.
// ---------------------------------------------------------------
/* ---------- PDO example (recommended) -------------------------- */
/*
$pdo = new PDO('mysql:host=YOUR_HOST;dbname=YOUR_DB;charset=utf8mb4', 'YOUR_USER', 'YOUR_PASS');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
*/
/* ---------- MySQLi example ----------------------------------- */
/*
$mysqli = new mysqli('YOUR_HOST', 'YOUR_USER', 'YOUR_PASS', 'YOUR_DB');
if ($mysqli->connect_error) {
    die('Database connection error: ' . $mysqli->connect_error);
}
*/

// -----------------------------------------------------------------
// Choose your preferred driver (uncomment the block you will use)
// -----------------------------------------------------------------
/* ---------- PDO flow ------------------------------------------ */
/*
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $user['email']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Existing user – log them in
    $userId = $row['id'];
} else {
    // New user – create a record
    $insert = $pdo->prepare('INSERT INTO users (name,email,google_id,created_at) VALUES (:name,:email,:gid,NOW())');
    $insert->execute([
        'name'  => $user['name'] ?? $user['given_name'] ?? '',
        'email' => $user['email'],
        'gid'   => $user['id']
    ]);
    $userId = $pdo->lastInsertId();
}
*/

/* ---------- MySQLi flow --------------------------------------- */
/*
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt->close();
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,google_id,created_at) VALUES (?,?,?,NOW())');
    $name = $user['name'] ?? $user['given_name'] ?? '';
    $stmt->bind_param('sss', $name, $user['email'], $user['id']);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();
}
*/

// -----------------------------------------------------------------
// 6️⃣  SET SESSION (you may want to store additional fields)
// -----------------------------------------------------------------
$_SESSION['user_logged_in'] = true;
$_SESSION['user_id']         = $userId;                     // primary key in your users table
$_SESSION['user_name']       = $user['name'] ?? '';
$_SESSION['user_email']      = $user['email'];

// -----------------------------------------------------------------
// 7️⃣  REDIRECT TO HOME / DASHBOARD
// -----------------------------------------------------------------
header('Location: index.php');
exit;
?>
