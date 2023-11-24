<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Application;
use App\Models\HrManager;
use App\Models\HrStaff;
use App\Models\User;
use App\Models\JobPosition;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class HiredController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $jobPositions = JobPosition::all();

        $hired = Application::query()
        ->where('status', 5)
        ->with(['applicant', 'jobPosition'])
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('applicant', function ($query) use ($search) {
                    $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(middle_name) LIKE LOWER(?)', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                });
                $query->whereHas('job_position', function ($query) use ($search) {
                    $query->whereRaw('LOWER(title) LIKE LOWER(?)', ['%' . $search . '%']);
                });
            });

            // Use this if we like to perform a case-insensitive and approximate matching search

            // $query->where(function ($query) use ($search) {
            //     $lowerSearch = strtolower($search);
            //     $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
            //           ->orWhereRaw('SOUNDEX(last_name) = SOUNDEX(?)', [$lowerSearch]);
            // })
            // ->orWhere(function ($query) use ($search) {
            //     $lowerSearch = strtolower($search);
            //     $query->whereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%'])
            //           ->orWhereRaw('SOUNDEX(first_name) = SOUNDEX(?)', [$lowerSearch]);
            // });
        })
        ->orderBy('id', 'asc')
        ->paginate(10)
        ->withQueryString()
        ->through(fn($hire) => [
            'id' => $hire->id,
            'first_name' => $hire->applicant->first_name,
            'middle_name' => $hire->applicant->middle_name,
            'last_name' => $hire->applicant->last_name,
            'gender' => $hire->applicant->gender,
            'email' => $hire->applicant->user->email,
            'contact_number' => $hire->applicant->contact_number,
            'is_active' => $hire->applicant->user->is_active,
            'created_at' => $hire->created_at,
            'file_name' => $hire->file_name,
            'file_path' => $hire->file_path,
            'job_id' => $hire->jobPosition->id,
            'title' => $hire->jobPosition->title,
            'location' => $hire->jobPosition->location,
            'schedule' => $hire->jobPosition->schedule,
            'status' => $hire->status
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $hired->currentPage();
        $lastPage = $hired->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $hired->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $hired->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $hired->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $hired->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $hired->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $hired->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $hired->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('Hired', [
            'hired' => $hired,
            'filters' => $filters,
            'jobPositions' => $jobPositions,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
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
        $applicantValidate = Request::validate([
            'job_id' => ['required'],
        ]);

        $application = Application::findOrFail($id);
        $jobPosition = JobPosition::findOrFail($applicantValidate['job_id']);

        $application->job_position_id = $applicantValidate['job_id'];

        $application->save();
    }

}
