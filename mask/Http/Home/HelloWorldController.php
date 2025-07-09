<?php

declare(strict_types=1);

namespace Mask\Http\Home;

use Mantle\Routing\Attributes\UGet;

class HelloWorldController
{
    #[UGet('/helloworld')]
    public function __invoke(): string
    {
        return 'Hello World';
    }
}
