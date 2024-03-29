<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ApplicantVerifyEmailAddress;
use App\Notifications\ApplicantForgotPassword;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AuthApplicantsController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'middleName' => 'nullable|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:users',
            'gender' => 'required|string',
            'contact_number' => 'required|numeric|digits:11',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $adminId = User::where('user_type', 3)->first();

        $user = User::create([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'user_type' => 0,
            'is_active' => 1
        ]);

        Applicant::create([
            'user_id' => $user->id,
            'first_name' => $request->input('firstName'),
            'middle_name' => $request->input('middleName') ?? '',
            'last_name' => $request->input('lastName'),
            'gender' => $request->input('gender'),
            'contact_number' => $request->input('contact_number'),
        ]);

        Notification::create([
            'title' => 'A new account for ' . $request->input('firstName') . ' ' . $request->input('lastName') . ' has been successfully created in our system.',
            'user_id' => array_merge([$adminId->id]),
            'author_id' => $user->id,
            'status' => 0
        ]);

        Notification::create([
            'title' => "Welcome to FAMSI Job Portal! Browse jobs and start applying today. Good luck!",
            'user_id' => array_merge([$user->id]),
            'author_id' => $adminId->id,
            'status' => 0
        ]);

        return response()->json(['message' => 'Applicant registered successfully']);
    }

    // Log in a user
    public function login(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'captcha_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if ($user->user_type === 0 && $user->email_verified_at) {
            // Get the reCAPTCHA token from the request
            $recaptchaToken = $request->input('captcha_token');

            // Verify the reCAPTCHA token
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $recaptchaToken,
            ]);

            // Decode the response JSON
            $data = json_decode($response->body());

            // Check if the reCAPTCHA verification was successful
            if ($data->success) {
                // reCAPTCHA verification successful
                $token = $user->createToken('Applicant Dashboard')->plainTextToken;

                return response()->json(['token' => $token], 200);
            } else {
                // reCAPTCHA verification failed
                return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed'], 422);
            }
        } else if ($user->user_type === 0 && !$user->email_verified_at) {
            return response()->json(['error' => 'You need to verify your email first.', 'title' => "Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.", 'message' => "Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."], 401);
        } else {
            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }
    } else {
        return response()->json(['error' => 'These credentials do not match our records.'], 401);
    }
}

    // Log in using google account
    public function googleLogin(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'family_name' => 'required|string',
        'given_name' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $checkUserEmail = User::where('email', $request->input('email'))->first();

    $user = null;
    if(!$checkUserEmail) {
        $user = User::create([
            'email' => $request->input('email'),
            'password' => Hash::make('12345678'),
            'user_type' => 0,
            'is_active' => 1
        ]);

        $user->markEmailAsVerified();
    
        Applicant::create([
            'user_id' => $user->id,
            'first_name' => $request->input('given_name'),
            'middle_name' => '',
            'last_name' => $request->input('family_name'),
            'gender' => '',
            'contact_number' => '',
        ]);
    }

    $credentials = [
        'email' => $request->input('email'),
        'password' => '12345678',
    ];

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if ($user->user_type === 0 && $user->email_verified_at) {
            $token = $user->createToken('Applicant Dashboard')->plainTextToken;

            return response()->json(['token' => $token], 200);
        } else if ($user->user_type === 0 && !$user->email_verified_at) {
            return response()->json(['error' => 'You need to verify your email first.', 'title' => "Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.", 'message' => "Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."], 401);
        } else {
            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }
    } else {
        return response()->json(['error' => 'These credentials do not match our records.'], 401);
    }
}

    // VerifyEmail
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userVerifyingEmail = User::where('email', $request->input('email'))->first();

           // Check if the user with the provided email exists
        if ($userVerifyingEmail && $userVerifyingEmail->user_type === 0) {
        // Check if the user has already verified their email
        if (!$userVerifyingEmail->hasVerifiedEmail()) {
            // Send the custom verification email
            $userVerifyingEmail->notify(new ApplicantVerifyEmailAddress);

            return response()->json(['message' => 'A verification link has been sent to your email address.']);
        } else {
            return response()->json(['message' => 'The email the you provided is already verified.'], 404);
        }
        } else {
            return response()->json(['message' => 'User not found with the provided email.'], 404);
        }
    }

    // Verify Forgot Password
    public function verifyForgotPass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userVerifyingEmail = User::where('email', $request->input('email'))->first();

            // Check if the user with the provided email exists
        if ($userVerifyingEmail && $userVerifyingEmail->user_type === 0) {
        // Check if the user has already verified their email
        if (!$userVerifyingEmail->hasVerifiedEmail()) {    
            return response()->json(['message' => 'The email the you provided is not yet verified.'], 404);
        } else {
            $token = Str::random(64);
            // Send the custom verification email
            $userVerifyingEmail->notify(new ApplicantForgotPassword($token));

            return response()->json(['message' => 'A password reset link has been sent to your email address.']);
        }
        } else {
            return response()->json(['message' => 'User not found with the provided email.'], 404);
        }
    }

    public function resetPass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'newPass' => 'required|min:8',
            'confirmNewPass' => 'required|same:newPass|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userVerifyingEmail = User::where('email', $request->input('email'))->first();

            // Check if the user with the provided email exists
        if ($userVerifyingEmail && $userVerifyingEmail->user_type === 0) {
            // Update the user's password
            $newPassword = $request->input('newPass');
            $hashedPassword = Hash::make($newPassword);

            $userVerifyingEmail->update([
                'password' => $hashedPassword,
            ]);

            return response()->json(['message' => 'Password updated successfully.'], 200);
        } else {
            return response()->json(['message' => 'The provided email cannot be found.'], 404);
        }
    }

    public function checkEmailVerification(Request $request)
    {
        $data = $request->all();

        $user = User::where('id', $data['id'])->first();

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }


        // Check if the link has expired
        $expires = $request->input('expires');

        if (time() <= $expires) {
            // Link is valid and not expired
            // Perform any additional verification logic here

            // Update the user's email_verified_at field
            $user->markEmailAsVerified();

            $token = $user->createToken('Applicant Dashboard')->plainTextToken;

            return response()->json(['message' => 'Email verification successful.', 'token' => $token], 201);
        } else {
            // Link has expired
            return response()->json(['message' => 'Email verification link has expired.'], 403);
        }
    }

    public function forgotPasswordVerification(Request $request)
    {
        $data = $request->all();

        $user = User::where('id', $data['id'])->first();

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }

    // Log out a user
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }

    // Refresh access token
    public function refresh(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        $newToken = $request->user()->createToken('MyApp')->accessToken;

        return response()->json(['token' => $newToken], 200);
    }

    // Get the authenticated user
    public function details(Request $request)
    {
        return response()->json($request->user());
    }
}
