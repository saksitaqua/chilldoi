@props([
    'closures' => collect(),
    'hint' => 'ช่วงวันที่เลือกจะถือว่าหยุดให้บริการ ลูกค้าจะจองไม่ได้',
])

@php
    $rows = old('closures');

    if ($rows === null) {
        $rows = $closures->map(fn ($c) => [
            'id' => $c->id,
            'start_date' => $c->start_date->format('Y-m-d'),
            'end_date' => $c->end_date->format('Y-m-d'),
            'reason' => $c->reason,
        ])->values()->all();
    }

    $today = now()->format('Y-m-d');
@endphp

<div data-closure-editor>
    <label class="block text-sm font-medium mb-1">วันที่หยุดให้บริการ / ไม่ใช้งาน</label>
    <p class="text-xs text-gray-400 mb-2">{{ $hint }} (วันเดียว: ใส่วันที่เริ่มอย่างเดียว / หลายวัน: ใส่วันที่เริ่มและวันสุดท้าย)</p>

    <input type="hidden" name="closures_present" value="1">

    <div class="space-y-2" data-closure-rows>
        @foreach ($rows as $index => $row)
            <div class="flex flex-wrap items-center gap-2 rounded-md border border-gray-200 p-2" data-closure-row>
                <input type="hidden" name="closures[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                <label class="text-xs text-gray-500">เริ่ม
                    <input type="date" name="closures[{{ $index }}][start_date]" value="{{ $row['start_date'] ?? '' }}" class="block border-gray-300 rounded-md text-sm">
                </label>
                <label class="text-xs text-gray-500">ถึง (ไม่ใส่ = วันเดียว)
                    <input type="date" name="closures[{{ $index }}][end_date]" value="{{ $row['end_date'] ?? '' }}" class="block border-gray-300 rounded-md text-sm">
                </label>
                <label class="text-xs text-gray-500 flex-1 min-w-[160px]">เหตุผล (ไม่บังคับ)
                    <input type="text" name="closures[{{ $index }}][reason]" value="{{ $row['reason'] ?? '' }}" maxlength="255" placeholder="เช่น ซ่อมบำรุง, ปิดปรับปรุง" class="block w-full border-gray-300 rounded-md text-sm">
                </label>
                @if (! empty($row['end_date']) && $row['end_date'] < $today)
                    <span class="text-xs text-gray-400">ผ่านมาแล้ว</span>
                @endif
                <button type="button" data-closure-remove class="text-xs text-red-600 hover:underline">ลบช่วงนี้</button>
            </div>
        @endforeach
    </div>

    @if ($errors->has('closures.*') || $errors->has('closures'))
        <p class="text-xs text-red-600 mt-1">{{ collect($errors->get('closures.*'))->flatten()->first() ?? 'กรุณาตรวจสอบวันที่หยุดให้บริการ (วันสุดท้ายต้องไม่ก่อนวันเริ่ม)' }}</p>
    @endif

    <button type="button" data-closure-add class="mt-2 text-sm text-emerald-700 hover:underline">+ เพิ่มช่วงวันหยุด</button>

    <template data-closure-template>
        <div class="flex flex-wrap items-center gap-2 rounded-md border border-gray-200 p-2" data-closure-row>
            <input type="hidden" name="closures[__INDEX__][id]" value="">
            <label class="text-xs text-gray-500">เริ่ม
                <input type="date" name="closures[__INDEX__][start_date]" class="block border-gray-300 rounded-md text-sm">
            </label>
            <label class="text-xs text-gray-500">ถึง (ไม่ใส่ = วันเดียว)
                <input type="date" name="closures[__INDEX__][end_date]" class="block border-gray-300 rounded-md text-sm">
            </label>
            <label class="text-xs text-gray-500 flex-1 min-w-[160px]">เหตุผล (ไม่บังคับ)
                <input type="text" name="closures[__INDEX__][reason]" maxlength="255" placeholder="เช่น ซ่อมบำรุง, ปิดปรับปรุง" class="block w-full border-gray-300 rounded-md text-sm">
            </label>
            <button type="button" data-closure-remove class="text-xs text-red-600 hover:underline">ลบช่วงนี้</button>
        </div>
    </template>
</div>

@once
    <script>
        (function () {
            let counter = Date.now();

            document.addEventListener('click', function (event) {
                const add = event.target.closest('[data-closure-add]');
                if (add) {
                    const root = add.closest('[data-closure-editor]');
                    const html = root.querySelector('[data-closure-template]').innerHTML.replaceAll('__INDEX__', 'n' + (counter++));
                    root.querySelector('[data-closure-rows]').insertAdjacentHTML('beforeend', html);
                    return;
                }

                const remove = event.target.closest('[data-closure-remove]');
                if (remove) {
                    remove.closest('[data-closure-row]').remove();
                }
            });
        })();
    </script>
@endonce
