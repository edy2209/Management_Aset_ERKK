<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class BarangController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $query = Barang::with(['category:id,name', 'kbarang:id,name'])
                ->select([
                    'id',
                    'name',
                    'category_id',
                    'kbarang_id',
                    'kode_barang',
                    'jumlah_barang',
                    'status_pinjam',
                    'image',
                    'created_at'
                ]);

            if (Auth::guard('peminjam')->check()) {
                $query->where('status_pinjam', true);
            }

            $barangData = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'kode_barang' => $item->kode_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'status_pinjam' => $item->status_pinjam,
                    'category' => $item->category->name,
                    'kbarang' => $item->kbarang->name,
                    'image_url' => $item->image ? asset('storage/'.$item->image) : null,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $barangData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching barang data: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data barang',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
}