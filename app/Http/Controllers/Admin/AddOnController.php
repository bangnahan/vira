<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\AddOnVariant;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AddOnController extends Controller
{
    /**
     * Tampilkan daftar seluruh item add-on & merchandise.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $eventFilter = $request->input('event_id', 'all');
        $statusFilter = $request->input('status', 'all');

        $query = AddOn::with(['event', 'variants'])
            ->withCount('registrationAddOns');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($eventFilter === 'global') {
            $query->whereNull('event_id');
        } elseif ($eventFilter !== 'all' && is_numeric($eventFilter)) {
            $query->where('event_id', (int) $eventFilter);
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $addOns = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Statistik ringkas
        $totalItems = AddOn::count();
        $activeItems = AddOn::where('is_active', true)->count();
        $globalItems = AddOn::whereNull('event_id')->count();
        $totalBaseStock = (int) AddOn::sum('stock');

        $events = Event::select('id', 'title', 'event_code')->orderBy('title')->get();

        return view('admin.addons.index', compact(
            'addOns',
            'events',
            'search',
            'eventFilter',
            'statusFilter',
            'totalItems',
            'activeItems',
            'globalItems',
            'totalBaseStock'
        ));
    }

    /**
     * Tampilkan form pembuatan add-on baru.
     */
    public function create(): View
    {
        $events = Event::select('id', 'title', 'event_code')->orderBy('title')->get();

        return view('admin.addons.create', compact('events'));
    }

    /**
     * Simpan add-on baru beserta varian opsional ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:add_ons,slug'],
            'event_id' => ['nullable', 'exists:events,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'has_variants' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.variant_name' => ['nullable', 'string', 'max:100'],
            'variants.*.additional_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Pastikan slug unik jika terjadi duplikasi nama
        $originalSlug = $slug;
        $counter = 1;
        while (AddOn::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('addons', 'public');
        }

        $hasVariants = $request->boolean('has_variants');

        DB::transaction(function () use ($validated, $slug, $imagePath, $hasVariants, $request) {
            $addon = AddOn::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'event_id' => ! empty($validated['event_id']) ? (int) $validated['event_id'] : null,
                'price' => $validated['price'],
                'weight_grams' => $validated['weight_grams'],
                'stock' => $validated['stock'],
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'has_variants' => $hasVariants,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($hasVariants && ! empty($validated['variants'])) {
                $totalVariantStock = 0;
                foreach ($validated['variants'] as $varData) {
                    $name = trim($varData['variant_name'] ?? '');
                    if ($name === '') {
                        continue;
                    }

                    $vStock = isset($varData['stock']) && $varData['stock'] !== '' ? (int) $varData['stock'] : 0;
                    $vAddPrice = isset($varData['additional_price']) && $varData['additional_price'] !== '' ? (float) $varData['additional_price'] : 0.00;

                    AddOnVariant::create([
                        'add_on_id' => $addon->id,
                        'variant_name' => $name,
                        'additional_price' => $vAddPrice,
                        'stock' => $vStock,
                    ]);

                    $totalVariantStock += $vStock;
                }

                if ($totalVariantStock > 0) {
                    $addon->update(['stock' => $totalVariantStock]);
                }
            }
        });

        return redirect()->route('admin.addons.index')
            ->with('success', "Item add-on '{$validated['name']}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Tampilkan form edit add-on.
     */
    public function edit(AddOn $addon): View
    {
        $addon->load('variants');
        $events = Event::select('id', 'title', 'event_code')->orderBy('title')->get();

        return view('admin.addons.edit', compact('addon', 'events'));
    }

    /**
     * Perbarui data add-on beserta variannya.
     */
    public function update(Request $request, AddOn $addon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:add_ons,slug,'.$addon->id],
            'event_id' => ['nullable', 'exists:events,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'has_variants' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.variant_name' => ['nullable', 'string', 'max:100'],
            'variants.*.additional_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        if ($slug !== $addon->slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (AddOn::where('slug', $slug)->where('id', '!=', $addon->id)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }
        }

        $imagePath = $addon->image_path;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada dan tersimpan di storage lokal
            if ($addon->image_path && Storage::disk('public')->exists($addon->image_path)) {
                Storage::disk('public')->delete($addon->image_path);
            }
            $imagePath = $request->file('image')->store('addons', 'public');
        }

        $hasVariants = $request->boolean('has_variants');

        DB::transaction(function () use ($addon, $validated, $slug, $imagePath, $hasVariants, $request) {
            $addon->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'event_id' => ! empty($validated['event_id']) ? (int) $validated['event_id'] : null,
                'price' => $validated['price'],
                'weight_grams' => $validated['weight_grams'],
                'stock' => $validated['stock'],
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'has_variants' => $hasVariants,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($hasVariants) {
                $keptVariantIds = [];
                $totalVariantStock = 0;

                if (! empty($validated['variants'])) {
                    foreach ($validated['variants'] as $varData) {
                        $name = trim($varData['variant_name'] ?? '');
                        if ($name === '') {
                            continue;
                        }

                        $vStock = isset($varData['stock']) && $varData['stock'] !== '' ? (int) $varData['stock'] : 0;
                        $vAddPrice = isset($varData['additional_price']) && $varData['additional_price'] !== '' ? (float) $varData['additional_price'] : 0.00;

                        if (! empty($varData['id'])) {
                            $existingVar = AddOnVariant::where('add_on_id', $addon->id)
                                ->where('id', $varData['id'])
                                ->first();

                            if ($existingVar) {
                                $existingVar->update([
                                    'variant_name' => $name,
                                    'additional_price' => $vAddPrice,
                                    'stock' => $vStock,
                                ]);
                                $keptVariantIds[] = $existingVar->id;
                                $totalVariantStock += $vStock;

                                continue;
                            }
                        }

                        $newVar = AddOnVariant::create([
                            'add_on_id' => $addon->id,
                            'variant_name' => $name,
                            'additional_price' => $vAddPrice,
                            'stock' => $vStock,
                        ]);
                        $keptVariantIds[] = $newVar->id;
                        $totalVariantStock += $vStock;
                    }
                }

                // Hapus varian yang dihapus oleh admin
                AddOnVariant::where('add_on_id', $addon->id)
                    ->whereNotIn('id', $keptVariantIds)
                    ->delete();

                if ($totalVariantStock > 0) {
                    $addon->update(['stock' => $totalVariantStock]);
                }
            } else {
                // Jika has_variants dinonaktifkan, hapus varian terkait
                $addon->variants()->delete();
            }
        });

        return redirect()->route('admin.addons.index')
            ->with('success', "Item add-on '{$addon->name}' berhasil diperbarui!");
    }

    /**
     * Hapus add-on dari katalog.
     */
    public function destroy(AddOn $addon): RedirectResponse
    {
        // Proteksi: jangan hapus jika sudah dipesan di pendaftaran peserta untuk menjaga integritas data riwayat
        $purchasedCount = $addon->registrationAddOns()->count();
        if ($purchasedCount > 0) {
            return redirect()->route('admin.addons.index')
                ->with('error', "Item '{$addon->name}' tidak dapat dihapus permanen karena sudah tercatat dalam {$purchasedCount} pesanan peserta. Anda dapat menonaktifkan statusnya agar tidak lagi muncul di formulir pendaftaran.");
        }

        if ($addon->image_path && Storage::disk('public')->exists($addon->image_path)) {
            Storage::disk('public')->delete($addon->image_path);
        }

        $addonName = $addon->name;
        $addon->delete();

        return redirect()->route('admin.addons.index')
            ->with('success', "Item add-on '{$addonName}' berhasil dihapus.");
    }

    /**
     * Toggle status aktif / nonaktif add-on dari katalog (untuk sembunyikan/tampilkan produk).
     */
    public function toggleStatus(AddOn $addon): RedirectResponse
    {
        $addon->update([
            'is_active' => ! $addon->is_active,
        ]);

        $statusLabel = $addon->is_active
            ? 'diaktifkan kembali dan kini tampil di pendaftaran & etalase'
            : 'dinonaktifkan dan disembunyikan dari pendaftaran event serta etalase';

        return redirect()->back()
            ->with('success', "Produk '{$addon->name}' berhasil {$statusLabel}.");
    }
}
