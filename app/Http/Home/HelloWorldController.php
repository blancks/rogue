<?php

declare(strict_types=1);

namespace App\Http\Home;

use Mantle\Routing\Attributes\Get;

class HelloWorldController
{
    #[Get('/helloworld2')]
    public function __invoke(Test $test): string
    {
        return 'Hello World from my app! ' . $test->test2->test3->message;
    }
}
