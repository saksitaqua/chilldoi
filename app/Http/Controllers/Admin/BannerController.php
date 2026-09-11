<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        $this->syncFilesFromDisk();

        $banners = Banner::orderBy('sort_order')->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:8192',
        ]);

        $directory = public_path('images/banner');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach ($request->file('images', []) as $file) {
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($directory, $filename);
            $this->fixOrientation($directory.DIRECTORY_SEPARATOR.$filename);
        }

        return redirect()->route('admin.banners.index')->with('status', 'อัปโหลดรูปภาพเรียบร้อยแล้ว');
    }

    public function destroyImage(Banner $banner)
    {
        $path = public_path('images/banner/'.$banner->filename);

        if (is_file($path)) {
            unlink($path);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'ลบรูปภาพเรียบร้อยแล้ว');
    }

    private function fixOrientation(string $path): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg'])) {
            return;
        }

        $exif = @exif_read_data($path);

        if (! $exif || ! isset($exif['Orientation']) || $exif['Orientation'] === 1) {
            return;
        }

        $image = @imagecreatefromjpeg($path);

        if (! $image) {
            return;
        }

        $rotated = match ($exif['Orientation']) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        imagejpeg($rotated, $path, 92);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'banners' => 'nullable|array',
            'banners.*.id' => 'required|exists:banners,id',
            'banners.*.sort_order' => 'required|integer|min:0',
            'banners.*.focal_position' => ['required', Rule::in(['top', 'center', 'bottom'])],
        ]);

        $activeIds = collect($request->input('active', []))->map(fn ($id) => (int) $id)->all();

        foreach ($data['banners'] ?? [] as $row) {
            Banner::where('id', $row['id'])->update([
                'sort_order' => $row['sort_order'],
                'focal_position' => $row['focal_position'],
                'is_active' => in_array((int) $row['id'], $activeIds, true),
            ]);
        }

        return redirect()->route('admin.banners.index')->with('status', 'บันทึกการตั้งค่าแบนเนอร์เรียบร้อยแล้ว');
    }

    private function syncFilesFromDisk(): void
    {
        $directory = public_path('images/banner');

        if (! is_dir($directory)) {
            return;
        }

        $files = collect(scandir($directory))
            ->filter(fn ($file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
            ->values();

        $existing = Banner::pluck('filename')->all();
        $nextOrder = (int) Banner::max('sort_order') + 1;

        foreach ($files as $file) {
            if (! in_array($file, $existing, true)) {
                Banner::create([
                    'filename' => $file,
                    'sort_order' => $nextOrder++,
                    'is_active' => false,
                ]);
            }
        }

        // Remove DB entries whose file no longer exists on disk.
        Banner::whereNotIn('filename', $files->all())->delete();
    }
}
