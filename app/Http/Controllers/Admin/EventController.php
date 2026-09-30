<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\TemplateDesign;
use App\Services\MetaCapiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected MetaCapiService $metaCapiService
    ) {}

    /**
     * Tampilkan daftar seluruh event yang ada di sistem.
     */
    public function index(): View
    {
        $events = Event::withCount(['categories', 'packages'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    /**
     * Form pembuatan event baru.
     */
    public function create(): View
    {
        return view('admin.events.create');
    }

    /**
     * Simpan event baru beserta konfigurasi Meta Pixel & CAPI, kategori awal, dan paket.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:events,slug'],
            'event_code' => ['required', 'string', 'max:10', 'unique:events,event_code'],
            'activity_type' => ['required', 'in:RUN,RIDE,WALK'],
            'submission_mode' => ['required', 'in:SINGLE,CUMULATIVE'],
            'race_type' => ['required', 'in:CHALLENGE,RACE'],
            'description' => ['nullable', 'string'],
            'rules_and_terms' => ['nullable', 'string'],
            'registration_start' => ['required', 'date'],
            'registration_end' => ['required', 'date', 'after:registration_start'],
            'race_start' => ['required', 'date'],
            'race_end' => ['required', 'date', 'after:race_start'],
            'banner_image' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],

            // Konfigurasi Meta Pixel & CAPI
            'meta_pixel_id' => ['nullable', 'string', 'max:100'],
            'meta_capi_token' => ['nullable', 'string'],
            'meta_test_code' => ['nullable', 'string', 'max:50'],
            'is_meta_capi_enabled' => ['nullable', 'boolean'],

            // Kategori awal
            'category_name' => ['nullable', 'string', 'max:100'],
            'target_distance_km' => ['nullable', 'numeric', 'min:0.1'],
            'bib_prefix' => ['nullable', 'string', 'max:10'],

            // Paket awal
            'package_name' => ['nullable', 'string', 'max:100'],
            'package_price' => ['nullable', 'numeric', 'min:0'],
            'package_description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title'].'-'.strtolower($validated['event_code']));

        $bannerUrl = $validated['banner_image'] ?? null;
        if ($request->hasFile('hero_image')) {
            Storage::disk('public')->makeDirectory('events/banners');
            $path = $request->file('hero_image')->store('events/banners', 'public');
            if ($path) {
                $bannerUrl = Storage::url($path);
            }
        }

        $event = DB::transaction(function () use ($validated, $slug, $bannerUrl, $request) {
            $event = Event::create([
                'title' => $validated['title'],
                'slug' => $slug,
                'event_code' => strtoupper(trim($validated['event_code'])),
                'activity_type' => $validated['activity_type'],
                'submission_mode' => $validated['submission_mode'],
                'race_type' => $validated['race_type'],
                'description' => $validated['description'] ?? null,
                'rules_and_terms' => $validated['rules_and_terms'] ?? null,
                'registration_start' => $validated['registration_start'],
                'registration_end' => $validated['registration_end'],
                'race_start' => $validated['race_start'],
                'race_end' => $validated['race_end'],
                'banner_image' => $bannerUrl,
                'meta_pixel_id' => $validated['meta_pixel_id'] ?? null,
                'meta_capi_token' => $validated['meta_capi_token'] ?? null,
                'meta_test_code' => $validated['meta_test_code'] ?? null,
                'is_meta_capi_enabled' => (bool) ($request->has('is_meta_capi_enabled')),
                'is_active' => (bool) ($request->has('is_active')),
            ]);

            // Buat Kategori Awal dengan fallback aman
            $catName = ! empty($validated['category_name']) ? trim($validated['category_name']) : '10K Challenge';
            $dist = ! empty($validated['target_distance_km']) ? (float) $validated['target_distance_km'] : 10.0;
            $prefix = ! empty($validated['bib_prefix']) ? strtoupper(trim($validated['bib_prefix'])) : '10K';

            Category::create([
                'event_id' => $event->id,
                'name' => $catName,
                'target_distance_km' => $dist,
                'bib_prefix' => $prefix,
                'last_bib_sequence' => 0,
            ]);

            // Buat Paket Pendaftaran Awal dengan fallback aman
            $pkgName = ! empty($validated['package_name']) ? trim($validated['package_name']) : 'Reguler (Medali Finisher)';
            $pkgPrice = isset($validated['package_price']) ? (float) $validated['package_price'] : 150000.0;
            $pkgDesc = ! empty($validated['package_description'])
                ? trim($validated['package_description'])
                : 'e-BIB Digital, E-Sertifikat Finisher, Medali Logam Cor';

            Package::create([
                'event_id' => $event->id,
                'name' => $pkgName,
                'description' => $pkgDesc,
                'price' => $pkgPrice,
                'base_weight_grams' => 300,
                'requires_shipping' => true,
            ]);

            // Inisialisasi Template e-BIB dan E-Certificate
            TemplateDesign::create([
                'event_id' => $event->id,
                'type' => 'BIB',
                'canvas_width' => 1200,
                'canvas_height' => 800,
                'elements_config' => TemplateDesign::defaultBibConfig(),
            ]);

            TemplateDesign::create([
                'event_id' => $event->id,
                'type' => 'CERTIFICATE',
                'canvas_width' => 1920,
                'canvas_height' => 1080,
                'elements_config' => TemplateDesign::defaultCertificateConfig(),
            ]);

            return $event;
        });

        return redirect()->route('admin.events.index')
            ->with('success', "Event '{$event->title}' berhasil dibuat! Desain e-BIB dan Sertifikat telah diinisialisasi.");
    }

    /**
     * Form edit event beserta konfigurasi Meta Pixel & CAPI.
     */
    public function edit(Event $event): View
    {
        $event->load(['categories', 'packages']);

        return view('admin.events.edit', compact('event'));
    }

    /**
     * Perbarui data event & Meta CAPI.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug,'.$event->id],
            'event_code' => ['required', 'string', 'max:10', 'unique:events,event_code,'.$event->id],
            'activity_type' => ['required', 'in:RUN,RIDE,WALK'],
            'submission_mode' => ['required', 'in:SINGLE,CUMULATIVE'],
            'race_type' => ['required', 'in:CHALLENGE,RACE'],
            'description' => ['nullable', 'string'],
            'rules_and_terms' => ['nullable', 'string'],
            'registration_start' => ['required', 'date'],
            'registration_end' => ['required', 'date', 'after:registration_start'],
            'race_start' => ['required', 'date'],
            'race_end' => ['required', 'date', 'after:race_start'],
            'banner_image' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],

            // Meta Pixel & CAPI
            'meta_pixel_id' => ['nullable', 'string', 'max:100'],
            'meta_capi_token' => ['nullable', 'string'],
            'meta_test_code' => ['nullable', 'string', 'max:50'],
            'is_meta_capi_enabled' => ['nullable', 'boolean'],

            // Kategori Jarak
            'categories' => ['nullable', 'array'],
            'categories.*.id' => ['nullable'],
            'categories.*.name' => ['nullable', 'string', 'max:100'],
            'categories.*.target_distance_km' => ['nullable', 'numeric', 'min:0.1'],
            'categories.*.bib_prefix' => ['nullable', 'string', 'max:10'],
            'categories.*.quota' => ['nullable', 'integer', 'min:1'],
            'categories.*.is_deleted' => ['nullable'],

            // Paket Pendaftaran
            'packages' => ['nullable', 'array'],
            'packages.*.id' => ['nullable'],
            'packages.*.name' => ['nullable', 'string', 'max:100'],
            'packages.*.description' => ['nullable', 'string', 'max:500'],
            'packages.*.price' => ['nullable', 'numeric', 'min:0'],
            'packages.*.base_weight_grams' => ['nullable', 'integer', 'min:0'],
            'packages.*.requires_shipping' => ['nullable'],
            'packages.*.includes_medal' => ['nullable'],
            'packages.*.includes_jersey' => ['nullable'],
            'packages.*.is_deleted' => ['nullable'],
        ]);

        $bannerUrl = $event->banner_image;
        if ($request->hasFile('hero_image')) {
            if ($event->banner_image && str_starts_with($event->banner_image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $event->banner_image);
                if ($oldPath !== '') {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            Storage::disk('public')->makeDirectory('events/banners');
            $path = $request->file('hero_image')->store('events/banners', 'public');
            if ($path) {
                $bannerUrl = Storage::url($path);
            }
        } elseif ($request->filled('banner_image')) {
            $bannerUrl = $request->input('banner_image');
        }

        DB::transaction(function () use ($event, $validated, $bannerUrl, $request) {
            $event->update([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['slug']),
                'event_code' => strtoupper(trim($validated['event_code'])),
                'activity_type' => $validated['activity_type'],
                'submission_mode' => $validated['submission_mode'],
                'race_type' => $validated['race_type'],
                'description' => $validated['description'] ?? null,
                'rules_and_terms' => $validated['rules_and_terms'] ?? null,
                'registration_start' => $validated['registration_start'],
                'registration_end' => $validated['registration_end'],
                'race_start' => $validated['race_start'],
                'race_end' => $validated['race_end'],
                'banner_image' => $bannerUrl,
                'meta_pixel_id' => $validated['meta_pixel_id'] ?? null,
                'meta_capi_token' => $validated['meta_capi_token'] ?? null,
                'meta_test_code' => $validated['meta_test_code'] ?? null,
                'is_meta_capi_enabled' => (bool) ($request->has('is_meta_capi_enabled')),
                'is_active' => (bool) ($request->has('is_active')),
            ]);

            // Sinkronisasi Kategori Jarak
            if ($request->has('categories') && is_array($request->input('categories'))) {
                foreach ($request->input('categories') as $catData) {
                    $catId = ! empty($catData['id']) && is_numeric($catData['id']) ? (int) $catData['id'] : null;
                    $isDeleted = ! empty($catData['is_deleted']);

                    if ($catId && $isDeleted) {
                        $cat = Category::where('id', $catId)->where('event_id', $event->id)->first();
                        if ($cat && $cat->registrations()->count() === 0) {
                            $cat->delete();
                        }

                        continue;
                    }

                    if (empty($catData['name'])) {
                        continue;
                    }

                    $dist = isset($catData['target_distance_km']) ? (float) $catData['target_distance_km'] : 10.0;
                    $prefix = ! empty($catData['bib_prefix']) ? strtoupper(trim($catData['bib_prefix'])) : 'BIB';
                    $quota = ! empty($catData['quota']) ? (int) $catData['quota'] : null;

                    if ($catId) {
                        Category::where('id', $catId)->where('event_id', $event->id)->update([
                            'name' => trim($catData['name']),
                            'target_distance_km' => $dist,
                            'bib_prefix' => $prefix,
                            'quota' => $quota,
                        ]);
                    } else {
                        Category::create([
                            'event_id' => $event->id,
                            'name' => trim($catData['name']),
                            'target_distance_km' => $dist,
                            'bib_prefix' => $prefix,
                            'quota' => $quota,
                            'last_bib_sequence' => 0,
                        ]);
                    }
                }
            }

            // Sinkronisasi Paket Pendaftaran
            if ($request->has('packages') && is_array($request->input('packages'))) {
                foreach ($request->input('packages') as $pkgData) {
                    $pkgId = ! empty($pkgData['id']) && is_numeric($pkgData['id']) ? (int) $pkgData['id'] : null;
                    $isDeleted = ! empty($pkgData['is_deleted']);

                    if ($pkgId && $isDeleted) {
                        $pkg = Package::where('id', $pkgId)->where('event_id', $event->id)->first();
                        if ($pkg && $pkg->registrations()->count() === 0) {
                            $pkg->delete();
                        }

                        continue;
                    }

                    if (empty($pkgData['name'])) {
                        continue;
                    }

                    $price = isset($pkgData['price']) ? (float) $pkgData['price'] : 0.0;
                    $weight = isset($pkgData['base_weight_grams']) ? (int) $pkgData['base_weight_grams'] : 300;
                    $desc = ! empty($pkgData['description']) ? trim($pkgData['description']) : null;
                    $reqShipping = ! empty($pkgData['requires_shipping']);
                    $incMedal = ! empty($pkgData['includes_medal']);
                    $incJersey = ! empty($pkgData['includes_jersey']);

                    if ($pkgId) {
                        Package::where('id', $pkgId)->where('event_id', $event->id)->update([
                            'name' => trim($pkgData['name']),
                            'description' => $desc,
                            'price' => $price,
                            'base_weight_grams' => $weight,
                            'requires_shipping' => $reqShipping,
                            'includes_medal' => $incMedal,
                            'includes_jersey' => $incJersey,
                        ]);
                    } else {
                        Package::create([
                            'event_id' => $event->id,
                            'name' => trim($pkgData['name']),
                            'description' => $desc,
                            'price' => $price,
                            'base_weight_grams' => $weight,
                            'requires_shipping' => $reqShipping,
                            'includes_medal' => $incMedal,
                            'includes_jersey' => $incJersey,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.events.edit', $event)
            ->with('success', "Konfigurasi Event '{$event->title}', Kategori Jarak, dan Paket berhasil disimpan.");
    }

    /**
     * Uji coba pengiriman event Meta CAPI menggunakan Test Event Code.
     */
    public function testCapi(Request $request, Event $event): RedirectResponse
    {
        $testCode = $request->input('test_event_code') ?: $event->meta_test_code;

        $result = $this->metaCapiService->sendTestEvent($event, $testCode);

        if ($result['status'] === 'success') {
            return back()->with('success', "Uji coba Meta CAPI Berhasil! Cek tab 'Test Events' di Meta Events Manager untuk Pixel {$event->meta_pixel_id}.");
        }

        $msg = $result['message'] ?? ($result['error'] ?? 'Gagal mengirim event test ke Meta CAPI.');

        return back()->with('error', "Gagal uji coba Meta CAPI: {$msg}");
    }

    /**
     * Upload gambar inline untuk deskripsi lengkap event (Rich Text / WYSIWYG editor).
     */
    public function uploadDescriptionImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $path = $request->file('image')->store('events/descriptions', 'public');
        $url = '/storage/'.$path;

        return response()->json([
            'status' => 'success',
            'url' => $url,
        ]);
    }

    /**
     * Hapus event beserta seluruh relasinya (kategori, paket, template, dan pendaftaran).
     */
    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title;

        // Hapus file gambar banner jika ada di storage lokal
        if ($event->banner_image && str_contains($event->banner_image, '/storage/')) {
            $path = str_replace('/storage/', '', $event->banner_image);
            Storage::disk('public')->delete($path);
        }

        // Hapus template design background image jika ada
        foreach ($event->templateDesigns as $template) {
            if ($template->background_image_path) {
                Storage::disk('public')->delete($template->background_image_path);
            }
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', "Event '{$title}' berhasil dihapus secara permanen.");
    }
}
