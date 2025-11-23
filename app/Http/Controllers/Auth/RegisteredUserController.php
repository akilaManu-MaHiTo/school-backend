<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Notifications\WelcomeNotification\WelcomeNotification;
use App\Repositories\All\ComOrganization\ComOrganizationInterface;
use App\Repositories\All\User\UserInterface;
use App\Services\OrganizationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class RegisteredUserController extends Controller
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

    public function store(RegisterRequest $request)
    {
        $validatedData                      = $request->validated();
        $validatedData['isCompanyEmployee'] = (bool) $validatedData['isCompanyEmployee'];
        $validatedData['password']          = Hash::make($validatedData['password']);

        $user = $this->userInterface->create($validatedData);

        try {
            $organization = $this->comOrganizationInterface->first();

            if ($organization) {
                $organizationName = $organization->organizationName;
                $organizationFactoryName = $organization->organizationFactoryName;
                $logoData         = null;

                if (! empty($organization->logoUrl)) {
                    $logoInfo = $this->organizationService->getImageUrl($organization->logoUrl);
                    $logoData = $logoInfo['signedUrl'] ?? null;
                }

                Notification::send($user, new WelcomeNotification($user->name, $organizationName, $logoData, $organizationFactoryName));
            }
        } catch (\Exception $e) {
        }

        return response()->json([
            'message' => 'User registered successfully!',
            'user'    => $user,
        ], 201);
    }
}
