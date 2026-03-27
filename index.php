<?php

// Mengarahkan pencarian aset (foto/css/js) ke dalam folder public
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Menghidupkan mesin utama Laravel
require_once __DIR__.'/public/index.php';