<?php
// ====================================================================
// Google Search Scraper (Free version, No API Key)
// ====================================================================
// This script fetches top Google search results and returns them as JSON.
// Place this file in your /api/ or backend folder (e.g., backend/scraper.php)
// Make sure your server has "allow_url_fopen" or cURL enabled in php.ini.
// ====================================================================

require_once __DIR__ . '/../auth/check.php'; // comment this if not using login auth
header('Content-Type: application/json');

// ----------------------
// 1. Validate input
// ----------------------
$q = trim($_GET['q'] ?? '');
if ($q === '') {
    http_response_code(400);
    echo json_encode(['error' => 'No query provided']);
    exit;
}

// ----------------------
// 2. Setup Google URL
// ----------------------
$query = urlencode($q);
$url = "https://www.google.com/search?q={$query}&num=10&hl=en";

// ----------------------
// 3. Setup cURL
// ----------------------
$headers = [
    'Accept-Language: en-US,en;q=0.9',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Referer: https://www.google.com/',
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.5993.90 Safari/537.36',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10,
]);
$html = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err || !$html) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to fetch results', 'curl_error' => $err]);
    exit;
}

// ----------------------
// 4. Parse results using DOM
// ----------------------
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$results = [];

// Modern Google search results are inside <div class="tF2Cxc"> containers
$nodes = $xpath->query('//div[@class="tF2Cxc"]//a/h3/ancestor::a');

foreach ($nodes as $a) {
    $href = $a->getAttribute('href');
    $title = trim($a->textContent);
    if ($href && $title) {
        $results[] = ['title' => $title, 'link' => $href];
        if (count($results) >= 10) break;
    }
}

// ----------------------
// 5. Fallback (in case structure changes)
// ----------------------
if (empty($results)) {
    $anchors = $dom->getElementsByTagName('a');
    foreach ($anchors as $a) {
        $href = $a->getAttribute('href');
        $txt = trim($a->textContent);
        if (strpos($href, '/url?q=') === 0) {
            $parsed = parse_url($href);
            parse_str($parsed['query'] ?? '', $params);
            if (isset($params['q'])) {
                $results[] = [
                    'title' => $txt ?: $params['q'],
                    'link' => $params['q']
                ];
                if (count($results) >= 10) break;
            }
        }
    }
}

// ----------------------
// 6. Send JSON response
// ----------------------
if (empty($results)) {
    http_response_code(404);
    echo json_encode(['error' => 'No results found. Google may have blocked automated scraping.']);
    exit;
}

echo json_encode([
    'success' => true,
    'query' => $q,
    'results' => array_slice($results, 0, 10)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
