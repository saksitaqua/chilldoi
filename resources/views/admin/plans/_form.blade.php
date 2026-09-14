@php $plan = $plan ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อแผนงาน</label>
    <input type="text" name="title" value="{{ old('title', $plan?->title) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('title')" class="mt-1" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">กำหนดเริ่มแจ้งเตือน (ถ้ามี)</label>
        <input type="date" name="remind_from" value="{{ old('remind_from', $plan?->remind_from?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
        <p class="text-xs text-gray-400 mt-1">Dashboard จะเริ่มแจ้งเตือนแผนงานนี้ตั้งแต่วันที่นี้เป็นต้นไป</p>
        <x-input-error :messages="$errors->get('remind_from')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">กำหนดเสร็จ (ถ้ามี)</label>
        <input type="date" name="due_date" value="{{ old('due_date', $plan?->due_date?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
    </div>
</div>

<div>
    <label class="block text-sm font-medium mb-1">งบประมาณ (บาท)</label>
    <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $plan?->budget) }}" class="w-full border-gray-300 rounded-md sm:w-60">
    <x-input-error :messages="$errors->get('budget')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">หมายเหตุ</label>
    <textarea name="notes" rows="4" class="w-full border-gray-300 rounded-md">{{ old('notes', $plan?->notes) }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">สถานะ</label>
    <select name="status" required class="w-full border-gray-300 rounded-md">
        <option value="pending" @selected(old('status', $plan?->status ?? 'pending') == 'pending')>รอดำเนินการ</option>
        <option value="done" @selected(old('status', $plan?->status) == 'done')>เสร็จแล้ว</option>
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-1" />
</div>
