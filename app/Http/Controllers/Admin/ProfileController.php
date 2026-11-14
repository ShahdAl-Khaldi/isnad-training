<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'job_title' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $user->update($data);

        return back()->with('success', 'انحفظت بياناتك.');
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'avatar.image' => 'الملف لازم يكون صورة.',
            'avatar.max' => 'الصورة لازم تكون أقل من 3 ميجا.',
        ]);

        $user = $request->user();
        $old = $user->avatar_path;
        $user->avatar_path = $request->file('avatar')->store('avatars', 'local');
        $user->save();
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return back()->with('success', 'تغيّرت صورتك.');
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->avatar_path) {
            Storage::disk('local')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return back()->with('success', 'انشالت الصورة.');
    }

    public function showAvatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path), 404);

        return Storage::disk('local')->response($user->avatar_path, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'current_password.current_password' => 'كلمة السر الحالية غلط.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'تغيّرت كلمة السر.');
    }
}
