<?php

declare(strict_types=1);

namespace Rogue\Mask\Http\Home;

use Rogue\Mantle\Routing\Attributes\UGet;

class HomeController
{
    #[UGet('/')]
    #[UGet('/item')]
    #[UGet('/item/{id}')]
    public function index(int $id = 1): string
    {
        return "HomeController@index executed with ID {$id}.<br>";
    }
}
