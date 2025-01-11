<?php

return [
    'optimize_composer' => true,
    'opcache' => [
        'enable' => true,
        'memory_consumption' => 128,
        'interned_strings_buffer' => 16,
        'max_accelerated_files' => 10000,
        'validate_timestamps' => true,
        'revalidate_freq' => 0,
    ],
];
