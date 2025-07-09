<?php

declare(strict_types=1);
use Mantle\Providers\WebServiceProvider;

require __DIR__ . '/../mantle/bootstrap.php';

$provider = new WebServiceProvider();
$provider->register();
$provider->boot();
