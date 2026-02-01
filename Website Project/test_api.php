<?php
// Tampilkan semua error PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Sedang mengetes koneksi ke Groq API...</h3>";

$apiKey = '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'; // API Key Anda
$apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

$data = [
    'model' => 'llama3-8b-8192',
    'messages' => [
        ['role' => 'user', 'content' => 'Tes koneksi. Jawab "Koneksi Berhasil" jika kamu menerima pesan ini.']
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

// BYPASS SSL (Penting untuk XAMPP)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "<b>HTTP Code:</b> $httpCode <br>";

if ($curlError) {
    echo "<div style='color:red; font-weight:bold;'>ERROR CURL: $curlError</div>";
    echo "<p>Solusi: Internet mati atau sertifikat SSL bermasalah (sudah di-bypass).</p>";
} elseif ($httpCode !== 200) {
    echo "<div style='color:red; font-weight:bold;'>ERROR DARI API (Code $httpCode):</div>";
    echo "<pre>$response</pre>";
    echo "<p>Solusi: Cek pesan di atas. Biasanya 'Invalid API Key' atau 'Rate limit exceeded'.</p>";
} else {
    $result = json_decode($response, true);
    $reply = $result['choices'][0]['message']['content'] ?? 'Tidak ada respon konten';
    echo "<div style='color:green; font-weight:bold;'>SUKSES! Respon AI:</div>";
    echo "<blockquote>$reply</blockquote>";
    echo "<p>Artinya backend aman. Masalah ada di JavaScript/Frontend.</p>";
}
?>