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

class InProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $applications = Application::query()
        ->where('status', 3)
        ->with(['applicant', 'jobPosition'])
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('applicant', function ($query) use ($search) {
                    $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(middle_name) LIKE LOWER(?)', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                });
                $query->orWhereHas('jobPosition', function ($query) use ($search) {
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
        ->through(fn($application) => [
            'id' => $application->id,
            'first_name' => $application->applicant->first_name,
            'middle_name' => $application->applicant->middle_name,
            'last_name' => $application->applicant->last_name,
            'gender' => $application->applicant->gender,
            'email' => $application->applicant->user->email,
            'contact_number' => $application->applicant->contact_number,
            'is_active' => $application->applicant->user->is_active,
            'created_at' => $application->created_at,
            'file_name' => $application->file_name,
            'file_path' => $application->file_path,
            'job_id' => $application->jobPosition->id,
            'title' => $application->jobPosition->title,
            'location' => $application->jobPosition->location,
            'schedule' => $application->jobPosition->schedule,
            'status' => $application->status
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $applications->currentPage();
        $lastPage = $applications->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $applications->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $applications->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $applications->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $applications->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $applications->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $applications->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $applications->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('InProgress', [
            'applications' => $applications,
            'filters' => $filters,
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
        //
    }

    /**
     * Activate the specified resource.
     */
    public function approve($id)
    {
        $application = Application::findOrFail($id);

        $applicantUser = Applicant::findOrFail($application->applicant_id);

        $jobPosition = JobPosition::findOrFail($application->job_position_id);

        $application->status = 4;

        Facade::message()->send($applicantUser->contact_number, 'Hi, ' . $applicantUser->first_name . '. I hope this message finds you well. Following a thorough review of your requirements, we are pleased to inform you that you have successfully passed this stage of the application process. Your application status is already for deployment and is ready to be hired. Congratulations!');

        $application->save();
    }

    /**
     * Deactivate the specified resource.
     */
    public function disapprove($id)
    {
        $application = Application::findOrFail($id);

        $applicantUser = Applicant::findOrFail($application->applicant_id);

        $jobPosition = JobPosition::findOrFail($application->job_position_id);

        $application->status = 0;

        Facade::message()->send($applicantUser->contact_number, 'Hi, ' . $applicantUser->first_name . '. After a careful review of your requirements, we regret to inform you that we are unable to proceed with your application at this time. We appreciate the effort you put into the application process and value the time you spent discussing your qualifications with us.');

        $application->save();
    }
}
