<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\FrogotpasswordOTPsend\SendPasswordChangeConfirmation;
use App\Repositories\All\ComOrganization\ComOrganizationInterface;
use App\Repositories\All\User\UserInterface;
use App\Services\OrganizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    protected $userInterface;
    protected $comOrganizationInterface;
    protected $organizationService;

    public function __construct(UserInterface $userInterface, ComOrganizationInterface $comOrganizationInterface, OrganizationService $organizationService)
    {
        $this->userInterface            = $userInterface;
        $this->comOrganizationInterface = $comOrganizationInterface;
        $this->organizationService      = $organizationService;
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $this->userInterface->findByColumn(['email' => $request->email]);

        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $otp = rand(100000, 999999);

        $user->otp            = $otp;
        $user->otp_expires_at = Carbon::now('UTC')->addMinutes(5);

        $user->save();
        try {
            $organization = $this->comOrganizationInterface->first();

            if ($organization) {
                $organizationName        = $organization->organizationName;
                $organizationFactoryName = $organization->organizationFactoryName;
                $logoData                = null;

                if (! empty($organization->logoUrl)) {
                    $logoInfo = $this->organizationService->getImageUrl($organization->logoUrl);
                    $logoData = $logoInfo['signedUrl'] ?? null;
                }
                Notification::route('mail', $user->email)->notify(new SendPasswordChangeConfirmation($otp, $user->email, $user->name, $organizationName, $logoData, $organizationFactoryName));
                return response()->json(['message' => 'OTP has been sent to your email.'], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }
    }

    public function otpVerifyFunction(Request $request)
    {
        $validator = Validator::make($request->only(['email', 'otp']), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $this->userInterface->findByColumn(['email' => $request->email]);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
        return response()->json(['message' => 'OTP has expired.'], 400);
    }

        return response()->json(['message' => 'OTP verified successfully.'], 202);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $this->userInterface->findByColumn(['email' => $request->email]);

        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->otp      = null;
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

}
