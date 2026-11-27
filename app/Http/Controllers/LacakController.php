<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/** Lacak pesanan memakai kode pesanan, tanpa harus masuk ke akun. */
class LacakController extends Controller
{
    public function index()
    {
        return view('lacak.index', [
            'kode' => '',
            'pesanan' => null,
            'riwayat' => collect(),
        ]);
    }

    public function cari(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30'],
        ], [
            'kode.required' => 'Masukkan kode pesanan terlebih dahulu.',
        ]);

        $kode = strtoupper(trim($data['kode']));

        $pesanan = Order::where('order_code', $kode)
            ->with(['item', 'toko', 'pengiriman'])
            ->first();

        if (! $pesanan) {
            return back()
                ->withInput()
                ->with('gagal', 'Pesanan dengan kode '.$kode.' tidak ditemukan. Periksa kembali kode pesanan Anda.');
        }

        // Riwayat status diambil dari tabel tracking_events, urut dari yang terbaru
        $riwayat = $pesanan->riwayat()->orderByDesc('happened_at')->orderByDesc('id')->get();

        return view('lacak.index', [
            'kode' => $kode,
            'pesanan' => $pesanan,
            'riwayat' => $riwayat,
        ]);
    }
}
