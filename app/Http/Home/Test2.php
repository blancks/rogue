<?php

namespace Rogue\App\Http\Home;

class Test2
{
    public string $message = 'Test2class!!';

    public function __construct(
        public Test3 $test3
    ) {
    }
}
