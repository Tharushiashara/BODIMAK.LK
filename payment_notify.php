<?php
/**
 * PayHere Payment Notify Handler
 * This URL receives a POST request from PayHere after payment.
 * Must be publicly accessible (use ngrok for local testing).
 */
require_once 'includes/db.php';
require_once 'includes/config.php';

// Get POST data
$merchant_id     = $_POST['merchant_id']     ?? '';
$order_id        = $_POST['order_id']        ?? '';
$payhere_amount  = $_POST['payhere_amount']  ?? '';
$payhere_currency= $_POST['payhere_currency']?? '';
$status_code     = $_POST['status_code']     ?? '';
$md5sig          = $_POST['md5sig']          ?? '';
$payment_id      = $_POST['payment_id']      ?? '';

// Log for debugging (optional)
file_put_contents(__DIR__ . '/payhere_notify_log.txt',
    date('Y-m-d H:i:s') . ' ' . json_encode($_POST) . "\n",
    FILE_APPEND
);

// Verify the hash signature
if (!verifyPayhereNotify($merchant_id, $order_id, $payhere_amount, $payhere_currency, $status_code, $md5sig, PAYHERE_MERCHANT_SECRET)) {
    http_response_code(400);
    echo "Invalid signature";
    exit();
}

// Find the payment record
$stmt = $pdo->prepare("SELECT * FROM ad_payments WHERE order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

if (!$payment) {
    http_response_code(404);
    echo "Order not found";
    exit();
}

if ($status_code == 2) {
    // SUCCESS
    $upd = $pdo->prepare("UPDATE ad_payments SET status = 'success', payhere_payment_id = ?, updated_at = NOW() WHERE order_id = ?");
    $upd->execute([$payment_id, $order_id]);

    // Activate the ad (make it live)
    $upd2 = $pdo->prepare("UPDATE advertisements SET status = 'active' WHERE ad_id = ?");
    $upd2->execute([$payment['ad_id']]);

} elseif ($status_code == 0) {
    // PENDING (e.g., bank transfer)
    $upd = $pdo->prepare("UPDATE ad_payments SET status = 'pending', payhere_payment_id = ?, updated_at = NOW() WHERE order_id = ?");
    $upd->execute([$payment_id, $order_id]);

} elseif ($status_code == -1) {
    // CANCELLED
    $upd = $pdo->prepare("UPDATE ad_payments SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?");
    $upd->execute([$order_id]);

} elseif ($status_code == -2 || $status_code == -3) {
    // FAILED
    $upd = $pdo->prepare("UPDATE ad_payments SET status = 'failed', updated_at = NOW() WHERE order_id = ?");
    $upd->execute([$order_id]);
}

http_response_code(200);
echo "OK";
