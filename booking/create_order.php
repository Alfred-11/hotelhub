<?php
ob_start();
header('Content-Type: application/json');

require('../vendor/autoload.php');
use Razorpay\Api\Api;

include '../db.php';
session_start();

$api = new Api('rzp_test_YXSmAaz8w7PTl9', 'vtBXANJQ79EqQ8AaVs1PmEdA');

// Validate and parse amount
$amount = isset($_POST['amount']) ? preg_replace('/[^0-9.]/', '', $_POST['amount']) : 0;
$amount = floatval($amount);

if ($amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit;
}

try {
    $order = $api->order->create([
        'amount' => intval(round($amount * 100)), // amount in paise
        'currency' => 'INR',
        'receipt' => 'rcptid_' . time()
    ]);

    echo json_encode(['success' => true, 'order_id' => $order['id']]);
} catch (Exception $e) {
    error_log('Razorpay order create error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to create order']);
}
