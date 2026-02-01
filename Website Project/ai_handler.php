<?php
session_start();
header('Content-Type: application/json');

// --- 1. SETUP API ---
// Pastikan API Key Anda benar di sini
$apiKey = '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'; 
$apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

// Ambil input JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Error: Pesan kosong.']);
    exit;
}

$systemPrompt = "Kamu adalah SITA-BOT, asisten akademik cerdas untuk mahasiswa Universitas Pembangunan Jaya. 
Gaya bicaramu formal, akademis, tapi ramah dan suportif seperti dosen pembimbing.
Jawaban harus ringkas (maksimal 3 paragraf), format rapi, dan solutif. Gunakan Bahasa Indonesia.";

// --- PERBAIKAN DI SINI: GANTI MODEL LAMA KE MODEL BARU ---
$data = [
    'model' => 'llama-3.3-70b-versatile', // Menggunakan model terbaru yang didukung
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'temperature' => 0.7
];

// --- 2. EKSEKUSI CURL ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

// FIX SSL KHUSUS LOCALHOST/XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// --- 3. DIAGNOSA ERROR ---
if ($curlError) {
    echo json_encode(['reply' => "Sistem Error (cURL): $curlError. Cek koneksi internet."]);
    exit;
}

if ($httpCode !== 200) {
    $errorResp = json_decode($response, true);
    $errorMsg = $errorResp['error']['message'] ?? 'Unknown Error';
    echo json_encode(['reply' => "API Error ($httpCode): $errorMsg"]);
    exit;
}

// --- 4. SUKSES ---
$result = json_decode($response, true);
$reply = $result['choices'][0]['message']['content'] ?? 'AI tidak menjawab.';
$reply = nl2br(htmlspecialchars($reply));

echo json_encode(['reply' => $reply]);
?>