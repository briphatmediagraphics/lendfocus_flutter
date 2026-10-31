<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function show(Inquiry $inquiry): View
    {
        $inquiry->load(['replies.user', 'user', 'project', 'property', 'assignedTo']);

        return view('admin.inquiry-detail', [
            'inquiry' => $inquiry,
        ]);
    }

    public function addReply(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $reply = InquiryReply::create([
            'inquiry_id' => $inquiry->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        $inquiry->update([
            'last_replied_at' => now(),
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Reply added successfully.');
    }

    public function assign(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $inquiry->update($validated);

        return back()->with('success', 'Inquiry assigned successfully.');
    }
}
