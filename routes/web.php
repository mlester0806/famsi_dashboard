<?php

use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\HrManagerController;
use App\Http\Controllers\HrStaffController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserApplicantController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\CompanyAssignmentController;
use App\Http\Controllers\DisqualifiedController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\HiredController;
use App\Http\Controllers\ResignedController;
use App\Http\Controllers\HrStaffDashboardController;
use App\Http\Controllers\HrManagerDashboardController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\QualifiedController;
use App\Http\Controllers\ForInterviewController;
use App\Http\Controllers\InProgressController;
use App\Http\Controllers\HrManagerApplicationController;
use App\Http\Controllers\ReportController;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\HrManager;
use App\Models\HrStaff;
use App\Models\Qualification;
use App\Models\Benefit;
use App\Models\CompanyAssignment;
use App\Models\JobType;
use App\Models\EmployeeType;
use App\Models\Industry;
use App\Models\JobPosition;
use App\Models\Document;
use Spatie\Activitylog\Models\Activity;
// use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
})->middleware('guest');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::group(['middleware' => 'role:applicant', 'prefix' => 'applicant', 'as' => 'applicant.'], function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
    });

    Route::group(['middleware' => 'role:hr-staff', 'prefix' => 'hr-staff', 'as' => 'hr-staff.'], function () {
        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function() {
            Route::get('/', function () {
                return Inertia::render('Dashboard', [
                    'totalUserApplicants' => Applicant::count(),
                    'totalDisqualified' => Application::where('status', 0)->count(),
                    'totalApplications' => Application::where('status', 1)->count(),
                    'totalForInterview' => Application::where('status', 2)->count(),
                    'totalInProgress' => Application::where('status', 3)->count(),
                    'totalQualified' => Application::where('status', 4)->count(),
                    'totalHired' => Application::where('status', 5)->count(),
                ]);
            })->name('index');
        });

        Route::group(['prefix' => 'appointments', 'as' => 'appointments.'], function() {
            Route::get('/', HrStaffDashboardController::class)->name('index');

            Route::post('/store', [HrStaffDashboardController::class, 'store'])->name('store');

            Route::put('/update/{id}', [HrStaffDashboardController::class, 'update'])->name('update');

            Route::delete('/delete/{id}', [HrStaffDashboardController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'applicants', 'as' => 'applicants.'], function() {
            Route::get('/', [UserApplicantController::class, 'index'])->name('index');

            Route::post('/store', [UserApplicantController::class, 'store'])->name('store');

            Route::put('/update/{id}', [UserApplicantController::class, 'update'])->name('update');

            Route::put('/activate/{id}', [UserApplicantController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [UserApplicantController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'applications', 'as' => 'applications.'], function() {
            Route::get('/', [ApplicationController::class, 'index'])->name('index');

            Route::post('/store', [ApplicationController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [ApplicationController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [ApplicationController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'for-interview', 'as' => 'for-interview.'], function() {
            Route::get('/', [ForInterviewController::class, 'index'])->name('index');

            Route::post('/store', [ForInterviewController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [ForInterviewController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [ForInterviewController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'in-progress', 'as' => 'in-progress.'], function() {
            Route::get('/', [InProgressController::class, 'index'])->name('index');

            Route::post('/store', [InProgressController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [InProgressController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [InProgressController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'qualified', 'as' => 'qualified.'], function() {
            Route::get('/', [QualifiedController::class, 'index'])->name('index');

            Route::put('/hire/{id}', [QualifiedController::class, 'hire'])->name('hire');

            Route::put('/disapprove/{id}', [QualifiedController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'disqualified', 'as' => 'disqualified.'], function() {
            Route::get('/', [DisqualifiedController::class, 'index'])->name('index');

            Route::put('/approve/{id}', [DisqualifiedController::class, 'approve'])->name('approve');
        });

        Route::group(['prefix' => 'hired', 'as' => 'hired.'], function() {
            Route::get('/', [HiredController::class, 'index'])->name('index');

            Route::put('/resign/{id}', [HiredController::class, 'resign'])->name('resign');

            Route::put('/update/{id}', [HiredController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'resigned', 'as' => 'resigned.'], function() {
            Route::get('/', [ResignedController::class, 'index'])->name('index');

            Route::put('/update/{id}', [ResignedController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'documents', 'as' => 'documents.'], function() {
            Route::get('/', [DocumentsController::class, 'index'])->name('index');

            Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');

            Route::post('/upload-revert', [DocumentsController::class, 'uploadRevert'])->name('upload-revert');

            Route::post('/store', [DocumentsController::class, 'store'])->name('store');

            Route::put('/update/{id}', [DocumentsController::class, 'update'])->name('update');

            Route::delete('/destroy/{id}', [DocumentsController::class, 'destroy'])->name('destroy');
        });
    });

    Route::group(['middleware' => 'role:hr-manager', 'prefix' => 'hr-manager', 'as' => 'hr-manager.'], function () {
        Route::group(['prefix' => 'reports', 'as' => 'reports.'], function() {
            Route::get('/', [ReportController::class, 'index'])->name('index');
        });

        Route::group(['prefix' => 'appointments', 'as' => 'appointments.'], function() {
            Route::get('/', HrManagerDashboardController::class)->name('index');

            Route::post('/store', [HrManagerDashboardController::class, 'store'])->name('store');

            Route::put('/update/{id}', [HrManagerDashboardController::class, 'update'])->name('update');

            Route::delete('/delete/{id}', [HrManagerDashboardController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'hr-staffs', 'as' => 'hr-staffs.'], function() {
            Route::get('/', [HrStaffController::class, 'index'])->name('index');

            Route::post('/store', [HrStaffController::class, 'store'])->name('store');

            Route::put('/update/{id}', [HrStaffController::class, 'update'])->name('update');

            Route::put('/activate/{id}', [HrStaffController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [HrStaffController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'applicants', 'as' => 'applicants.'], function() {
            Route::get('/', [UserApplicantController::class, 'index'])->name('index');

            Route::post('/store', [UserApplicantController::class, 'store'])->name('store');

            Route::put('/update/{id}', [UserApplicantController::class, 'update'])->name('update');

            Route::put('/activate/{id}', [UserApplicantController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [UserApplicantController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'applications', 'as' => 'applications.'], function() {
            Route::get('/', [HrManagerApplicationController::class, 'index'])->name('index');

            Route::post('/store', [HrManagerApplicationController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [HrManagerApplicationController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [HrManagerApplicationController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'for-interview', 'as' => 'for-interview.'], function() {
            Route::get('/', [ForInterviewController::class, 'index'])->name('index');

            Route::post('/store', [ForInterviewController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [ForInterviewController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [ForInterviewController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'in-progress', 'as' => 'in-progress.'], function() {
            Route::get('/', [InProgressController::class, 'index'])->name('index');

            Route::post('/store', [InProgressController::class, 'store'])->name('store');

            Route::put('/approve/{id}', [InProgressController::class, 'approve'])->name('approve');

            Route::put('/disapprove/{id}', [InProgressController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'qualified', 'as' => 'qualified.'], function() {
            Route::get('/', [QualifiedController::class, 'index'])->name('index');

            Route::put('/hire/{id}', [QualifiedController::class, 'hire'])->name('hire');

            Route::put('/disapprove/{id}', [QualifiedController::class, 'disapprove'])->name('disapprove');
        });

        Route::group(['prefix' => 'disqualified', 'as' => 'disqualified.'], function() {
            Route::get('/', [DisqualifiedController::class, 'index'])->name('index');

            Route::put('/approve/{id}', [DisqualifiedController::class, 'approve'])->name('approve');
        });

        Route::group(['prefix' => 'hired', 'as' => 'hired.'], function() {
            Route::get('/', [HiredController::class, 'index'])->name('index');
            
            Route::put('/resign/{id}', [HiredController::class, 'resign'])->name('resign');

            Route::put('/update/{id}', [HiredController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'resigned', 'as' => 'resigned.'], function() {
            Route::get('/', [ResignedController::class, 'index'])->name('index');

            Route::put('/update/{id}', [ResignedController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'documents', 'as' => 'documents.'], function() {
            Route::get('/', [DocumentsController::class, 'index'])->name('index');

            Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');

            Route::post('/upload-revert', [DocumentsController::class, 'uploadRevert'])->name('upload-revert');

            Route::post('/store', [DocumentsController::class, 'store'])->name('store');

            Route::put('/update/{id}', [DocumentsController::class, 'update'])->name('update');

            Route::delete('/destroy/{id}', [DocumentsController::class, 'destroy'])->name('destroy');
        });
    });

    Route::group(['middleware' => 'role:admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function() {
            Route::get('/', function () {
                return Inertia::render('Dashboard', [
                    'totalApplicants' => Application::where('status', 3)->count(),
                    'totalHrStaffs' => HrStaff::count(),
                    'totalHrManagers' => HrManager::count(),
                    'totalQualifications' => Qualification::count(),
                    'totalBenefits' => Benefit::count(),
                    'totalCompanyAssignments' => CompanyAssignment::count(),
                    'totalJobTypes' => JobType::count(),
                    'totalEmployeeTypes' => EmployeeType::count(),
                    'totalIndustries' => Industry::count(),
                    'totalJobPositions' => JobPosition::count(),
                    'totalActivityLogs' => Activity::count(),
                ]);
            })->name('index');
        });

        Route::group(['prefix' => 'qualifications', 'as' => 'qualifications.'], function() {
            Route::get('/', [QualificationController::class, 'index'])->name('index');

            Route::post('/store', [QualificationController::class, 'store'])->name('store');

            Route::put('/update/{id}', [QualificationController::class, 'update'])->name('update');

            
            Route::put('/activate/{id}', [QualificationController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [QualificationController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'benefits', 'as' => 'benefits.'], function() {
            Route::get('/', [BenefitController::class, 'index'])->name('index');

            Route::post('/store', [BenefitController::class, 'store'])->name('store');

            Route::put('/update/{id}', [BenefitController::class, 'update'])->name('update');

            
            Route::put('/activate/{id}', [BenefitController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [BenefitController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'company-assignments', 'as' => 'company-assignments.'], function() {
            Route::get('/', [CompanyAssignmentController::class, 'index'])->name('index');

            Route::post('/store', [CompanyAssignmentController::class, 'store'])->name('store');

            Route::put('/update/{id}', [CompanyAssignmentController::class, 'update'])->name('update');

            
            Route::put('/activate/{id}', [CompanyAssignmentController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [CompanyAssignmentController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'job-types', 'as' => 'job-types.'], function() {
            Route::get('/', [JobTypeController::class, 'index'])->name('index');

            Route::post('/store', [JobTypeController::class, 'store'])->name('store');

            Route::put('/update/{id}', [JobTypeController::class, 'update'])->name('update');

            
            Route::put('/activate/{id}', [JobTypeController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [JobTypeController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'employee-types', 'as' => 'employee-types.'], function() {
            Route::get('/', [EmployeeTypeController::class, 'index'])->name('index');

            Route::post('/store', [EmployeeTypeController::class, 'store'])->name('store');

            Route::put('/update/{id}', [EmployeeTypeController::class, 'update'])->name('update');

            Route::put('/activate/{id}', [EmployeeTypeController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [EmployeeTypeController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'industries', 'as' => 'industries.'], function() {
            Route::get('/', [IndustryController::class, 'index'])->name('index');

            Route::post('/store', [IndustryController::class, 'store'])->name('store');

            Route::put('/update/{id}', [IndustryController::class, 'update'])->name('update');

            
            Route::put('/activate/{id}', [IndustryController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [IndustryController::class, 'deactivate'])->name('deactivate');
        });

        Route::group(['prefix' => 'job-positions', 'as' => 'job-positions.'], function() {
            Route::get('/', [JobPositionController::class, 'index'])->name('index');

            Route::post('/store', [JobPositionController::class, 'store'])->name('store');

            Route::put('/update/{id}', [JobPositionController::class, 'update'])->name('update');

                        
            Route::put('/activate/{id}', [JobPositionController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [JobPositionController::class, 'deactivate'])->name('deactivate');
        });

        // Route::get('/landing-page', function () {
        //     return Inertia::render('LandingPage');
        // })->name('landingpage');

        // Route::get('/mainpage', function () {
        //     return Inertia::render('MainPage');
        // })->name('mainpage');

            Route::group(['prefix' => 'activity-logs', 'as' => 'activity-logs.'], function () {
                Route::get('/', [ActivityLogsController::class, 'index'])->name('index');

                Route::delete('/destroy/{id}', [ActivityLogsController::class, 'destroy'])->name('destroy');
            });

            Route::group(['prefix' => 'hr-managers', 'as' => 'hr-managers.'], function () {
                Route::get('/', [HrManagerController::class, 'index'])->name('index');

                Route::post('/store', [HrManagerController::class, 'store'])->name('store');

                Route::put('/update/{id}', [HrManagerController::class, 'update'])->name('update');

                Route::put('/activate/{id}', [HrManagerController::class, 'activate'])->name('activate');

                Route::put('/deactivate/{id}', [HrManagerController::class, 'deactivate'])->name('deactivate');
            });

            Route::group(['prefix' => 'hr-staffs', 'as' => 'hr-staffs.'], function() {
                Route::get('/', [HrStaffController::class, 'index'])->name('index');

                Route::post('/store', [HrStaffController::class, 'store'])->name('store');

                Route::put('/update/{id}', [HrStaffController::class, 'update'])->name('update');

                Route::put('/activate/{id}', [HrStaffController::class, 'activate'])->name('activate');

                Route::put('/deactivate/{id}', [HrStaffController::class, 'deactivate'])->name('deactivate');
            });

            Route::group(['prefix' => 'applicants', 'as' => 'applicants.'], function() {
                Route::get('/', [ApplicantController::class, 'index'])->name('index');

                Route::post('/store', [ApplicantController::class, 'store'])->name('store');

                Route::put('/update/{id}', [ApplicantController::class, 'update'])->name('update');

                Route::put('/activate/{id}', [ApplicantController::class, 'activate'])->name('activate');

                Route::put('/deactivate/{id}', [ApplicantController::class, 'deactivate'])->name('deactivate');
            });

            Route::group(['prefix' => 'qualified', 'as' => 'qualified.'], function() {
                Route::get('/', [QualifiedController::class, 'index'])->name('index');

                Route::put('/hire/{id}', [QualifiedController::class, 'hire'])->name('hire');

                Route::put('/disapprove/{id}', [QualifiedController::class, 'disapprove'])->name('disapprove');
            });

            Route::group(['prefix' => 'disqualified', 'as' => 'disqualified.'], function() {
                Route::get('/', [DisqualifiedController::class, 'index'])->name('index');

                Route::put('/approve/{id}', [DisqualifiedController::class, 'approve'])->name('approve');
            });

            Route::group(['prefix' => 'hired', 'as' => 'hired.'], function() {
                Route::get('/', [HiredController::class, 'index'])->name('index');
            });

            Route::group(['prefix' => 'applications', 'as' => 'applications.'], function() {
                Route::get('/', [ApplicationController::class, 'index'])->name('index');

                Route::put('/approve/{id}', [ApplicationController::class, 'approve'])->name('approve');

                Route::put('/disapprove/{id}', [ApplicationController::class, 'disapprove'])->name('disapprove');
            });

            Route::group(['prefix' => 'documents', 'as' => 'documents.'], function() {
                Route::get('/', [DocumentsController::class, 'index'])->name('index');

                Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');

                Route::post('/upload-revert', [DocumentsController::class, 'uploadRevert'])->name('upload-revert');

                Route::post('/store', [DocumentsController::class, 'store'])->name('store');

                Route::put('/update/{id}', [DocumentsController::class, 'update'])->name('update');

                Route::delete('/destroy/{id}', [DocumentsController::class, 'destroy'])->name('destroy');
            });

    });
});

require_once __DIR__ . '/jetstream.php';
