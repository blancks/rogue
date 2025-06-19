<?php

namespace Rogue\App\Http\Home;

class Test
{
    public string $message = 'Testclass';

    public function __construct(
        public Test2 $test2
    ) {
    }
}
