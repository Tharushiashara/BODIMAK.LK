<?php

// PayHere Configuration

//define constant hadnwa 
define('PAYHERE_MERCHANT_ID', '1236659');
define('PAYHERE_MERCHANT_SECRET', 'NDg0NDQ2MjEzMDg4Mjg2ODgyMjk5NTIyNTA0OTk4ODQyMzU3Mw==');

// Sandbox (Testing) , Live (Real Payments)thorana thana
define('PAYHERE_ENV', 'sandbox'); // sandbox / live


// PayHere payment page URL thiranaya krana kotasa
define(
    'PAYHERE_CHECKOUT_URL',
    PAYHERE_ENV === 'sandbox'
        ? 'https://sandbox.payhere.lk/pay/checkout' //Environment eka sandbox nam me URL use.Test payment page eka.
        : 'https://www.payhere.lk/pay/checkout' //Sandbox newe nm Live payment page eka.
);

// --- Site Base URL (localhost URL) ---
define('SITE_URL', 'http://localhost/BODIMAK.LK');

// function
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

//  Verify PayHere notify hash ---
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
