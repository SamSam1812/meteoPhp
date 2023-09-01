<?php

if (isset($_POST['img'])) {
    define('UPLOAD_DIR', './assets/img/');
    $img = $_POST['img'];
    $img = str_replace('data:img/svg;,', '', $img);
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);
    $file = UPLOAD_DIR . uniqid() . '.svg';
    $success = file_put_contents($file, $data);
    print $success ? $file : 'Unable to save the file.';
}


