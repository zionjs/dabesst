<?php
function saveToGitHub($data, $username, $token) {
    // Konfigurasi GitHub API
    $repo_owner = 'zionjs';
    $repo_name = 'database';
    $file_path = 'number.json';
    $branch = 'main';
    
    // URL API GitHub untuk mendapatkan informasi file
    $api_url = "https://api.github.com/repos/{$repo_owner}/{$repo_name}/contents/{$file_path}";
    
    // Encode data ke JSON
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    $content = base64_encode($json_data);
    
    // Dapatkan SHA file saat ini
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP Script',
        'Authorization: token ' . $token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $sha = null;
    if ($http_code === 200) {
        $file_info = json_decode($response, true);
        $sha = $file_info['sha'] ?? null;
    }
    
    curl_close($ch);
    
    // Update file di GitHub
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'message' => 'Update number.json via web interface',
        'content' => $content,
        'sha' => $sha,
        'branch' => $branch
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP Script',
        'Content-Type: application/json',
        'Authorization: token ' . $token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return true;
    } else {
        // Debug info
        $response_data = json_decode($response, true);
        return $response_data['message'] ?? 'Unknown error occurred';
    }
}
?>