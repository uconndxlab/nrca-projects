<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EntraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected EntraService $entraService;

    public function __construct(EntraService $entraService)
    {
        $this->entraService = $entraService;
    }

    /**
     * Initialize the OIDC flow by redirecting to Microsoft Entra ID.
     */
    public function redirect(Request $request)
    {
        return $this->entraService->generateRedirect();
    }

    /**
     * Handle the OIDC callback from Entra ID.
     */
    public function callback(Request $request)
    {
        try {
            $entraData = $this->entraService->handleCallback($request);
        } catch (\Throwable $e) {
            logger()->error('Entra callback error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect('/')->with('error', 'Authentication failed: ' . $e->getMessage());
        }

        $netid = $entraData['netid'];

        if (!$netid) {
            return redirect('/')->with('error', 'Unable to retrieve your NetID from the login service.');
        }

        $user = User::where('netid', $netid)->first();

        if (!$user) {
            return redirect('/')->with('error', 'You are not authorized to access this page.');
        }

        if (!$user->is_admin) {
            return redirect('/')->with('error', 'You are not authorized to access this page.');
        }

        // Update name or email from Entra claims if present
        if (!empty($entraData['name']) && empty($user->name)) {
            $user->name = $entraData['name'];
        }
        if (!empty($entraData['email']) && empty($user->email)) {
            $user->email = $entraData['email'];
        }
        $user->save();

        Auth::login($user);

        return redirect()->intended('/admin');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'You have been logged out.');
    }
}
