<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ข้อเสนอแนะจากลูกค้า</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-3 mb-4 text-center">
            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500">คะแนนเฉลี่ย</div>
                <div class="text-2xl font-semibold text-amber-500">{{ $total ? number_format($average, 1) : '-' }} <span class="text-sm text-gray-400">/ 5</span></div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500">ทั้งหมด</div>
                <div class="text-2xl font-semibold">{{ number_format($total) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500">ยังไม่อ่าน</div>
                <div class="text-2xl font-semibold {{ $unread ? 'text-red-600' : '' }}">{{ number_format($unread) }}</div>
            </div>
        </div>

        @if ($tagCounts)
            <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
                <div class="text-xs text-gray-500 mb-2">ด้านที่ลูกค้าอยากให้ปรับปรุง (เรียงตามที่ถูกเลือกบ่อย)</div>
                <div class="flex flex-wrap gap-2 text-sm">
                    @foreach ($tagCounts as $tag => $count)
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200">{{ \App\Models\Feedback::TAGS[$tag] }} <b>{{ $count }}</b></span>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm items-center">
            <select name="rating" onchange="this.form.submit()" class="border-gray-300 rounded-md">
                <option value="">ทุกคะแนน</option>
                @foreach (range(5, 1) as $r)
                    <option value="{{ $r }}" @selected((string) request('rating') === (string) $r)>{{ $r }} ดาว</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="unread" value="1" @checked(request()->boolean('unread')) onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-700">
                เฉพาะที่ยังไม่อ่าน
            </label>
            @if (request()->hasAny(['rating', 'unread']))
                <a href="{{ route('admin.feedbacks.index') }}" class="text-emerald-700 hover:underline">ล้างตัวกรอง</a>
            @endif
        </form>

        <div class="space-y-3">
            @forelse ($feedbacks as $feedback)
                <div class="bg-white shadow-sm rounded-lg p-4 {{ $feedback->read_at ? '' : 'border-l-4 border-emerald-500' }}">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <span class="text-amber-400 text-lg tracking-wider">{{ str_repeat('★', $feedback->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $feedback->rating) }}</span></span>
                            @unless ($feedback->read_at)
                                <span class="ml-2 text-xs bg-emerald-100 text-emerald-800 rounded-full px-2 py-0.5">ใหม่</span>
                            @endunless
                        </div>
                        <div class="text-xs text-gray-400">{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    @php
                        $answers = [
                            'เหตุผลของคะแนน' => $feedback->comment,
                            'ชอบ/ประทับใจที่สุด' => $feedback->best,
                            'อยากให้ปรับปรุงด้านพื้นที่และสิ่งอำนวยความสะดวก' => $feedback->facility,
                            'การต้อนรับและการบริการ' => $feedback->service,
                            'อยากพบเพิ่มเมื่อมาครั้งหน้า' => $feedback->future,
                        ];
                        $tags = collect($feedback->facility_tags ?? [])->map(fn ($t) => \App\Models\Feedback::TAGS[$t] ?? null)->filter();
                    @endphp

                    @if ($tags->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($tags as $label)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">{{ $label }}</span>
                            @endforeach
                        </div>
                    @endif

                    <dl class="mt-2 space-y-2 text-sm">
                        @foreach (array_filter($answers) as $question => $answer)
                            <div>
                                <dt class="text-xs text-gray-500">{{ $question }}</dt>
                                <dd class="text-gray-800 whitespace-pre-line">{{ $answer }}</dd>
                            </div>
                        @endforeach
                        @if (! array_filter($answers) && $tags->isEmpty())
                            <p class="text-gray-400">(ให้คะแนนอย่างเดียว ไม่ได้เขียนความเห็น)</p>
                        @endif
                    </dl>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <div>
                            {{ $feedback->name ?: ($feedback->booking?->guest_name ?? '-') }}
                            · {{ $feedback->booking?->unit?->name ?? '-' }}
                            @if ($feedback->booking)
                                · <a href="{{ route('admin.bookings.edit', $feedback->booking_id) }}" class="text-emerald-700 hover:underline">การจอง #{{ $feedback->booking_id }}</a>
                            @endif
                            @if ($feedback->receipt)
                                · ใบเสร็จ {{ $feedback->receipt->receipt_no }}
                            @endif
                        </div>
                        @unless ($feedback->read_at)
                            <form method="POST" action="{{ route('admin.feedbacks.read', $feedback) }}">
                                @csrf
                                <button type="submit" class="text-emerald-700 hover:underline">ทำเครื่องหมายว่าอ่านแล้ว</button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-400">ยังไม่มีข้อเสนอแนะ</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $feedbacks->links() }}</div>
    </div>
</x-app-layout>
