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

class ResignedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $jobPositions = JobPosition::all();

        $resigned = Application::query()
        ->where('status', 6)
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
        ->through(fn($resign) => [
            'id' => $resign->id,
            'first_name' => $resign->applicant->first_name,
            'middle_name' => $resign->applicant->middle_name,
            'last_name' => $resign->applicant->last_name,
            'gender' => $resign->applicant->gender,
            'email' => $resign->applicant->user->email,
            'contact_number' => $resign->applicant->contact_number,
            'is_active' => $resign->applicant->user->is_active,
            'created_at' => $resign->created_at,
            'file_name' => $resign->file_name,
            'file_path' => $resign->file_path,
            'job_id' => $resign->jobPosition->id,
            'title' => $resign->jobPosition->title,
            'location' => $resign->jobPosition->location,
            'schedule' => $resign->jobPosition->schedule,
            'status' => $resign->status
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $resigned->currentPage();
        $lastPage = $resigned->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $resigned->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $resigned->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $resigned->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $resigned->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $resigned->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $resigned->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $resigned->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('Resigned', [
            'resigned' => $resigned,
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
