<?php
require_once __DIR__ . '/../auth/check.php';
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No PDF uploaded']);
    exit;
}
$upload = $_FILES['pdf'];
$tmp = $upload['tmp_name'];
$ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(400);
    echo json_encode(['error' => 'Only PDF allowed']);
    exit;
}
$text = '';
$pdftotext = trim(shell_exec('which pdftotext'));
if ($pdftotext) {
    $outtxt = tempnam(sys_get_temp_dir(), 'pdf_').'.txt';
    $cmd = escapeshellcmd($pdftotext) . ' ' . escapeshellarg($tmp) . ' ' . escapeshellarg($outtxt);
    shell_exec($cmd);
    if (file_exists($outtxt)) {
        $text = file_get_contents($outtxt);
        @unlink($outtxt);
    }
}
if ($text === '') {
    $bin = file_get_contents($tmp);
    $clean = preg_replace('/[^\\x20-\\x7E\\s]/', ' ', substr($bin, 0, 20000));
    $text = strip_tags($clean);
}
if (trim($text) === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Could not extract text from PDF on this server. Consider enabling pdftotext or using the Hugging Face API option in README.']);
    exit;
}
if (defined('HF_API_KEY') && HF_API_KEY !== '') {
    $url = 'https://api-inference.huggingface.co/models/facebook/bart-large-cnn';
    $data = ['inputs' => mb_substr($text, 0, 5000)];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.HF_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        http_response_code(500);
        echo json_encode(['error' => 'HF API error: '.$err]);
        exit;
    }
    $json = json_decode($res, true);
    if (isset($json[0]['summary_text'])) {
        echo json_encode(['success' => true, 'summary' => $json[0]['summary_text']]);
        exit;
    } else {
        echo json_encode(['success' => true, 'summary' => strip_tags($res)]);
        exit;
    }
} else {
    $sentences = preg_split('/(?<=[.?!])\s+/', trim($text));
    $summary = '';
    $count = min(7, count($sentences));
    for ($i=0;$i<$count;$i++) {
        $summary .= ' ' . trim($sentences[$i]);
    }
    $summary = trim($summary);
    if ($summary === '') $summary = mb_substr(trim($text), 0, 600) . '...';
    echo json_encode(['success' => true, 'summary' => $summary]);
    exit;
}
?>