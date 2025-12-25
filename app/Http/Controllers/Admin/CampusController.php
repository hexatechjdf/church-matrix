<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campus;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    public function list(Request $request)
    {
        $query = Campus::query()->orderBy('created_at', 'desc');
        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Create + Update (single method)
     */
    public function save(Request $request)
    {
        $request->validate([
            'campus_id'    => ['required','string','max:255'],
            'location_id'  => 'required|string|max:255',
            'campus_name'  => 'nullable|string|max:255',
        ]);

        // Check if campus_unique_id already exists
        $campusExistsQuery = Campus::where('campus_unique_id', $request->campus_id);

        if ($request->id) {
            // Edit → ignore current record
            $campusExistsQuery->where('id', '!=', (int)$request->id);
        }

        $existingCampus = $campusExistsQuery->first();

        if ($existingCampus) {
            return response()->json([
                'error'   => true,
                'message' => 'Campus ID already exists',
            ]);
        }

        // Create or Update
        Campus::updateOrCreate(
            ['id' => $request->id],
            [
                'location_id'      => $request->location_id,
                'campus_unique_id' => $request->campus_id,
                'name'             => $request->campus_name ?? 'campus',
                'description'      => $request->campus_name,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $request->id
                ? 'Campus updated successfully'
                : 'Campus created successfully',
        ]);
    }


    /**
     * Delete campus
     */
    public function destroy($id)
    {
        Campus::findOrFail($id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Campus deleted successfully'
        ]);
    }
}
