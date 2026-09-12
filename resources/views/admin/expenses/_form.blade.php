@php $expense = $expense ?? null; @endphp

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">วันที่</label>
        <input type="date" name="expense_date" value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">ผู้บันทึก</label>
        <input type="text" value="{{ $expense?->creator->name ?? auth()->user()->name }}" disabled class="w-full border-gray-200 bg-gray-50 rounded-md text-gray-500">
    </div>
</div>

<div>
    <label class="block text-sm font-medium mb-1">รายการ</label>
    <input type="text" name="item" value="{{ old('item', $expense?->item) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('item')" class="mt-1" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">จำนวน</label>
        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity', $expense?->quantity ?? 1) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('quantity')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">ราคาต่อหน่วย (บาท)</label>
        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price', $expense?->unit_price) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('unit_price')" class="mt-1" />
    </div>
</div>

<div class="text-sm text-gray-500">
    รวม: <span id="total-preview" class="font-semibold text-gray-800">{{ number_format($expense?->total_amount ?? 0, 2) }}</span> บาท
</div>

@if ($expense?->receipt_file)
    <div>
        <label class="block text-sm font-medium mb-2">ไฟล์รายจ่ายปัจจุบัน</label>
        <div class="flex items-center gap-3">
            <a href="{{ $expense->receipt_url }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline text-sm">📎 เปิดดูไฟล์</a>
            <label class="flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="remove_receipt" value="1"> ลบไฟล์นี้
            </label>
        </div>
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">แนบไฟล์ (ใบเสร็จ/บิล)</label>
    <input type="file" name="receipt_file" accept="image/*,.pdf" class="w-full">
    <p class="text-xs text-gray-400 mt-1">รองรับ jpg, png, pdf ขนาดไม่เกิน 8MB</p>
    <x-input-error :messages="$errors->get('receipt_file')" class="mt-1" />
</div>

<script>
    (function () {
        const qty = document.getElementById('quantity');
        const price = document.getElementById('unit_price');
        const preview = document.getElementById('total-preview');

        function update() {
            const total = (parseFloat(qty.value) || 0) * (parseFloat(price.value) || 0);
            preview.textContent = total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        qty.addEventListener('input', update);
        price.addEventListener('input', update);
    })();
</script>
