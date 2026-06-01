<?php

namespace App\Http\Controllers;

use App\Traits\HasTryCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    use HasTryCatch;

    public function index()
    {
        return Inertia::render('User', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $alert = $this::execute(
            try: function () use ($request, $validated) {
                $user = $request->user();
                $update = $request->filled('password')
                    ? collect($validated)->only(['nama', 'password'])->all()
                    : collect($validated)->only(['nama'])->all();

                // Cek jika ada foto
                if ($file = $request->file('foto')) {
                    $name = str($request->nama)->slug()->append('.' . $file->extension());
                    Storage::disk('public')->makeDirectory('images');
                    Image::make($file)->save(Storage::disk('public')->path('images/' . $name->value()));

                    if ($user->foto && $user->foto !== $name->value()) {
                        Storage::disk('public')->delete('images/' . $user->foto);
                    }

                    $update = array_merge($update, ['foto' => $name->value()]);
                }

                $user->update($update);
            },
            message: 'update profil'
        );

        return back()->with('alert', $alert);
    }
}
