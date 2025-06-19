<?php

declare(strict_types=1);

namespace Rogue\Mask\Http\Home;

use Rogue\Mantle\Routing\Attributes\UGet;

class HelloWorldController
{
    #[UGet('/helloworld')]
    public function __invoke(): string
    {
        return 'Hello World';
    }
}
