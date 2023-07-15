<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class HrStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $hrStaffs = HrStaff::query()
        ->with('user')
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
        ->through(fn($hrStaff) => [
            'id' => $hrStaff->id,
            'first_name' => $hrStaff->first_name,
            'middle_name' => $hrStaff->middle_name,
            'last_name' => $hrStaff->last_name,
            'gender' => $hrStaff->gender,
            'email' => $hrStaff->user->email,
            'contact_number' => $hrStaff->contact_number,
            'is_active' => $hrStaff->user->is_active,
            'created_at' => $hrStaff->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $hrStaffs->currentPage();
        $lastPage = $hrStaffs->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $hrStaffs->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $hrStaffs->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $hrStaffs->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $hrStaffs->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $hrStaffs->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $hrStaffs->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $hrStaffs->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('HRStaffs', [
            'hrStaffs' => $hrStaffs,
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
        $hrStaffValidate = Request::validate([
            'first_name' => ['required', 'max:50'],
            'middle_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required'],
            'email' => ['required', 'max:50', 'email'],
            'contact_number' => ['required', 'digits:11'],
        ]);

        $user = User::create([
            'email' => $hrStaffValidate['email'],
            'password' => Hash::make('12345678'),
            'user_type' => 2,
            'is_active' => 1
        ]);

        HrStaff::create([
            'user_id' => $user['id'],
            'first_name' => $hrStaffValidate['first_name'],
            'middle_name' => $hrStaffValidate['middle_name'],
            'last_name' => $hrStaffValidate['last_name'],
            'gender' => $hrStaffValidate['gender'],
            'contact_number' => $hrStaffValidate['contact_number'],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(HrStaff $hrStaff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HrStaff $hrStaff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $hrStaffValidate = Request::validate([
            'first_name' => ['required', 'max:50'],
            'middle_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required'],
            'email' => ['required', 'max:50', 'email'],
            'contact_number' => ['required', 'digits:11'],
        ]);

        $hrStaff = HrStaff::findOrFail($id);
        $user = HrStaff::findOrFail($id)->user;

        $hrStaff->first_name = $hrStaffValidate['first_name'];
        $hrStaff->middle_name = $hrStaffValidate['middle_name'];
        $hrStaff->last_name = $hrStaffValidate['last_name'];
        $hrStaff->gender = $hrStaffValidate['gender'];
        $user->email = $hrStaffValidate['email'];
        $hrStaff->contact_number = $hrStaffValidate['contact_number'];

        $user->save();
        $hrStaff->save();
    }

    /**
     * Activate the specified resource.
     */
    public function activate($id)
    {
        $user = HrStaff::findOrFail($id)->user;

        $user->is_active = 1;

        $user->save();
    }

    /**
     * Deactivate the specified resource.
     */
    public function deactivate($id)
    {
        $user = HrStaff::findOrFail($id)->user;

        $user->is_active = 0;

        $user->save();
    }
}
