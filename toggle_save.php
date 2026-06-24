<?php
// pasubimen data aragenhart kotasa add save or delete SQL command run
session_start();
header('Content-Type: application/json');
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$ad_id = $data['ad_id'] ?? null;

if (!$ad_id || !is_numeric($ad_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid listing ID']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    //kalin save krlad kyla blnwa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_listings WHERE user_id = ? AND ad_id = ?");
    $stmt->execute([$user_id, $ad_id]);
    $is_saved = $stmt->fetchColumn() > 0;

    if ($is_saved) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM saved_listings WHERE user_id = ? AND ad_id = ?");
        $stmt->execute([$user_id, $ad_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Save
        $stmt = $pdo->prepare("INSERT IGNORE INTO saved_listings (user_id, ad_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $ad_id]);
        echo json_encode(['status' => 'success', 'action' => 'saved']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
