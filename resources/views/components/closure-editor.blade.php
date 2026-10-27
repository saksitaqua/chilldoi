@props([
    'model' => null,
    'hint' => 'วันที่เลือกจะถือว่าหยุดให้บริการ ลูกค้าจะจองไม่ได้',
])

@php
    $existing = $model ? $model->closedDays() : [];
    $selected = old('closure_days');
    $selected = is_array($selected) ? array_values(array_unique($selected)) : array_keys($existing);
@endphp

<div data-closure-picker data-days="{{ json_encode($selected) }}" data-reasons="{{ json_encode((object) $existing, JSON_UNESCAPED_UNICODE) }}">
    <label class="block text-sm font-medium mb-1">วันที่หยุดให้บริการ / ไม่ใช้งาน</label>
    <p class="text-xs text-gray-400 mb-2">{{ $hint }} — คลิกที่วันเพื่อเลือก (คลิกซ้ำเพื่อยกเลิก) เลือกได้หลายวันที่ไม่ติดกัน หรือกด Shift ค้างแล้วคลิกวันสุดท้ายเพื่อเลือกต่อเนื่อง</p>

    <input type="hidden" name="closures_present" value="1">
    <div data-closure-inputs></div>

    <div class="rounded-md border border-gray-200 p-3 max-w-sm">
        <div class="flex items-center justify-between mb-2">
            <button type="button" data-closure-nav="-1" class="px-2 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50" aria-label="เดือนก่อนหน้า">&lsaquo;</button>
            <div class="flex items-center gap-2">
                <span data-closure-title class="font-medium text-sm"></span>
                <button type="button" data-closure-nav="today" class="text-xs text-emerald-700 hover:underline">วันนี้</button>
            </div>
            <button type="button" data-closure-nav="1" class="px-2 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50" aria-label="เดือนถัดไป">&rsaquo;</button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-400 mb-1">
            <span>อา</span><span>จ</span><span>อ</span><span>พ</span><span>พฤ</span><span>ศ</span><span>ส</span>
        </div>
        <div data-closure-grid class="grid grid-cols-7 gap-1"></div>
    </div>

    <div class="mt-3">
        <div class="flex items-center justify-between max-w-sm">
            <p class="text-sm font-medium">วันที่หยุดที่เลือก <span data-closure-count class="text-gray-500 font-normal"></span></p>
            <button type="button" data-closure-clear class="text-xs text-red-600 hover:underline">ล้างทั้งหมด</button>
        </div>
        <div data-closure-chips class="mt-2 flex flex-wrap gap-2"></div>
        <p data-closure-empty class="text-xs text-gray-400">ยังไม่ได้เลือกวันหยุด</p>
    </div>

    <div class="mt-3 max-w-sm">
        <label class="block text-xs text-gray-500">เหตุผล (ไม่บังคับ — ใช้กับวันที่เพิ่มใหม่ในครั้งนี้)
            <input type="text" name="closure_reason" value="{{ old('closure_reason') }}" maxlength="255" placeholder="เช่น ซ่อมบำรุง, ปิดปรับปรุง"
                   class="mt-1 block w-full border-gray-300 rounded-md text-sm">
        </label>
    </div>

    @error('closure_days')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    @if ($errors->get('closure_days.*'))
        <p class="text-xs text-red-600 mt-1">{{ collect($errors->get('closure_days.*'))->flatten()->first() }}</p>
    @endif
    @error('closure_reason')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

@once
    <script>
        (function () {
            const MONTHS = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            const SHORT = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const pad = (n) => String(n).padStart(2, '0');
            const fmt = (date) => date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
            const parse = (text) => { const p = text.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); };
            const addDays = (text, n) => { const d = parse(text); d.setDate(d.getDate() + n); return fmt(d); };
            const todayText = fmt(new Date());

            function label(startText, endText) {
                const s = parse(startText), e = parse(endText);
                if (startText === endText) return s.getDate() + ' ' + SHORT[s.getMonth()] + ' ' + s.getFullYear();
                if (s.getFullYear() !== e.getFullYear()) return s.getDate() + ' ' + SHORT[s.getMonth()] + ' ' + s.getFullYear() + ' – ' + e.getDate() + ' ' + SHORT[e.getMonth()] + ' ' + e.getFullYear();
                if (s.getMonth() !== e.getMonth()) return s.getDate() + ' ' + SHORT[s.getMonth()] + ' – ' + e.getDate() + ' ' + SHORT[e.getMonth()] + ' ' + e.getFullYear();
                return s.getDate() + '–' + e.getDate() + ' ' + SHORT[s.getMonth()] + ' ' + s.getFullYear();
            }

            function ranges(selected) {
                const days = [...selected].sort();
                const out = [];
                days.forEach(function (day) {
                    const last = out[out.length - 1];
                    if (last && addDays(last.end, 1) === day) last.end = day; else out.push({start: day, end: day});
                });
                return out;
            }

            function render(root) {
                const st = root._closure;
                root.querySelector('[data-closure-title]').textContent = MONTHS[st.month] + ' ' + st.year;

                const first = new Date(st.year, st.month, 1);
                const total = new Date(st.year, st.month + 1, 0).getDate();
                let html = '';
                for (let i = 0; i < first.getDay(); i++) html += '<span></span>';

                for (let d = 1; d <= total; d++) {
                    const text = st.year + '-' + pad(st.month + 1) + '-' + pad(d);
                    const on = st.selected.has(text);
                    let cls = 'h-9 rounded-md text-sm ';
                    cls += on ? 'bg-emerald-600 text-white font-semibold ' : (text < todayText ? 'text-gray-400 hover:bg-gray-100 ' : 'text-gray-800 hover:bg-gray-100 ');
                    if (text === todayText) cls += 'ring-1 ring-emerald-500 ';
                    html += '<button type="button" data-closure-day="' + text + '" aria-pressed="' + on + '" class="' + cls + '">' + d + '</button>';
                }
                root.querySelector('[data-closure-grid]').innerHTML = html;

                const list = ranges(st.selected);
                root.querySelector('[data-closure-chips]').innerHTML = list.map(function (r) {
                    const reason = st.reasons[r.start] ? ' title="' + String(st.reasons[r.start]).replace(/"/g, '&quot;') + '"' : '';
                    const past = r.end < todayText ? ' opacity-60' : '';
                    return '<span' + reason + ' class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700' + past + '">' + label(r.start, r.end) +
                        '<button type="button" data-closure-chip-remove data-start="' + r.start + '" data-end="' + r.end + '" class="text-gray-400 hover:text-red-600" aria-label="เอาออก">&times;</button></span>';
                }).join('');

                root.querySelector('[data-closure-empty]').classList.toggle('hidden', st.selected.size > 0);
                root.querySelector('[data-closure-count]').textContent = st.selected.size ? '(' + st.selected.size + ' วัน)' : '';

                root.querySelector('[data-closure-inputs]').innerHTML = [...st.selected].sort().map(function (day) {
                    return '<input type="hidden" name="closure_days[]" value="' + day + '">';
                }).join('');
            }

            function setRange(st, from, to, on) {
                const a = from <= to ? from : to, b = from <= to ? to : from;
                for (let cursor = a, guard = 0; cursor <= b && guard < 800; cursor = addDays(cursor, 1), guard++) {
                    if (on) st.selected.add(cursor); else st.selected.delete(cursor);
                }
            }

            function init(root) {
                const selected = new Set(JSON.parse(root.dataset.days || '[]'));
                const upcoming = [...selected].sort().find((d) => d >= todayText) || [...selected].sort()[0] || todayText;
                const anchor = parse(upcoming);

                root._closure = {selected: selected, reasons: JSON.parse(root.dataset.reasons || '{}'), last: null, year: anchor.getFullYear(), month: anchor.getMonth()};
                render(root);
            }

            document.addEventListener('click', function (event) {
                const root = event.target.closest('[data-closure-picker]');
                if (! root) return;
                const st = root._closure;

                const dayButton = event.target.closest('[data-closure-day]');
                if (dayButton) {
                    const day = dayButton.dataset.closureDay;
                    const turnOn = ! st.selected.has(day);

                    if (event.shiftKey && st.last) setRange(st, st.last, day, turnOn);
                    else if (turnOn) st.selected.add(day); else st.selected.delete(day);

                    st.last = day;
                    render(root);
                    return;
                }

                const nav = event.target.closest('[data-closure-nav]');
                if (nav) {
                    if (nav.dataset.closureNav === 'today') {
                        const now = new Date(); st.year = now.getFullYear(); st.month = now.getMonth();
                    } else {
                        const moved = new Date(st.year, st.month + Number(nav.dataset.closureNav), 1);
                        st.year = moved.getFullYear(); st.month = moved.getMonth();
                    }
                    render(root);
                    return;
                }

                const chip = event.target.closest('[data-closure-chip-remove]');
                if (chip) {
                    setRange(st, chip.dataset.start, chip.dataset.end, false);
                    render(root);
                    return;
                }

                if (event.target.closest('[data-closure-clear]')) {
                    st.selected.clear();
                    st.last = null;
                    render(root);
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-closure-picker]').forEach(init);
            });
        })();
    </script>
@endonce
