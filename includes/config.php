<?php

// PayHere Configuration

// --- PayHere Credentials ---
define('PAYHERE_MERCHANT_ID', '1236659');
define('PAYHERE_MERCHANT_SECRET', 'NDg0NDQ2MjEzMDg4Mjg2ODgyMjk5NTIyNTA0OTk4ODQyMzU3Mw==');

// --- Environment: 'sandbox' or 'live' ---
define('PAYHERE_ENV', 'sandbox');

// --- PayHere Endpoint ---
define(
    'PAYHERE_CHECKOUT_URL',
    PAYHERE_ENV === 'sandbox'
        ? 'https://sandbox.payhere.lk/pay/checkout'
        : 'https://www.payhere.lk/pay/checkout'
);

// --- Site Base URL (must match your domain) ---
define('SITE_URL', 'http://localhost/BODIMAK.LK');

// --- Helper: Generate PayHere hash ---
function generatePayhereHash($merchant_id, $order_id, $amount, $currency, $merchant_secret)
{
    return strtoupper(
        md5(
            $merchant_id .
                $order_id .
                number_format($amount, 2, '.', '') .
                $currency .
                strtoupper(md5($merchant_secret))
        )
    );
}

// --- Helper: Verify PayHere notify hash ---
function verifyPayhereNotify($merchant_id, $order_id, $amount, $currency, $status_code, $md5sig, $merchant_secret)
{
    $local_md5sig = strtoupper(
        md5(
            $merchant_id .
                $order_id .
                number_format($amount, 2, '.', '') .
                $currency .
                $status_code .
                strtoupper(md5($merchant_secret))
        )
    );
    return $md5sig === $local_md5sig;
}
