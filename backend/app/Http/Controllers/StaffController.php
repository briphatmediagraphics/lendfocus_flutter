<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display all staff members
     */
    public function index(): View
    {
        $staff = Staff::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('staff.index', compact('staff'));
    }

    /**
     * Display staff by department
     */
    public function department(string $department): View
    {
        $staff = Staff::where('department', $department)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('staff.department', compact('staff', 'department'));
    }

    /**
     * Display single staff member
     */
    public function show(Staff $member): View
    {
        return view('staff.show', compact('member'));
    }
}
