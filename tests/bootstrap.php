<?php

use DG\BypassFinals;

define('PHP_UNIT_TEST_MODE', true);

BypassFinals::enable();

function test_files(string $filePath, bool $ensureCreated = false): string
{
    $file = __DIR__ . "/static_files/$filePath";

    if ($ensureCreated) {
        touch($file);
    }
    return rtrim($file, '/');
}
