<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request) {
        // tab: pending / approved
        $tab = $request->query('tab', 'pending');

        $query = StampCorrectionRequest::query()
            ->with(['user', 'attendance']);

        if ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $tab = 'pending';
            $query->where('status', 'pending');
        }

        $requests = $query->orderByDesc('created_at')->get();

        return view('admin.request_list', [
            'tab' => $tab,
            'requests' => $requests,
        ]);
    }
}
