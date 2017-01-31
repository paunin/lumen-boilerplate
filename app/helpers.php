<?php

//@codingStandardsIgnoreFile

if (!function_exists('sentinels')) {
    /**
     * Parse sentinel string configuration.
     *
     * @param string $sentinels Sentinel configuration format "host:port,host:port,..."
     *
     * @return array
     */
    function sentinels($sentinels)
    {
        return array_map(
            function ($string) {
                $parts = explode(':', $string);

                return [
                    'host' => $parts[0],
                    'port' => $parts[1],
                ];
            },
            explode(',', $sentinels)
        );
    }
}