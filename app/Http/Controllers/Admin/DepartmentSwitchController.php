<?php

// app/Http/Controllers/Admin/DepartmentSwitchController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartmentSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $deptId = $request->input('department_id');

        if ($deptId && $deptId !== 'all') {
            session(['selected_department_id' => $deptId]);
        } else {
            session()->forget('selected_department_id');
        }

        return redirect()->back();
    }
}