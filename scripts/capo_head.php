<?php
define('SALT', '$2a$07$32a36184d121c9b1f991d7ae494fa7551ecda9dbd5c0f813d41c0ea3ee122340$');
define('CODE', '$2a$07$32a36184d121c9b1f991duBNYGKo9E8RPdsXSmPD5gENuHaIRfDWK');

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array(
        '2a02:2308::61d:bad:da7a',
        '127.0.0.1',
        '::1',
    ))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file.');
}

switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':
    break;
case 'POST':
    if (!isset($_POST['code']) || crypt($_POST['code'], SALT) !== CODE) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file.');
    }
    break;
default:
    header('HTTP/1.0 405 Method Not Allowed');
    exit('This method is not allowed.');
}


?>
