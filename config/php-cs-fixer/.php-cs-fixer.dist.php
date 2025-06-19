<?php

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                __DIR__.'/../../app',
                __DIR__.'/../../mantle',
                __DIR__.'/../../mask',
                __DIR__.'/../../public',
                __DIR__.'/../../test',
            ])
    );
