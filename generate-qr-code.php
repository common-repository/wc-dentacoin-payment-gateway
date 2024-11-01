<?php

if(!empty($_GET['wallet_address'])) {
    if (!class_exists('QRcode')) {
        include('./assets/libs/phpqrcode/qrlib.php');
    }
    // outputs image directly into browser, as PNG stream
    QRcode::png($_GET['wallet_address'], false, QR_ECLEVEL_L, 15);
}