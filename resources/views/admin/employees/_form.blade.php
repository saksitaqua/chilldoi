@php $employee = $employee ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อ</label>
    <input type="text" name="name" value="{{ old('name', $employee?->name) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">เบอร์โทร</label>
    <input type="text" name="phone" value="{{ old('phone', $employee?->phone) }}" class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">สิทธิ์</label>
        <select name="role" required class="w-full border-gray-300 rounded-md">
            <option value="accounting" @selected(old('role', $employee?->role) == 'accounting')>พนักงานบัญชี</option>
            <option value="sales" @selected(old('role', $employee?->role) == 'sales')>พนักงานขาย</option>
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">สถานะ</label>
        <select name="status" required class="w-full border-gray-300 rounded-md">
            <option value="active" @selected(old('status', $employee?->status ?? 'active') == 'active')>Active</option>
            <option value="inactive" @selected(old('status', $employee?->status) == 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

@if ($employee)
    <div class="bg-gray-50 border border-gray-200 rounded-md p-3 text-sm text-gray-600">
        Username: <span class="font-mono font-medium text-gray-800">{{ $employee->username }}</span>
    </div>
@else
    <p class="text-xs text-gray-400">ระบบจะสร้าง Username และรหัสผ่านให้อัตโนมัติหลังบันทึก</p>
@endif
