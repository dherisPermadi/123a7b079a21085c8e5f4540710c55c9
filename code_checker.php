<?php

$directory = __DIR__;

$phpFiles = glob($directory . '/**/*.php', GLOB_BRACE);
foreach ($phpFiles as $file) {
    if (strpos($file, '/vendor/') === false) {
        $output = [];
        $exitCode = 0;
        exec("php -l \"$file\"", $output, $exitCode);

        if ($exitCode !== 0) {
            echo "Syntax error in file: $file\n";
            foreach ($output as $line) {
                echo "$line\n";
            }
            echo "-----------------------------------------\n";
        }
    }
}

echo "PHP linting completed.\n";