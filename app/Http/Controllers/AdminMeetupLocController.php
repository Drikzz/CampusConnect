<?php

namespace App\Http\Controllers;

use App\Models\AdminMeetupLoc;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminMeetupLocController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/admin-meetuploc', [
            'locations' => AdminMeetupLoc::query()
                ->when(request('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('address', 'like', "%{$search}%");
                })
                ->when(request('filter') && request('filter') !== 'all', function($query, $filter) {
                    $query->where('is_active', $filter === 'active');
                })
                ->latest()
                ->paginate(10)
                ->withQueryString()
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        AdminMeetupLoc::create($validated);

        return redirect()->back()->with('success', 'Location created successfully');
    }

    public function show(AdminMeetupLoc $adminMeetupLoc)
    {
        //
    }

    public function edit(AdminMeetupLoc $adminMeetupLoc)
    {
        //
    }

    public function update(Request $request, AdminMeetupLoc $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location->update($validated);

        return redirect()->back()->with('success', 'Location updated successfully');
    }

    public function destroy(AdminMeetupLoc $location)
    {
        $location->delete();
        return redirect()->back()->with('success', 'Location deleted successfully');
    }
}
