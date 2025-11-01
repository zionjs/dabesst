<?php
session_start();

// Kredensial yang valid
$valid_username = "zionjs";
$valid_token = "sumbit";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $token = $_POST['token'] ?? '';
    
    // Validasi kredensial
    if ($username === $valid_username && $token === $valid_token) {
        // Simpan data login di session
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['token'] = $token;
        
        // Redirect ke dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        // Redirect kembali dengan pesan error
        $error_message = urlencode("Username atau token salah!");
        header('Location: index.html?error=' . $error_message);
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke halaman login
    header('Location: index.html');
    exit;
}
?>