<?php
// Simple diagnostic script to test if PHP files can be accessed
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>PDF Access Test</title></head><body>";
echo "<h1>PDF Generation Access Test</h1>";
echo "<p>If you can see this page, PHP files are accessible.</p>";
echo "<hr>";

// Check if session is working
session_start();
echo "<h2>Session Check:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session status: " . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
} else {
    echo "<p style='color: red;'>User not logged in - This could cause the 404 error if middleware redirects</p>";
}

echo "<hr>";
echo "<h2>File System Check:</h2>";

// Check if generate_pdf.php exists
$pdfFile = __DIR__ . '/generate_pdf.php';
if (file_exists($pdfFile)) {
    echo "<p style='color: green;'>✓ generate_pdf.php exists at: " . $pdfFile . "</p>";
    echo "<p>File size: " . filesize($pdfFile) . " bytes</p>";
    echo "<p>Readable: " . (is_readable($pdfFile) ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p style='color: red;'>✗ generate_pdf.php not found at: " . $pdfFile . "</p>";
}

// Check includes file
$includesFile = __DIR__ . '/includes/pdf_contents.php';
if (file_exists($includesFile)) {
    echo "<p style='color: green;'>✓ includes/pdf_contents.php exists</p>";
} else {
    echo "<p style='color: red;'>✗ includes/pdf_contents.php not found</p>";
}

echo "<hr>";
echo "<h2>URL Test:</h2>";
echo "<p>Current script: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Server software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test URL generation
$testUrl = 'generate_pdf.php?requestId=123&activityName=Test';
echo "<p>Test URL that would be generated: <code>" . htmlspecialchars($testUrl) . "</code></p>";
echo "<p><a href='" . htmlspecialchars($testUrl) . "' target='_blank'>Click here to test the PDF generation URL</a></p>";
echo "<p style='font-size: 0.9em; color: #666;'>Note: This will fail with missing parameters, but you should NOT get a 404 error.</p>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If you see a 404 error when clicking the test link above, it's an nginx/server configuration issue</li>";
echo "<li>If you see a PHP error about missing parameters, the file is accessible and the issue is with parameter passing</li>";
echo "<li>Check your nginx error logs at: <code>/var/log/nginx/error.log</code></li>";
echo "<li>Check your PHP error logs (location varies by server configuration)</li>";
echo "</ol>";

echo "</body></html>";
?>
