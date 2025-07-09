<?php

declare(strict_types=1);

namespace App\Http\Home;

use Mantle\Routing\Attributes\Get;

class HomeController
{
    #[Get('/appitem/{id}')]
    public function item(int $id = 1): array
    {
        return [
            "HomeController@index executed with ID {$id} from app.",
            $id,
            memory_get_usage(true) / 1024
        ];
    }
}
