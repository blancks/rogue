<?php

// TODO: when a cli tool will be created, this must be generated as cached resource

if (php_sapi_name() === 'cli-server') {
    // @phpstan-ignore-next-line
    $path = __DIR__ . '/../../public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

require __DIR__ . '/../../public/index.php';
