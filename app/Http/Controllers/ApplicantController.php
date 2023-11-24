<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Applicant;
use App\Models\HrManager;
use App\Models\HrStaff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $applicants = Applicant::query()
        ->with(['user', 'applications'])
        ->whereHas('applications', function ($query) {
            $query->where('status', 5);
        })
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->whereRaw('LOWER(email) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(contact_number) LIKE LOWER(?)', ['%' . $search . '%']);
                })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(middle_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
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
        ->through(fn($applicant) => [
            'id' => $applicant->id,
            'first_name' => $applicant->first_name,
            'middle_name' => $applicant->middle_name,
            'last_name' => $applicant->last_name,
            'gender' => $applicant->gender,
            'email' => $applicant->user->email,
            'email_verified_at' => $applicant->user->email_verified_at,
            'contact_number' => $applicant->contact_number,
            'is_active' => $applicant->user->is_active,
            'created_at' => $applicant->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $applicants->currentPage();
        $lastPage = $applicants->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $applicants->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $applicants->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $applicants->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $applicants->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $applicants->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $applicants->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $applicants->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('HiredEmployees', [
            'applicants' => $applicants,
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
        $applicantValidate = Request::validate([
            'first_name' => ['required', 'max:50'],
            'middle_name' => ['max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required'],
            'email' => ['required', 'max:50', 'email', 'unique:users'],
            'contact_number' => ['required', 'digits:11'],
        ]);

        $user = User::create([
            'email' => $applicantValidate['email'],
            'password' => Hash::make('12345678'),
            'user_type' => 0,
            'is_active' => 1
        ]);

        Applicant::create([
            'user_id' => $user['id'],
            'first_name' => $applicantValidate['first_name'],
            'middle_name' => $applicantValidate['middle_name'],
            'last_name' => $applicantValidate['last_name'],
            'gender' => $applicantValidate['gender'],
            'contact_number' => $applicantValidate['contact_number'],
        ]);

        $userInfo = null;
        $userRole = '';

        if(auth()->user()->user_type === User::ADMIN) {
            $userInfo = Admin::where('user_id', auth()->user()->id)->first();
            $userRole = "Admin";
        } else if(auth()->user()->user_type === User::HR_MANAGER) {
            $userInfo = HrManager::where('user_id', auth()->user()->id)->first();
            $userRole = "Hr Manager";
        } else if(auth()->user()->user_type === User::HR_STAFF) {
            $userInfo = HrStaff::where('user_id', auth()->user()->id)->first();
            $userRole = "Hr Staff";
        } else if(auth()->user()->user_type === User::APPLICANT) {
            $userInfo = Applicant::where('user_id', auth()->user()->id)->first();
            $userRole = "Applicant";
        }

        activity()
        ->performedOn(Applicant::where('user_id', $user['id'])->first())
        ->causedBy(auth()->user())
        ->event('created')
        ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
        ->log("Created an Applicant account with the name of {$applicantValidate['first_name']} {$applicantValidate['last_name']}");
    }

    /**
     * Display the specified resource.
     */
    public function show(Applicant $applicant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Applicant $applicant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $applicantValidate = Request::validate([
            'first_name' => ['required', 'max:50'],
            'middle_name' => ['max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore(Applicant::findOrFail($id)->user->id)],
            'contact_number' => ['required', 'digits:11'],
        ]);

        $applicant = Applicant::findOrFail($id);
        $user = Applicant::findOrFail($id)->user;

        $userInfo = null;
        $userRole = '';

        if(auth()->user()->user_type === User::ADMIN) {
            $userInfo = Admin::where('user_id', auth()->user()->id)->first();
            $userRole = "Admin";
        } else if(auth()->user()->user_type === User::HR_MANAGER) {
            $userInfo = HrManager::where('user_id', auth()->user()->id)->first();
            $userRole = "Hr Manager";
        } else if(auth()->user()->user_type === User::HR_STAFF) {
            $userInfo = HrStaff::where('user_id', auth()->user()->id)->first();
            $userRole = "Hr Staff";
        } else if(auth()->user()->user_type === User::APPLICANT) {
            $userInfo = Applicant::where('user_id', auth()->user()->id)->first();
            $userRole = "Applicant";
        }

        if($applicantValidate['first_name'] !== $applicant->first_name) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's first name from {$applicant->first_name} to {$applicantValidate['first_name']}");

            $applicant->first_name = $applicantValidate['first_name'];
        }

        if($applicantValidate['middle_name'] !== $applicant->middle_name) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's middle name from {$applicant->middle_name} to {$applicantValidate['middle_name']}");

            $applicant->middle_name = $applicantValidate['middle_name'];
        }

        if($applicantValidate['last_name'] !== $applicant->last_name) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's last name from {$applicant->last_name} to {$applicantValidate['last_name']}");

            $applicant->last_name = $applicantValidate['last_name'];
        }

        if($applicantValidate['gender'] !== $applicant->gender) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's gender from {$applicant->gender} to {$applicantValidate['gender']}");

            $applicant->gender = $applicantValidate['gender'];
        }

        if($applicantValidate['email'] !== $user->email) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's email from {$user->email} to {$applicantValidate['email']}");

            $user->email = $applicantValidate['email'];
        }

        if($applicantValidate['contact_number'] !== $applicant->contact_number) {
            activity()
            ->performedOn(Applicant::where('user_id', $user['id'])->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Applicant account's contact number from {$applicant->contact_number} to {$applicantValidate['contact_number']}");

            $applicant->contact_number = $applicantValidate['contact_number'];
        }

        $user->save();
        $applicant->save();
    }

    /**
     * Activate the specified resource.
     */
    public function activate($id)
    {
        $user = Applicant::findOrFail($id)->user;

        $user->is_active = 1;

        $user->save();
    }

    /**
     * Deactivate the specified resource.
     */
    public function deactivate($id)
    {
        $user = Applicant::findOrFail($id)->user;

        $user->is_active = 0;

        $user->save();
    }
}
