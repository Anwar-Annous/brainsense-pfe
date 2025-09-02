<?php
// Utility functions for the application

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Calculate weeks using days
        $weeks = floor($diff->d / 7);
        $diff->d -= $weeks * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        $values = array();
        foreach ($string as $k => &$v) {
            if ($k === 'w') {
                if ($weeks > 0) {
                    $values[] = $weeks . ' ' . $v . ($weeks > 1 ? 's' : '');
                }
            } else if ($diff->$k) {
                $values[] = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            }
        }

        if (!$values) {
            return 'just now';
        }

        return implode(', ', $values) . ' ago';
    }
} 