<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::whereIn('role', ['accounting', 'sales'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['accounting', 'sales'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $username = User::generateUsername();
        $password = User::generateRandomPassword();

        $employee = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'status' => $data['status'],
            'username' => $username,
            'email' => "{$username}@chilldoi.local",
            'password' => $password,
        ]);

        return redirect()->route('admin.employees.index')->with([
            'status' => 'เพิ่มพนักงานเรียบร้อยแล้ว',
            'generated_username' => $username,
            'generated_password' => $password,
            'generated_for' => $employee->name,
        ]);
    }

    public function edit(User $employee)
    {
        abort_unless(in_array($employee->role, ['accounting', 'sales']), 404);

        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        abort_unless(in_array($employee->role, ['accounting', 'sales']), 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['accounting', 'sales'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function resetPassword(User $employee)
    {
        abort_unless(in_array($employee->role, ['accounting', 'sales']), 404);

        $password = User::generateRandomPassword();
        $employee->update(['password' => $password]);

        return redirect()->route('admin.employees.index')->with([
            'status' => 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
            'generated_username' => $employee->username,
            'generated_password' => $password,
            'generated_for' => $employee->name,
        ]);
    }

    public function destroy(User $employee)
    {
        abort_unless(in_array($employee->role, ['accounting', 'sales']), 404);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('status', 'ลบพนักงานเรียบร้อยแล้ว');
    }
}
