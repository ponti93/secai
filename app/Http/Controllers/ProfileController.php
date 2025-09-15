<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\CalendarEvent;
use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user profile
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get user statistics
        $stats = [
            'emails_sent' => Email::where('user_id', $userId)->count(),
            'meetings_recorded' => Meeting::where('user_id', $userId)->count(),
            'documents_created' => Document::where('user_id', $userId)->count(),
            'expenses_created' => Expense::where('user_id', $userId)->count(),
            'inventory_items' => Inventory::where('user_id', $userId)->count(),
            'member_since' => $user->created_at
        ];

        return view('profile', compact('user', 'stats'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
        ]);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile')->with('success', 'Password updated successfully!');
    }

    /**
     * Update profile picture
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('avatars', $fileName, 'public');
            
            $user->update(['avatar' => $filePath]);
        }

        return redirect()->route('profile')->with('success', 'Profile picture updated successfully!');
    }
}