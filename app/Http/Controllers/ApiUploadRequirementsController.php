<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Models\User;
use App\Models\Applicant;
use Illuminate\Support\Facades\Auth;

class ApiUploadRequirementsController extends Controller
{
    public function uploadRequirements (Request $request) {
        $applicationValidate = $request->validate([
            'applicant_id' => ['required'],
            'job_position_id' => ['required'],
        ]);

        $resumeFile = $request->all();

        $files = collect($request->keys())
        ->filter(function ($key) {
            return preg_match('/^(file_name|file_path)_\d+$/', $key);
        })
        ->mapWithKeys(function ($key) use ($request) {
            $index = substr($key, strlen('file_name_'));

            $fileName = $request->file("file_path_{$index}")->getClientOriginalName();

            $storedFilePath = $request->file("file_path_{$index}")->store('public');

            $path = storage_path('app/' . $storedFilePath);

            $cloudPath = Storage::disk('spaces')->putFileAs('uploads/applications/' . $request->input("applicant_id"), $path, $fileName);

            unlink($path);

            return [
                "file_name_{$index}" => $request->input("file_name_{$index}"),
                "file_path_{$index}" => env('DO_URL') . '/' . $cloudPath
            ];
        })
        ->toArray();

        $application = Application::where('applicant_id', $applicationValidate['applicant_id'])->first();

        $application->additional_files = $files;

        $application->save();

        $employeeIds = User::whereIn('user_type', [1, 2])->pluck('id')->toArray();

        $applicant = Applicant::where('id', $applicationValidate['applicant_id'])->first();

        $user = User::where('id', $applicant->user_id)->first();

        $notification = Notification::create([
            'title' => $applicant->first_name . ' ' . $applicant->last_name . ' has already submitted its requirements for the job application. Please take a look.',
            'user_id' => $employeeIds,
            'author_id' => $user->id,
            'status' => 0
        ]);

        // $fileName = $resumeFile->getClientOriginalName();

        // $storedFilePath = $resumeFile->store('public');

        // $path = storage_path('app/' . $storedFilePath);

        // $cloudPath = Storage::disk('spaces')->putFileAs('uploads/applications/' . $applicationValidate['applicant_id'], $path, $fileName);

        // $application = Application::create([
        //     'applicant_id' => $applicationValidate['applicant_id'],
        //     'job_position_id' => $applicationValidate['job_position_id'],
        //     'file_name' => $fileName,
        //     'file_path' => env('DO_URL') . '/' . $cloudPath,
        //     'status' => 1
        // ]);

        // unlink($path);

        // $employeeIds = User::whereIn('user_type', [1, 2])->pluck('id')->toArray();

        // $applicant = Applicant::where('id', $applicationValidate['applicant_id'])->first();

        // $user = User::where('id', $applicant->user_id)->first();

        // $notification = Notification::create([
        //     'title' => $applicant->first_name . ' ' . $applicant->last_name . ', a new applicant, has successfully applied for a job. Please take a look.',
        //     'user_id' => $employeeIds,
        //     'author_id' => $user->id,
        //     'status' => 0
        // ]);

        return response()->json(['success' => 'You successfully applied for this job. Please wait for the result of your application process. Thank you!'], 200);
    }


}
