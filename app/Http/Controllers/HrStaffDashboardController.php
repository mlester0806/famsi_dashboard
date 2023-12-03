<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\HrManager;
use App\Models\HrStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Twilio\Rest\Client;

class HrStaffDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $events = [];
        $authUser = HrStaff::where('user_id', auth()->user()->id)->first();
        $applicants = Application::where('status', 2)
        ->with(['applicant'])->get();
        $appointments = Appointment::with(['applicant', 'hrStaff'])
        ->where('interviewer_id', $authUser->id)
        ->get();

        foreach ($appointments as $appointment) {
            $events[] = [
                'id' => $appointment->id,
                'title' => $appointment->comments,
                'applicant_id' => $appointment->applicant_id,
                'start' => $appointment->start_time,
                'end' => $appointment->finish_time,
                'backgroundColor' => $appointment->finish_time <= now() ? 'green' : 'rgb(55, 136, 216)'
            ];
        }

        return Inertia::render('HRStaffDashboard', [
            'events' => $events,
            'applicants' => $applicants
        ]);
    }

    public function store()
    {
        $scheduleValidate = Request::validate([
            'startTimeDate' => ['required', 'date', 'before:endTimeDate'],
            'endTimeDate' => ['required', 'date', 'after:startTimeDate', 'different:startTimeDate'],
            'title' => ['required', 'max:50'],
            'applicant' => ['required']
        ], [
            'startTimeDate.before' => 'The start time is later than end time date.',
            'endTimeDate.after' => 'The end time is earlier than start time date.',
            'endTimeDate.different' => 'The end time must be different from the start time.',
        ]);

        $authUser = HrStaff::where('user_id', auth()->user()->id)->first();

        $startDateTime = Carbon::createFromFormat('Y-m-d\TH:i:sP', $scheduleValidate['startTimeDate']);
        $endDateTime = Carbon::createFromFormat('Y-m-d\TH:i:sP', $scheduleValidate['endTimeDate']);

        Appointment::create([
            'start_time' => $startDateTime,
            'finish_time' => $endDateTime,
            'comments' => $scheduleValidate['title'],
            'applicant_id' => $scheduleValidate['applicant'],
            'interviewer_id' => $authUser->id
        ]);

        // Your Account SID and Auth Token from twilio.com/console
        // $sid    = env("TWILIO_SID");
        // $token  = env("TWILIO_TOKEN");
        // $twilio = new Client($sid, $token);

        // $message = $twilio->messages
        //                   ->create(
        //                       "+639763386980", // Text this number
        //                       [
        //                           "body" => "Hello applicant! Thanks for your application.",
        //                           "from" => env("TWILIO_FROM") // Your Twilio number
        //                       ]
        //                   );

        return redirect()->back();
    }

    public function update($id)
    {
        $scheduleValidate = Request::validate([
            'startTimeDate' => ['required', 'date', 'before:endTimeDate'],
            'endTimeDate' => ['required', 'date', 'after:startTimeDate', 'different:startTimeDate'],
            'title' => ['required', 'max:50'],
            'applicant' => ['required']
        ], [
            'startTimeDate.before' => 'The start time is later than end time date.',
            'endTimeDate.after' => 'The end time is earlier than start time date.',
            'endTimeDate.different' => 'The end time must be different from the start time.',
        ]);

        $schedule = Appointment::findOrFail($id);
        $newApplicant = Applicant::findOrFail($scheduleValidate['applicant']);

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

        if($scheduleValidate['startTimeDate'] !== $schedule->start_time) {
            $startDateTime = Carbon::createFromFormat('Y-m-d\TH:i:sP', $scheduleValidate['startTimeDate']);
            $startDateTime->setTimezone('Asia/Manila');

            activity()
            ->performedOn(Appointment::where('id', $id)->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Schedule's start time date from {$schedule->start_time} to {$startDateTime}");

            $schedule->start_time = $startDateTime;
        }

        if($scheduleValidate['endTimeDate'] !== $schedule->finish_time) {
            $endDateTime = Carbon::createFromFormat('Y-m-d\TH:i:sP', $scheduleValidate['endTimeDate']);
            $endDateTime->setTimezone('Asia/Manila');

            activity()
            ->performedOn(Appointment::where('id', $id)->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Schedule's end time date from {$schedule->finish_time} to {$endDateTime}");

            $schedule->finish_time = $endDateTime;
        }

        if($scheduleValidate['title'] !== $schedule->comments) {
            activity()
            ->performedOn(Appointment::where('id', $id)->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Schedule's title from {$schedule->comments} to {$scheduleValidate['title']}");

            $schedule->comments = $scheduleValidate['title'];
        }

        if($scheduleValidate['applicant'] !== $schedule->applicant_id) {
            activity()
            ->performedOn(Appointment::where('id', $id)->first())
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['ipAddress' => request()->ip(), 'user' => $userInfo->first_name . ' ' . $userInfo->last_name, 'role' => $userRole])
            ->log("Updated a Schedule's applicant from {$schedule->applicant->first_name} {$schedule->applicant->middle_name} {$schedule->applicant->last_name} to {$newApplicant->first_name} {$newApplicant->middle_name} {$newApplicant->last_name}");

            $schedule->applicant_id = $scheduleValidate['applicant'];
        }

        $schedule->save();

        return redirect()->back();
    }


    public function delete($id)
    {
        $appointment = Appointment::find($id);

        $appointment->delete();

        return redirect()->back();
    }
}
