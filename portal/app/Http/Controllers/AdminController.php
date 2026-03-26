<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function logs(): \Illuminate\View\View
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return view('admin.logs', ['entries' => []]);
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -2000);

        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $m)) {
                if ($current) {
                    $entries[] = $current;
                }
                $current = [
                    'date'    => $m[1],
                    'level'   => strtolower($m[2]),
                    'message' => $m[3],
                    'detail'  => '',
                ];
            } elseif ($current) {
                $current['detail'] .= $line . "\n";
            }
        }

        if ($current) {
            $entries[] = $current;
        }

        return view('admin.logs', ['entries' => array_reverse($entries)]);
    }
}
