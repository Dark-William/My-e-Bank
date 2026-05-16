<?php
/**
 * e-Bank API - Backend natif minimal (PHP + SQLite)
 * Contournement des erreurs d'installation Composer/Laravel
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

$dbFile = __DIR__ . '/database.sqlite';
$dbExists = file_exists($dbFile);
$db = new PDO("sqlite:$dbFile");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize DB schema if it doesn't exist
if (!$dbExists) {
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            token TEXT
        );
        CREATE TABLE accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            balance REAL DEFAULT 0,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );
        CREATE TABLE accounting_journals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            type TEXT CHECK(type IN ('credit', 'debit')),
            amount REAL NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY(account_id) REFERENCES accounts(id)
        );
    ");
}

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function getJsonBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function getAuthUser($db) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) return $user;
    }
    
    sendResponse(['message' => 'Non autorisé.'], 401);
}

if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $arh = [];
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst(strtolower($ak_val));
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['CONTENT_LENGTH'])) $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        return $arh;
    }
}

$requestUri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$method = $_SERVER['REQUEST_METHOD'];

// Route: POST /api/register
if ($method === 'POST' && $requestUri === '/api/register') {
    $body = getJsonBody();
    if (empty($body['name']) || empty($body['email']) || empty($body['password'])) {
        sendResponse(['message' => 'Données invalides.'], 422);
    }
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $body['email']]);
    if ($stmt->fetch()) {
        sendResponse(['message' => 'Email déjà utilisé.'], 422);
    }
    
    $hash = password_hash($body['password'], PASSWORD_DEFAULT);
    
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([':name' => $body['name'], ':email' => $body['email'], ':password' => $hash]);
    $userId = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO accounts (user_id, balance) VALUES (:user_id, 0)");
    $stmt->execute([':user_id' => $userId]);
    $db->commit();
    
    sendResponse([
        'message' => 'Compte créé avec succès.',
        'user' => ['id' => $userId, 'name' => $body['name'], 'email' => $body['email']]
    ], 201);
}

// Route: POST /api/login
if ($method === 'POST' && $requestUri === '/api/login') {
    $body = getJsonBody();
    if (empty($body['email']) || empty($body['password'])) {
        sendResponse(['message' => 'Données invalides.'], 422);
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $body['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($body['password'], $user['password'])) {
        sendResponse(['message' => 'Email ou mot de passe incorrect.'], 401);
    }
    
    $token = bin2hex(random_bytes(32));
    $stmt = $db->prepare("UPDATE users SET token = :token WHERE id = :id");
    $stmt->execute([':token' => $token, ':id' => $user['id']]);
    
    sendResponse([
        'token' => $token,
        'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]
    ]);
}

// Route: GET /api/logout
if ($method === 'GET' && $requestUri === '/api/logout') {
    $user = getAuthUser($db);
    $stmt = $db->prepare("UPDATE users SET token = NULL WHERE id = :id");
    $stmt->execute([':id' => $user['id']]);
    sendResponse(['message' => 'Déconnecté avec succès.']);
}

// Route: GET /api/user
if ($method === 'GET' && $requestUri === '/api/user') {
    $user = getAuthUser($db);
    sendResponse($user);
}

// Route: GET /api/account
if ($method === 'GET' && $requestUri === '/api/account') {
    $user = getAuthUser($db);
    
    $stmt = $db->prepare("SELECT id, user_id, balance FROM accounts WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user['id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT id, account_id, type, amount, date FROM accounting_journals WHERE account_id = :account_id ORDER BY date DESC");
    $stmt->execute([':account_id' => $account['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // cast numbers
    $account['id'] = (int)$account['id'];
    $account['user_id'] = (int)$account['user_id'];
    $account['balance'] = (float)$account['balance'];
    
    foreach ($transactions as &$t) {
        $t['id'] = (int)$t['id'];
        $t['account_id'] = (int)$t['account_id'];
        $t['amount'] = (float)$t['amount'];
    }
    
    sendResponse([
        'account' => $account,
        'transactions' => $transactions
    ]);
}

// Route: POST /api/account/credit
if ($method === 'POST' && $requestUri === '/api/account/credit') {
    $user = getAuthUser($db);
    $body = getJsonBody();
    
    if (empty($body['amount']) || !is_numeric($body['amount']) || $body['amount'] <= 0) {
        sendResponse(['message' => 'Montant invalide.'], 422);
    }
    
    $stmt = $db->prepare("SELECT id, balance FROM accounts WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user['id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $db->beginTransaction();
    $newBalance = $account['balance'] + $body['amount'];
    $stmt = $db->prepare("UPDATE accounts SET balance = :balance WHERE id = :id");
    $stmt->execute([':balance' => $newBalance, ':id' => $account['id']]);
    
    $stmt = $db->prepare("INSERT INTO accounting_journals (account_id, type, amount, date) VALUES (:account_id, 'credit', :amount, :date)");
    $stmt->execute([
        ':account_id' => $account['id'],
        ':amount' => $body['amount'],
        ':date' => date('Y-m-d\TH:i:sP')
    ]);
    $db->commit();
    
    // return new state
    $account['balance'] = (float)$newBalance;
    $account['id'] = (int)$account['id'];
    
    $stmt = $db->prepare("SELECT id, account_id, type, amount, date FROM accounting_journals WHERE account_id = :account_id ORDER BY date DESC");
    $stmt->execute([':account_id' => $account['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($transactions as &$t) { $t['id'] = (int)$t['id']; $t['account_id'] = (int)$t['account_id']; $t['amount'] = (float)$t['amount']; }
    
    sendResponse([
        'message' => 'Compte crédité avec succès.',
        'account' => $account,
        'transactions' => $transactions
    ]);
}

// Route: POST /api/account/debit
if ($method === 'POST' && $requestUri === '/api/account/debit') {
    $user = getAuthUser($db);
    $body = getJsonBody();
    
    if (empty($body['amount']) || !is_numeric($body['amount']) || $body['amount'] <= 0) {
        sendResponse(['message' => 'Montant invalide.'], 422);
    }
    
    $stmt = $db->prepare("SELECT id, balance FROM accounts WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user['id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($body['amount'] > $account['balance']) {
        sendResponse(['message' => 'Solde insuffisant.'], 422);
    }
    
    $db->beginTransaction();
    $newBalance = $account['balance'] - $body['amount'];
    $stmt = $db->prepare("UPDATE accounts SET balance = :balance WHERE id = :id");
    $stmt->execute([':balance' => $newBalance, ':id' => $account['id']]);
    
    $stmt = $db->prepare("INSERT INTO accounting_journals (account_id, type, amount, date) VALUES (:account_id, 'debit', :amount, :date)");
    $stmt->execute([
        ':account_id' => $account['id'],
        ':amount' => $body['amount'],
        ':date' => date('Y-m-d\TH:i:sP')
    ]);
    $db->commit();
    
    // return new state
    $account['balance'] = (float)$newBalance;
    $account['id'] = (int)$account['id'];
    
    $stmt = $db->prepare("SELECT id, account_id, type, amount, date FROM accounting_journals WHERE account_id = :account_id ORDER BY date DESC");
    $stmt->execute([':account_id' => $account['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($transactions as &$t) { $t['id'] = (int)$t['id']; $t['account_id'] = (int)$t['account_id']; $t['amount'] = (float)$t['amount']; }
    
    sendResponse([
        'message' => 'Compte débité avec succès.',
        'account' => $account,
        'transactions' => $transactions
    ]);
}

sendResponse(['message' => 'Route non trouvée.'], 404);
