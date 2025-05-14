<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function profile(){
        $user = Auth()->user();
        $email = $user->email;
        $name = $user->name;
        $passwordOld = '';
        $passwordNew = '';

        return view('profile', compact('email', 'name', 'passwordOld', 'passwordNew'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'passwordOld' => 'required|string|min:8',
            'passwordNew' => 'required|string|min:8',
        ]);

        if (!password_verify($data['passwordOld'], auth()->user()->password)) {
            Log::warning('User ' . auth()->user()->id . ' tried to update profile with incorrect password.');
            return redirect()->back()->withErrors(['passwordOld' => 'The password is incorrect.']);
        }

        // check if the email is already taken by another user
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser && $existingUser->id !== auth()->id()) {
            return redirect()->back()->withErrors(['email' => 'The email has already been taken.']);
        }

        // hash the new password
        if (isset($data['passwordNew'])) {
            $data['password'] = bcrypt($data['passwordNew']);
            unset($data['passwordOld'], $data['passwordNew']);
        } else {
            unset($data['passwordOld'], $data['passwordNew']);
        }

        Log::info('User ' . auth()->user()->id . ' updated their profile.');

        $user = auth()->user();
        $user->update($data);

        return redirect(route('homepage'))->with('message', 'Profile updated');
    }
}
