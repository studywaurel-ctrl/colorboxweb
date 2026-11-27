<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

/** Daftar toko Colorbox, rinciannya, dan lembar QR untuk meja kasir. */
class TokoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:80'],
            'layanan' => ['nullable', 'in:ttc,donasi'],
        ]);

        $cari = trim((string) $request->query('cari'));
        $layanan = (string) $request->query('layanan');

        $daftar = Store::where('is_active', true);

        if ($cari !== '') {
            $daftar->where(function ($q) use ($cari) {
                $q->where('name', 'like', '%'.$cari.'%')
                    ->orWhere('city', 'like', '%'.$cari.'%')
                    ->orWhere('address', 'like', '%'.$cari.'%');
            });
        }

        if ($layanan === 'ttc') {
            $daftar->where('has_ttc', true);
        } elseif ($layanan === 'donasi') {
            $daftar->where('has_donation', true);
        }

        $toko = $daftar->orderBy('city')->orderBy('name')->get();

        return view('toko.index', [
            'toko' => $toko,
            'cari' => $cari,
            'layanan' => $layanan,
            'kota' => Store::where('is_active', true)->orderBy('city')->distinct()->pluck('city'),
        ]);
    }

    public function show(Store $store)
    {
        abort_unless($store->is_active, 404);

        // Beberapa produk yang stoknya masih ada di toko ini
        $idProduk = $store->stok()
            ->where('stock', '>', 0)
            ->with('varian')
            ->get()
            ->pluck('varian.product_id')
            ->unique()
            ->take(8);

        $produk = Product::where('is_active', true)
            ->whereIn('id', $idProduk)
            ->with(['kategori', 'gambar', 'varian'])
            ->orderBy('name')
            ->get();

        return view('toko.show', [
            'toko' => $store,
            'produk' => $produk,
        ]);
    }

    /**
     * Lembar QR yang dicetak dan diletakkan di meja kasir toko.
     * Pelanggan memindainya ketika menyerahkan donasi atau mengambil pesanan.
     */
    public function qrKasir(Store $store)
    {
        abort_unless($store->is_active, 404);

        return view('toko.qr', ['toko' => $store]);
    }
}
