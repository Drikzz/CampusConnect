<?php

namespace App\Http\Controllers;

use App\Models\AdminReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AdminReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/admin-reports-complaints', [
            'auth' => [
                'user' => Auth::user()
            ],
            'reports' => AdminReport::with(['reporter', 'subject'])
                ->when(request('search'), function($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('subject', 'like', "%{$search}%")
                          ->orWhereHas('reporter', function($q) use ($search) {
                              $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                          });
                    });
                })
                ->when(request('filter') && request('filter') !== 'all', function($query, $filter) {
                    $query->where('status', $filter);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString()
        ]);
    }

    public function update(Request $request, AdminReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,investigating,resolved'
        ]);

        $report->update($validated);

        return back()->with('success', 'Report status updated successfully');
    }

  
}
