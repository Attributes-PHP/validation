<?php

namespace Attributes\Validation\Tests\Models\Complex;

interface Logger
{
    public function log(string $message);
}

interface Formatter
{
    public function format();
}

class OnlyLogger implements Logger
{
    public function log(string $message) {}
}

class OnlyFormatter implements Formatter
{
    public function format() {}
}

class LoggerFormatter implements Formatter, Logger
{
    public function format() {}

    public function log(string $message) {}
}
