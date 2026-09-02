<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function edit(): View
    {
        return view('company.settings.change-password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        // Hashed automatically by User's 'password' => 'hashed' cast.
        $request->user()->update(['password' => $request->validated()['new_password']]);

        return redirect()
            ->route('company.settings.password.edit')
            ->with('status', 'Password updated successfully.');
    }
}
