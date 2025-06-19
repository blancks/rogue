<?php

declare(strict_types=1);

namespace Rogue\App\Http\Home;

use Rogue\Mantle\Routing\Attributes\Get;

class HelloWorldController
{
    #[Get('/helloworld2')]
    public function __invoke(Test $test): string
    {
        return 'Hello World from my app! ' . $test->test2->test3->message;
    }
}
