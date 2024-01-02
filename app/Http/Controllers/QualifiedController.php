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

class QualifiedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $jobPositions = JobPosition::all();

        $qualified = Application::query()
        ->where('status', 4)
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
        ->through(fn($qualify) => [
            'id' => $qualify->id,
            'first_name' => $qualify->applicant->first_name,
            'middle_name' => $qualify->applicant->middle_name,
            'last_name' => $qualify->applicant->last_name,
            'gender' => $qualify->applicant->gender,
            'email' => $qualify->applicant->user->email,
            'contact_number' => $qualify->applicant->contact_number,
            'is_active' => $qualify->applicant->user->is_active,
            'created_at' => $qualify->created_at,
            'file_name' => $qualify->file_name,
            'file_path' => $qualify->file_path,
            'job_id' => $qualify->jobPosition->id,
            'title' => $qualify->jobPosition->title,
            'location' => $qualify->jobPosition->location,
            'schedule' => $qualify->jobPosition->schedule,
            'status' => $qualify->status
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $qualified->currentPage();
        $lastPage = $qualified->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $qualified->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $qualified->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $qualified->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $qualified->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $qualified->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $qualified->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $qualified->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('Qualified', [
            'qualified' => $qualified,
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


    /**
     * Deactivate the specified resource.
     */
    public function disapprove($id)
    {
        $qualified = Application::findOrFail($id);

        $applicantUser = Applicant::findOrFail($qualified->applicant_id);

        $jobPosition = JobPosition::findOrFail($qualified->job_position_id);

        $qualified->status = 0;

        Facade::message()->send($applicantUser->contact_number, 'Hi, ' . $applicantUser->first_name . '. Thank you for your interest in the ' . $jobPosition->title . ' role. I appreciate your time and effort during the interview process. After careful consideration, we regret to inform you that we will not be moving forward with your application. Thank you for your interest, and best of luck in your job search.');

        $qualified->save();
    }

    public function hire($id)
    {
        $hire = Application::findOrFail($id);

        $applicantUser = Applicant::findOrFail($hire->applicant_id);

        $jobPosition = JobPosition::findOrFail($hire->job_position_id);

        $hire->status = 5;

        Facade::message()->send($applicantUser->contact_number, 'Hi, ' . $applicantUser->first_name . '. We are thrilled to officially welcome you to our company! Following a comprehensive review of your resume and a successful interview process, we are delighted to inform you that you have been selected for the position of ' . $jobPosition->title .'. We look forward to working together and achieving great success!');

        $hire->save();
    }
}
