<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.html');
    exit;
}

$username = $_SESSION['username'];
$token = $_SESSION['token'];

// URL file JSON di GitHub
$github_url = 'https://raw.githubusercontent.com/zionjs/database/refs/heads/main/number.json';

// Ambil data dari GitHub
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $github_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: PHP Script'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$numbers = [];
if ($http_code === 200 && $response) {
    $numbers = json_decode($response, true) ?? [];
}

// Proses form tambah data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_number'])) {
    $new_number = $_POST['new_number'] ?? '';
    
    if (is_numeric($new_number)) {
        // Tambahkan angka baru ke array
        $numbers[] = (int)$new_number;
        
        // Simpan ke GitHub
        include 'github_api.php';
        $result = saveToGitHub($numbers, $username, $token);
        
        if ($result === true) {
            $message = "Angka berhasil ditambahkan!";
            $message_class = "success";
        } else {
            $message = "Gagal menyimpan data: " . $result;
            $message_class = "error";
        }
    } else {
        $message = "Masukkan angka yang valid!";
        $message_class = "error";
    }
}

// Proses hapus data
if (isset($_GET['delete'])) {
    $index = $_GET['delete'];
    
    if (isset($numbers[$index])) {
        // Hapus angka dari array
        array_splice($numbers, $index, 1);
        
        // Simpan ke GitHub
        include 'github_api.php';
        $result = saveToGitHub($numbers, $username, $token);
        
        if ($result === true) {
            $message = "Angka berhasil dihapus!";
            $message_class = "success";
        } else {
            $message = "Gagal menghapus data: " . $result;
            $message_class = "error";
        }
    } else {
        $message = "Indeks tidak valid!";
        $message_class = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GitHub Data Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard - GitHub Data Manager</h1>
        <p>Selamat datang, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="data-section">
            <h2>Daftar Angka</h2>
            
            <?php if (empty($numbers)): ?>
                <p class="loading">Tidak ada data atau sedang memuat...</p>
            <?php else: ?>
                <ul class="data-list">
                    <?php foreach ($numbers as $index => $number): ?>
                        <li>
                            <span>Angka: <?php echo htmlspecialchars($number); ?></span>
                            <a href="?delete=<?php echo $index; ?>" class="delete-btn" 
                               onclick="return confirm('Yakin ingin menghapus angka ini?')">Hapus</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <form method="POST" class="add-form">
                <input type="number" name="new_number" placeholder="Masukkan angka baru" required>
                <button type="submit" name="add_number" class="btn-action">Tambah</button>
            </form>
        </div>
        
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</body>
</html>