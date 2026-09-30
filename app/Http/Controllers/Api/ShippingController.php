<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpxShippingRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Ambil daftar kota/kabupaten tujuan (mendukung pencarian keyword).
     */
    public function cities(Request $request): JsonResponse
    {
        $search = trim($request->query('q', ''));

        $query = SpxShippingRate::query()
            ->select('destination_city')
            ->distinct();

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('destination_city', 'like', "%{$search}%")
                    ->orWhere('destination_district', 'like', "%{$search}%");
            });
        }

        $cities = $query->orderBy('destination_city')
            ->limit(50)
            ->pluck('destination_city');

        return response()->json([
            'status' => 'success',
            'data' => $cities,
        ]);
    }

    /**
     * Ambil daftar kecamatan berdasarkan kota tujuan.
     */
    public function districts(Request $request): JsonResponse
    {
        $city = trim($request->query('city', ''));

        if ($city === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter city wajib disertakan.',
            ], 422);
        }

        $districts = SpxShippingRate::where('destination_city', $city)
            ->orderBy('destination_district')
            ->pluck('destination_district');

        return response()->json([
            'status' => 'success',
            'city' => $city,
            'data' => $districts,
        ]);
    }

    /**
     * Hitung tarif ongkir SPX Hemat dan Regular.
     */
    public function calculate(Request $request): JsonResponse
    {
        $city = $request->input('destination_city') ?? $request->input('city');
        $district = $request->input('destination_district') ?? $request->input('district');
        $weightGrams = (int) ($request->input('weight_grams') ?? 1000);

        if (! $city || ! $district) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kota dan Kecamatan tujuan wajib diisi.',
            ], 422);
        }

        $rateData = SpxShippingRate::calculateRate(
            $city,
            $district,
            $weightGrams
        );

        if (! $rateData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarif ongkir SPX tidak ditemukan untuk rute tujuan tersebut.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rateData,
        ]);
    }
}
