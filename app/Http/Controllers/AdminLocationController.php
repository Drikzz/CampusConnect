<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminLocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index()
    {
        $query = Location::query()
            ->when(request('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount('meetupLocations');

        return Inertia::render('Admin/admin-meetuploc', [
            'locations' => $query->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'filters' => request()->only('search')
        ]);
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            DB::beginTransaction();
            Location::create($validated);
            DB::commit();
            
            return redirect()->back()->with('success', 'Location created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create location: '.$e->getMessage());
        }
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            DB::beginTransaction();
            $location->update($validated);
            DB::commit();
            
            return redirect()->back()->with('success', 'Location updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update location: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location)
    {
        // Check if location is being used by meetup locations
        $inUse = $location->meetupLocations()->exists();
        
        if ($inUse) {
            return redirect()->back()->with('error', 'This location cannot be deleted because it is in use by meetup locations');
        }
        
        try {
            DB::beginTransaction();
            $location->delete();
            DB::commit();
            
            return redirect()->back()->with('success', 'Location deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete location: '.$e->getMessage());
        }
    }
}
