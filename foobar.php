#!/usr/bin/php
<?php

// Ensure that the script is run from the command line
if (php_sapi_name() !== 'cli') {
    exit("This script can only be run from the command line.\n");
}

// Loop from 1 to 100
for ($i = 1; $i <= 100; $i++) {
    $output = '';

    // Check if the number is divisible by 3 and/or 5
    if ($i % 3 === 0) {
        $output .= 'foo';
    }
    if ($i % 5 === 0) {
        $output .= 'bar';
    }

    // Output the number or the combined result
    echo ($output !== '') ? $output : $i;

    // Add a comma and space unless it's the last number
    if ($i < 100) {
        echo ', ';
    } else {
        echo "\n";
    }
}
