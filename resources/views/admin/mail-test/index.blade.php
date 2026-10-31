<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ทดสอบการส่งอีเมล</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if ($result)
            <div class="rounded-md border px-4 py-3 text-sm {{ $result['ok'] ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-700' }}">
                {{ $result['message'] }}
            </div>
        @endif

        @if ($warnings)
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 space-y-1">
                <p class="font-semibold">สิ่งที่ควรแก้ก่อน</p>
                <ul class="list-disc ml-5">
                    @foreach ($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="font-semibold mb-3">การตั้งค่าอีเมลที่ใช้อยู่ตอนนี้</h3>
            <dl class="divide-y divide-gray-100 text-sm">
                @foreach ($settings as $label => $value)
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-gray-500">{{ $label }}</dt>
                        <dd class="font-medium text-right break-all">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="text-xs text-gray-400 mt-3">ค่าเหล่านี้อ่านจากไฟล์ .env บนเซิร์ฟเวอร์ ถ้าแก้ไฟล์แล้วค่าไม่เปลี่ยน ให้ลองกดบันทึกไฟล์ .env อีกครั้งหรือรอสักครู่แล้วรีเฟรช</p>
        </div>

        <form method="POST" action="{{ route('admin.mail-test.send') }}" class="bg-white shadow-sm rounded-lg p-6">
            @csrf
            <h3 class="font-semibold mb-3">ส่งอีเมลทดสอบ</h3>
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-sm font-medium mb-1">ส่งไปที่อีเมล</label>
                    <input type="email" name="to" value="{{ old('to', config('mail.notify_to') ? trim(explode(',', config('mail.notify_to'))[0]) : '') }}" required class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('to')" class="mt-1" />
                </div>
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">ส่งอีเมลทดสอบ</button>
            </div>
            <p class="text-xs text-gray-400 mt-2">ถ้าส่งไม่สำเร็จ จะแสดงสาเหตุจากเซิร์ฟเวอร์เมลตรง ๆ ใช้ตรวจว่ารหัสผ่าน พอร์ต หรือการเข้ารหัสถูกหรือไม่</p>
        </form>
    </div>
</x-app-layout>
