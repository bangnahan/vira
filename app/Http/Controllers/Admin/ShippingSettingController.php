<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SpxShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingSettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan metode ekspedisi SPX.
     */
    public function index(): View
    {
        $activeMode = Setting::get('spx_active_services', 'ALL');
        $totalRates = SpxShippingRate::count();
        $totalCities = SpxShippingRate::distinct('destination_city')->count();

        // Sample simulasi tarif ke Jakarta Selatan (Cilandak)
        $sampleRate = SpxShippingRate::calculateRate('KOTA ADM. JAKARTA SELATAN', 'CILANDAK', 1000);

        return view('admin.settings.shipping', compact(
            'activeMode',
            'totalRates',
            'totalCities',
            'sampleRate'
        ));
    }

    /**
     * Perbarui opsi layanan SPX yang diaktifkan.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'spx_active_services' => ['required', 'in:ALL,SPX_REGULAR,SPX_HEMAT'],
        ]);

        Setting::set('spx_active_services', $validated['spx_active_services'], 'shipping');

        $labels = [
            'ALL' => 'Semua Layanan Aktif (SPX Hemat & SPX Reguler)',
            'SPX_REGULAR' => 'Hanya SPX Reguler (Standar)',
            'SPX_HEMAT' => 'Hanya SPX Hemat (Ekonomi)',
        ];

        $chosenLabel = $labels[$validated['spx_active_services']] ?? $validated['spx_active_services'];

        return redirect()->route('admin.shipping-settings.index')
            ->with('success', "Pengaturan metode ekspedisi SPX berhasil disimpan: {$chosenLabel}.");
    }
}
