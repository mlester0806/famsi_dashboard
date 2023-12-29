<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Applicant;
use App\Models\JobPosition;
use App\Models\HrManager;
use App\Models\HrStaff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Humans\Semaphore\Laravel\Facade;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['type', 'month', 'year']);
        $reportTypeReq = Request::input('type');
        $selectMonthReq = Request::input('month');
        $selectYearReq = Request::input('year');

        $currentYear = $selectYearReq ?? now()->year;

        $monthNumber = Carbon::parse("{$selectMonthReq} 1")->month;

        $query = Application::query();

        if ($selectMonthReq) {
            // Use Carbon to get the numerical representation of the month
            $monthNumber = Carbon::parse("{$selectMonthReq} 1")->month;
        
            // Add whereMonth condition
            $query->whereMonth('created_at', $monthNumber);
        }

        if ($selectYearReq) {
            // Add whereYear condition
            $query->whereYear('created_at', $selectYearReq);
        }
        
        // Execute the query
        $applications = $query->get();
        
        $overallReports = [$applications->count()];

        // Initialize an array to store the counts for each month
        $monthlyCounts = array_fill(0, 12, 0);

        // Iterate through applications and update the counts
        foreach ($applications as $application) {
            // Assuming 'created_at' is the date column; adjust accordingly
            $month = $application->created_at->month;
            
            // Increment the count for the corresponding month
            $monthlyCounts[$month - 1]++;
        }

        $dailyCounts = array_fill(0, 31, 0);

        // Iterate through applications and update the counts
        foreach ($applications as $application) {
            $day = $application->created_at->day;

            $dailyCounts[$day - 1]++;
        }

        return Inertia::render('Report', [
            'overallReports' => $overallReports,
            'monthlyReports' => $monthlyCounts,
            'dailyReports' => $dailyCounts,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        //
    }
}
