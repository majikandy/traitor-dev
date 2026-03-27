<?php

namespace App\Http\Controllers;

use App\Models\SentEmail;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function settings(): \Illuminate\View\View
    {
        return view('admin.settings', [
            'businessName' => Setting::get('business_name', ''),
        ]);
    }

    public function updateSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['business_name' => 'nullable|string|max:255']);
        Setting::set('business_name', $request->input('business_name'));

        return back()->with('success', 'Settings saved.');
    }

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

    public function emails(): \Illuminate\View\View
    {
        $emails = SentEmail::latest()->paginate(50);
        return view('admin.emails', compact('emails'));
    }

    public function showEmail(SentEmail $email): \Illuminate\View\View
    {
        return view('admin.email-show', compact('email'));
    }
}
