<?php

declare(strict_types=1);

if (!function_exists('true_sleep')) {
    function true_sleep(float|int $seconds): void
    {
        $end = microtime(true) + $seconds;
        while (($remaining = $end - microtime(true)) > 0) {
            usleep((int)($remaining * 1_000_000));
        }
    }
}
