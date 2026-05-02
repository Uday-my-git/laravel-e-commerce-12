<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

class SocialMediaController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // dd(config('services.google'));

            $googleUser = Socialite::driver('google')->user();

            $newUser = User::updateOrCreate(
                [
                    'email' => $googleUser->getEmail(),
                ], [
                    'name'      => $googleUser->getName(),
                    'password'  => Hash::make(Str::random(24)),
                    'google_id' => $googleUser->id,
                    'email_verified_at' => now()
                ]
            );
            Auth::login($newUser);
            return redirect()->intended(route('account.profile'));

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /************************ Facebook login ************************/

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            if (!$facebookUser->getEmail()) {
                return redirect()->route('account.login')->with('error', 'Unable to retrieve email from Facebook.');
            }
            
            // Logic: Find by Facebook ID, or find by Email and update the ID
            $user = User::where('facebook_id', $facebookUser->getId())->orWhere('email', $facebookUser->getEmail())->first();
            
            if ($user) {
                // If user exists by email but hasn't linked Facebook yet, link it now
                if (!$user->facebook_id) {
                    $user->update(['facebook_id' => $facebookUser->getId()]);
        
                    Log::info('Facebook ID linked to existing user', [
                        'id' => $user->id,
                    ]);
                }
                Auth::login($user);
                
            } else {
                $newUser = User::create([
                    'name'        => $facebookUser->getName(),
                    'email'       => $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'password'    => Hash::make(Str::random(24)),
                    'email_verified_at'  => now()
                ]);
                
                Auth::login($newUser);
                Log::info('New user created via Facebook', [
                    'id' => $newUser->id,
                ]);
            }
            return redirect()->intended(route('account.profile'));
            
        } catch (\Throwable $e) {
            Log::error('Facebook Auth Error ' . $e->getMessage());
            return redirect()->route('account.login')->with('error', 'Authentication failed. Please try again.');
        }
    }
    
    /************************ Github login ************************/

    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }
        
    public function handleGithubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->stateless()->user();

            if (!$githubUser->getEmail()) {
                return redirect()->route('account.login')->with('error', 'Unable to retrieve email from github.');
            }
            
            // Logic: Find by Github ID, or find by Email and update the ID
            $user = User::where('github_id', $githubUser->getId())->orWhere('email', $githubUser->getEmail())->first();

            if ($user) {
                // If user exists by email but hasn't linked github yet, link it now
                if (!$user->github_id) {
                    $user->update(['github_id' => $githubUser->getId()]);
        
                    Log::info('Github ID linked to existing user', [
                        'id' => $user->id,
                    ]);
                }
                Auth::login($user);
                
            } else {
                $newUser = User::create([
                    'name'        => $githubUser->getName() ?? $githubUser->getNickname() ?? 'Github User',
                    'email'       => $githubUser->getEmail(),
                    'github_id'   => $githubUser->getId(),
                    'password'    => Hash::make(Str::random(24)),
                    'email_verified_at'  => now()
                ]);
                
                Auth::login($newUser);
                Log::info('New user created via Github', [
                    'id' => $newUser->id,
                ]);
            }
            return redirect()->intended(route('account.profile'));
            
        } catch (\Throwable $e) {

            Log::error('Github Auth Error ' . $e->getMessage());
            return redirect()->route('account.login')->with('error', 'Authentication failed. Please try again.');
        }
    }
    
    
    
    
}
