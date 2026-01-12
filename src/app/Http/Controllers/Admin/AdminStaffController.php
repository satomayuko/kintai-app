<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function list()
    {
        $staffs = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('id')
            ->get();

        return view('admin.staff.list', compact('staffs'));
    }
}