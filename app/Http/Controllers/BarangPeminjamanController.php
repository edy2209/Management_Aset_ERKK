<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangPeminjaman;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use App\Models\PeminjamanKonfirmasi;

class BarangPeminjamanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'peminjam_id' => 'required|exists:peminjams,id',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_pengembalian' => 'nullable|date|after_or_equal:tanggal_pinjam',
        ]);

        $barang = Barang::find($request->barang_id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan.'
            ], 404);
        }

        if ($request->jumlah > $barang->jumlah_barang) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah barang tidak cukup persediaan.'
            ], 400);
        }

        $peminjaman = BarangPeminjaman::create([
            'peminjam_id' => $request->peminjam_id,
            'barang_id' => $request->barang_id,
            'kbarang_id' => $request->kbarang_id,
            'jumlah' => $request->jumlah,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tanggal_pengembalian' => $request->tanggal_pengembalian,
            'status' => 'pending',
        ]);

        // Kurangi stok barang
        $barang->jumlah_barang -= $request->jumlah;
        $barang->save();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil disimpan.',
            'data' => $peminjaman,
        ]);
    }

    public function konfirmasi($id, Request $request)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string',
            'user_id' => 'required|exists:users,id' // Tambahkan user_id manual
        ]);

        // Cari peminjaman
        $peminjaman = BarangPeminjaman::findOrFail($id);

        // Cek status
        if ($peminjaman->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman sudah dikonfirmasi sebelumnya.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Update status peminjaman
            $peminjaman->status = $request->status;
            $peminjaman->save();

            // Catat konfirmasi
            $konfirmasi = PeminjamanKonfirmasi::create([
                'peminjaman_id' => $peminjaman->id,
                'user_id' => $request->user_id, // Pakai user_id dari request
                'status' => $request->status,
                'catatan' => $request->catatan ?? ($request->status === 'ditolak' ? 'Ditolak oleh atasan' : null)
            ]);

            // Jika ditolak, kembalikan stok
            if ($request->status === 'ditolak') {
                Barang::where('id', $peminjaman->barang_id)
                    ->increment('jumlah_barang', $peminjaman->jumlah);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konfirmasi berhasil',
                'data' => $konfirmasi
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        return BarangPeminjaman::with(['peminjam', 'barang'])->get();
    }
}
