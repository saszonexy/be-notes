<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $path = $request->file('photo')->store('profile_photos', 'public');

        $user->profile_photo = $path;
        $user->save();

        return response()->json([
            'message'   => 'Foto profil berhasil diupload',
            'photo_url' => asset('storage/' . $path),
        ]);
    }
}
