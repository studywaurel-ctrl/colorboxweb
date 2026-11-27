<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;

/**
 * Virtual Try On.
 *
 * Catatan jujur: bagian pencocokan pakaian pada tubuh BELUM diproses oleh
 * layanan mana pun. Yang dikerjakan halaman ini adalah pratinjau lokal,
 * yaitu foto tangkapan kamera pelanggan disandingkan dengan foto produk.
 * Bila nanti tersedia layanan virtual try-on, tinggal mengubah
 * config('colorbox.try_on.mode') menjadi 'api' dan mengisi alamat serta
 * kunci layanannya pada berkas .env.
 */
class VtoController extends Controller
{
    /** Memastikan sesi try-on memang milik pengunjung yang sedang membuka. */
    private function pastikanMilikSendiri(VirtualTryOn $vto): void
    {
        if ($vto->user_id) {
            abort_unless(auth()->check() && auth()->id() === $vto->user_id, 404);

            return;
        }

        // Tamu dicatat lewat daftar id yang disimpan pada session
        $milik = session('vto_milik', []);
        abort_unless(in_array($vto->id, $milik), 404);
    }

    /** Pilih produk dan warna sebelum kamera dinyalakan. */
    public function index(Request $request)
    {
        $request->validate([
            'produk' => ['nullable', 'string', 'max:191'],
        ]);

        $produk = Product::where('is_active', true)
            ->with(['kategori', 'gambar', 'varian'])
            ->orderBy('name')
            ->get();

        $terpilih = null;
        if ($request->query('produk')) {
            $terpilih = $produk->firstWhere('slug', $request->query('produk'));
        }
        if (! $terpilih) {
            $terpilih = $produk->first();
        }

        return view('vto.index', [
            'produk' => $produk,
            'terpilih' => $terpilih,
            'mode' => config('colorbox.try_on.mode', 'preview'),
        ]);
    }

    /** Membuat satu sesi try-on lalu membuka panggung kamera. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'color' => ['required', 'string', 'max:40'],
        ], [
            'product_id.required' => 'Pilih produk yang ingin dicoba.',
            'color.required' => 'Pilih warna produk terlebih dahulu.',
        ]);

        $sesi = VirtualTryOn::create([
            'user_id' => auth()->id(),
            'product_id' => $data['product_id'],
            'color' => $data['color'],
            'status' => 'dibuat',
            'engine' => config('colorbox.try_on.mode', 'preview'),
        ]);

        if (! auth()->check()) {
            $milik = session('vto_milik', []);
            $milik[] = $sesi->id;
            session(['vto_milik' => $milik]);
        }

        return redirect()->route('vto.show', $sesi);
    }

    /** Panggung kamera, panduan posisi tubuh, dan hasil pratinjau. */
    public function show(VirtualTryOn $virtualTryOn)
    {
        $this->pastikanMilikSendiri($virtualTryOn);

        $virtualTryOn->load(['produk.gambar', 'produk.varian']);

        return view('vto.show', [
            'sesi' => $virtualTryOn,
            'produk' => $virtualTryOn->produk,
            'mode' => config('colorbox.try_on.mode', 'preview'),
            'maksKb' => (int) config('colorbox.try_on.max_upload_kb', 5120),
        ]);
    }

    /**
     * Menerima tangkapan kamera (data URL base64 dari canvas) atau foto
     * yang diunggah, lalu menyimpannya ke public/images/tryon.
     */
    public function proses(Request $request, VirtualTryOn $virtualTryOn)
    {
        $this->pastikanMilikSendiri($virtualTryOn);

        $maksKb = (int) config('colorbox.try_on.max_upload_kb', 5120);

        $data = $request->validate([
            'gambar' => ['nullable', 'string'],
            'berkas' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:'.$maksKb],
            'ukuran' => ['nullable', 'string', 'max:10'],
        ], [
            'berkas.image' => 'Berkas yang diunggah harus berupa gambar JPG atau PNG.',
            'berkas.max' => 'Ukuran foto paling besar '.$maksKb.' KB.',
        ]);

        $folder = public_path('images/tryon');
        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = null;

        if ($request->hasFile('berkas')) {
            $nama = 'tryon-'.$virtualTryOn->id.'-'.time().'.'.$request->file('berkas')->getClientOriginalExtension();
            $request->file('berkas')->move($folder, $nama);
            $path = 'images/tryon/'.$nama;
        } elseif (! empty($data['gambar'])) {
            // Tangkapan kamera dikirim sebagai data URL, contoh: data:image/png;base64,....
            $isi = $data['gambar'];

            if (! preg_match('/^data:image\/(png|jpeg);base64,/', $isi, $cocok)) {
                return back()->with('gagal', 'Gambar hasil kamera tidak dikenali. Silakan ambil gambar sekali lagi.');
            }

            $murni = base64_decode(substr($isi, strpos($isi, ',') + 1), true);

            if ($murni === false || strlen($murni) < 1) {
                return back()->with('gagal', 'Gambar hasil kamera tidak dapat dibaca. Silakan ambil gambar sekali lagi.');
            }

            if (strlen($murni) > $maksKb * 1024) {
                return back()->with('gagal', 'Ukuran gambar terlalu besar. Silakan ambil gambar sekali lagi.');
            }

            $nama = 'tryon-'.$virtualTryOn->id.'-'.time().'.'.($cocok[1] === 'png' ? 'png' : 'jpg');
            file_put_contents($folder.'/'.$nama, $murni);
            $path = 'images/tryon/'.$nama;
        }

        if (! $path) {
            return back()->with('gagal', 'Belum ada gambar yang dikirim. Ambil gambar dengan kamera atau unggah foto.');
        }

        // Berkas lama dihapus supaya folder tidak menumpuk
        if ($virtualTryOn->photo_path && file_exists(public_path($virtualTryOn->photo_path))) {
            unlink(public_path($virtualTryOn->photo_path));
        }

        $virtualTryOn->photo_path = $path;

        if ($data['ukuran'] ?? null) {
            $virtualTryOn->size = $data['ukuran'];
        }

        if (config('colorbox.try_on.mode', 'preview') === 'preview') {
            $virtualTryOn->status = 'selesai';
            $virtualTryOn->note = 'Pratinjau lokal: foto Anda disandingkan dengan foto produk. '
                .'Pencocokan pakaian pada tubuh belum diproses layanan mana pun.';
        } else {
            // Mode api disiapkan untuk disambungkan ke layanan virtual try-on
            $virtualTryOn->status = 'diproses';
            $virtualTryOn->note = 'Menunggu hasil dari layanan virtual try-on.';
        }

        $virtualTryOn->save();

        return back()->with('berhasil', 'Gambar tersimpan. Silakan lihat hasil pratinjaunya.');
    }

    /** Menghapus sesi try-on beserta berkas gambarnya. */
    public function destroy(VirtualTryOn $virtualTryOn)
    {
        $this->pastikanMilikSendiri($virtualTryOn);

        foreach ([$virtualTryOn->photo_path, $virtualTryOn->result_path] as $berkas) {
            if ($berkas && file_exists(public_path($berkas))) {
                unlink(public_path($berkas));
            }
        }

        $virtualTryOn->delete();

        return redirect()->route('vto.index')->with('berhasil', 'Sesi Virtual Try On dihapus.');
    }
}
