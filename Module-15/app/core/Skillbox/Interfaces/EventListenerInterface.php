<?php

namespace App\Base\Skillbox\Interfaces;

interface EventListenerInterface
{
    public function attachEvent(string $method, callable $callbackFun);
    public function detouchEvent(string $method);
}
