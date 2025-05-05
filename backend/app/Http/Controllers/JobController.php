<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     * this function is used to show all jobs
     */
    public function index()
    {
        return response()->json(Job::all()); // 
    }

    /**
     * Store a newly created resource in storage.
     * this function is used to create a new job
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'positionName' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'companyLocation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
        ]);

        $job = Job::create($validatedData);

        return response()->json($job, 201);
    }

    /**
     * Display the specified resource.
     * this function is used to show the details of a specific job
     */
    public function show(string $id)
    {
        $job = Job::findOrFail($id);
        return response()->json($job, 200);
    }

    /**
     * Update the specified resource in storage.
     * this function is used to update a specific job
     */
    public function update(Request $request, string $id)
    {
        $job = Job::findOrFail($id);
        $validatedData = $request->validate([
            'positionName' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'companyLocation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
        ]); // Validate the request data
        $job->update($validatedData);
        return response()->json($job, 200);
    }

    /**
     * Remove the specified resource from storage.
     * this function is used to delete a specific job
     */
    public function destroy(string $id)
    {
        $job = Job::findOrFail($id);
        $job->delete();
        return response()->json(null, 204); // No content
    }
}
