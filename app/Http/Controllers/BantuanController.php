<?php

namespace App\Http\Controllers;

/**
 * Pusat bantuan. Isi tiap halaman ditulis di controller sebagai array
 * sederhana lalu ditampilkan oleh satu view supaya mudah ditambah.
 */
class BantuanController extends Controller
{
    /** Daftar halaman bantuan yang tersedia beserta keterangan singkatnya. */
    public const TOPIK = [
        'cara-belanja' => ['Cara Belanja', 'Langkah memesan di situs Colorbox dari keranjang sampai pesanan dibuat.'],
        'pembayaran' => ['Pembayaran', 'Cara membayar pesanan dan batas waktu pembayarannya.'],
        'pengiriman' => ['Pengiriman', 'Pilihan pengiriman, ongkos kirim, dan cara melacak pesanan.'],
        'retur-dan-penukaran' => ['Retur dan Penukaran', 'Syarat pengajuan retur atau penukaran barang.'],
        'panduan-ukuran' => ['Panduan Ukuran', 'Tabel ukuran dan cara mengukur badan sendiri.'],
        'tap-try-collect' => ['Tap Try and Collect', 'Memesan lewat situs lalu mencoba dan mengambil barang di toko.'],
        'virtual-try-on' => ['Virtual Try On', 'Melihat gambaran pakaian sebelum memutuskan membeli.'],
        'donasi' => ['Donasi Poin', 'Menyerahkan pakaian layak pakai di toko dan menerima poin.'],
        'tentang-colorbox' => ['Tentang Colorbox', 'Keterangan singkat mengenai Colorbox dan layanannya.'],
    ];

    public function index()
    {
        return view('bantuan.index', ['topik' => self::TOPIK]);
    }

    public function show($halaman)
    {
        if (! array_key_exists($halaman, self::TOPIK)) {
            abort(404);
        }

        return view('bantuan.show', [
            'halaman' => $halaman,
            'judul' => self::TOPIK[$halaman][0],
            'ringkasan' => self::TOPIK[$halaman][1],
            'isi' => $this->isi($halaman),
        ]);
    }

    /**
     * Isi tiap halaman bantuan. Bentuknya daftar bagian, masing-masing
     * berisi judul, beberapa paragraf, dan daftar butir bila perlu.
     */
    private function isi(string $halaman): array
    {
        $gratisOngkir = rupiah(setting('free_shipping_threshold', 350000));
        $batasBayar = (int) setting('payment_expiry_hours', 24);
        $batasRetur = (int) setting('return_window_days', 7);
        $nilaiPoin = rupiah(setting('point_value', 100));
        $batasPoin = (int) setting('points_max_percent', 30);
        $poinPerPotong = (int) setting('donation_points_per_item', 1);
        $jendelaBooking = (int) setting('booking_window_days', 14);
        $minDonasi = (int) setting('min_donation_items', 1);
        $maksDonasi = (int) setting('max_donation_items', 50);

        $daftar = [
            'cara-belanja' => [
                [
                    'judul' => 'Langkah memesan',
                    'isi' => ['Belanja di Colorbox dilakukan dari halaman produk sampai halaman checkout. Urutannya seperti berikut.'],
                    'daftar' => [
                        'Pilih produk, lalu tentukan warna dan ukuran yang ingin dibeli.',
                        'Tekan "Tambah ke Keranjang". Isi keranjang dapat diubah jumlahnya atau dihapus kapan saja.',
                        'Buka halaman keranjang, centang barang yang ingin dibayar, lalu lanjut ke checkout.',
                        'Pilih cara menerima barang: dikirim ke alamat Anda atau diambil di toko.',
                        'Masukkan kode voucher atau tukarkan poin bila ada, kemudian selesaikan pembayaran.',
                    ],
                ],
                [
                    'judul' => 'Perlu punya akun?',
                    'isi' => [
                        'Keranjang dapat dipakai tanpa masuk ke akun, tetapi pembuatan pesanan memerlukan akun supaya riwayat pesanan, poin, dan pengajuan retur tersimpan atas nama Anda.',
                    ],
                ],
            ],
            'pembayaran' => [
                [
                    'judul' => 'Cara membayar',
                    'isi' => [
                        'Pembayaran diproses melalui Midtrans. Setelah pesanan dibuat, Anda akan diarahkan ke halaman pembayaran untuk memilih metode yang tersedia di sana.',
                        'Status pembayaran pada pesanan hanya berubah setelah Colorbox menerima keterangan resmi dari Midtrans. Anda tidak perlu menekan tombol apa pun untuk mengabarkan bahwa pembayaran sudah dilakukan.',
                    ],
                ],
                [
                    'judul' => 'Batas waktu pembayaran',
                    'isi' => [
                        'Pesanan yang belum dibayar dalam '.$batasBayar.' jam akan dibatalkan secara otomatis dan barangnya dikembalikan ke stok.',
                    ],
                ],
                [
                    'judul' => 'Voucher dan poin',
                    'isi' => [
                        'Kode voucher dimasukkan pada halaman checkout. Satu pesanan memakai satu kode voucher.',
                        'Poin dapat ditukar menjadi potongan belanja. Satu poin bernilai '.$nilaiPoin.' dan pemakaian poin paling banyak '.$batasPoin.' persen dari subtotal belanja.',
                    ],
                ],
            ],
            'pengiriman' => [
                [
                    'judul' => 'Pilihan pengiriman',
                    'isi' => [
                        'Pesanan dapat dikirim ke alamat Anda memakai kurir yang tersedia di halaman checkout, atau diambil sendiri di toko Colorbox yang Anda pilih.',
                        'Ongkos kirim dan perkiraan lama pengiriman muncul di halaman checkout setelah alamat pengiriman dipilih.',
                    ],
                ],
                [
                    'judul' => 'Gratis ongkos kirim',
                    'isi' => [
                        'Belanja mulai '.$gratisOngkir.' dalam satu pesanan mendapat gratis ongkos kirim.',
                    ],
                ],
                [
                    'judul' => 'Melacak pesanan',
                    'isi' => [
                        'Setiap perubahan status pesanan dicatat dan dapat dilihat pada halaman Lacak Pesanan dengan memasukkan kode pesanan, contohnya CBX-20260101-0001.',
                    ],
                    'tautan' => [['Buka Lacak Pesanan', 'lacak.index', null]],
                ],
            ],
            'retur-dan-penukaran' => [
                [
                    'judul' => 'Barang yang dapat diretur',
                    'isi' => [
                        'Pengajuan retur dibuka paling lama '.$batasRetur.' hari setelah pesanan diterima. Barang harus lengkap dengan labelnya dan belum dipakai.',
                    ],
                ],
                [
                    'judul' => 'Jenis pengajuan',
                    'isi' => ['Ada dua jenis pengajuan yang dapat dipilih.'],
                    'daftar' => [
                        'Tukar: barang diganti dengan warna atau ukuran lain sesuai ketersediaan stok.',
                        'Refund: dana dikembalikan setelah barang diperiksa petugas.',
                    ],
                ],
                [
                    'judul' => 'Cara mengajukan',
                    'isi' => [
                        'Pengajuan dilakukan dari halaman Retur. Barang dapat dikirim kembali atau dibawa langsung ke toko.',
                        'Pembelian yang dilakukan di toko juga dapat diretur dari rumah dengan memasukkan kode pembelian yang tertera pada struk.',
                    ],
                    'tautan' => [['Buka halaman Retur', 'retur.index', null]],
                ],
            ],
            'panduan-ukuran' => [
                [
                    'judul' => 'Cara mengukur',
                    'isi' => ['Ukur badan memakai meteran kain sambil berdiri tegak dan tidak menarik meteran terlalu kencang.'],
                    'daftar' => [
                        'Lingkar dada: ukur bagian dada yang paling lebar.',
                        'Lingkar pinggang: ukur bagian pinggang yang paling ramping.',
                        'Lingkar pinggul: ukur bagian pinggul yang paling lebar.',
                    ],
                ],
                [
                    'judul' => 'Tabel ukuran',
                    'isi' => ['Angka di bawah adalah ukuran badan dalam sentimeter, bukan ukuran pakaiannya. Kalau hasil pengukuran berada di antara dua ukuran, pilih ukuran yang lebih besar untuk potongan yang longgar.'],
                    'tabel' => [
                        'kepala' => ['Ukuran', 'Lingkar dada (cm)', 'Lingkar pinggang (cm)', 'Lingkar pinggul (cm)'],
                        'baris' => [
                            ['XS', '78 – 82', '60 – 64', '84 – 88'],
                            ['S', '83 – 87', '65 – 69', '89 – 93'],
                            ['M', '88 – 92', '70 – 74', '94 – 98'],
                            ['L', '93 – 98', '75 – 80', '99 – 104'],
                            ['XL', '99 – 104', '81 – 86', '105 – 110'],
                        ],
                    ],
                ],
                [
                    'judul' => 'Masih ragu?',
                    'isi' => [
                        'Setiap halaman produk mencantumkan bahan dan keterangan perawatannya. Anda juga dapat mencoba layanan Tap Try and Collect agar barang dapat dicoba lebih dulu di toko sebelum dibayar.',
                    ],
                    'tautan' => [['Pelajari Tap Try and Collect', 'bantuan.show', 'tap-try-collect']],
                ],
            ],
            'tap-try-collect' => [
                [
                    'judul' => 'Apa itu Tap Try and Collect',
                    'isi' => [
                        'Tap Try and Collect adalah layanan memesan barang lewat situs, lalu mencoba dan mengambilnya di toko Colorbox pilihan Anda tanpa ongkos kirim.',
                    ],
                ],
                [
                    'judul' => 'Cara memakainya',
                    'isi' => ['Layanan ini memakai jadwal kunjungan yang kuotanya terbatas.'],
                    'daftar' => [
                        'Pilih toko yang melayani Tap Try and Collect.',
                        'Pilih tanggal dan jam kunjungan. Jadwal dapat dipesan sampai '.$jendelaBooking.' hari ke depan.',
                        'Pilih barang beserta warna dan ukurannya, lalu simpan booking.',
                        'Datang ke toko sesuai jadwal, coba barangnya, dan bayar hanya yang jadi dibeli.',
                    ],
                    'tautan' => [['Buat booking Tap Try and Collect', 'ttc.index', null]],
                ],
                [
                    'judul' => 'Kuota jadwal',
                    'isi' => [
                        'Jumlah pengunjung tiap jadwal dibatasi supaya petugas toko dapat menyiapkan barangnya. Kalau jadwal sudah penuh, silakan pilih waktu lainnya.',
                    ],
                ],
            ],
            'virtual-try-on' => [
                [
                    'judul' => 'Apa itu Virtual Try On',
                    'isi' => [
                        'Virtual Try On membantu Anda membayangkan tampilan sebuah produk sebelum membeli. Anda mengunggah foto, memilih produk beserta warna dan ukurannya, lalu hasilnya ditampilkan pada halaman Virtual Try On.',
                        'Hasilnya berupa gambaran, bukan pengganti mencoba barang langsung. Untuk memastikan potongan dan ukurannya, barang tetap perlu dicoba, misalnya lewat Tap Try and Collect.',
                    ],
                    'tautan' => [['Buka Virtual Try On', 'vto.index', null]],
                ],
                [
                    'judul' => 'Foto yang dipakai',
                    'isi' => [
                        'Foto yang Anda unggah tersimpan pada akun Anda dan dapat dihapus sendiri dari halaman Virtual Try On kapan saja.',
                    ],
                ],
            ],
            'donasi' => [
                [
                    'judul' => 'Donasi pakaian layak pakai',
                    'isi' => [
                        'Anda dapat menyerahkan pakaian yang masih layak pakai di toko Colorbox yang melayani donasi, lalu menerima poin yang bisa ditukar menjadi potongan belanja.',
                    ],
                ],
                [
                    'judul' => 'Langkahnya',
                    'isi' => [],
                    'daftar' => [
                        'Pilih toko dan jadwal donasi di halaman Donasi Poin.',
                        'Tuliskan perkiraan jumlah pakaian yang akan diserahkan, paling sedikit '.$minDonasi.' potong dan paling banyak '.$maksDonasi.' potong.',
                        'Datang sesuai jadwal dan serahkan pakaian kepada petugas toko.',
                        'Petugas menghitung dan memeriksa pakaian, lalu poin ditambahkan ke akun Anda.',
                    ],
                    'tautan' => [['Jadwalkan donasi', 'donasi.index', null]],
                ],
                [
                    'judul' => 'Poin yang diterima',
                    'isi' => [
                        'Setiap potong pakaian yang diterima petugas bernilai '.$poinPerPotong.' poin. Poin baru masuk setelah donasi selesai diperiksa, dan satu poin bernilai '.$nilaiPoin.' ketika ditukar pada saat checkout.',
                    ],
                ],
            ],
            'tentang-colorbox' => [
                [
                    'judul' => 'Tentang Colorbox',
                    'isi' => [
                        'Colorbox adalah toko pakaian sehari-hari dengan koleksi atasan, bawahan, outerwear, denim, serta tas dan aksesori.',
                        'Situs ini melayani belanja daring sekaligus menghubungkannya dengan toko Colorbox, mulai dari pengambilan pesanan di toko sampai penyerahan donasi pakaian.',
                    ],
                ],
                [
                    'judul' => 'Layanan yang tersedia',
                    'isi' => [],
                    'daftar' => [
                        'Belanja produk dengan pengiriman ke alamat atau pengambilan di toko.',
                        'Tap Try and Collect untuk mencoba barang di toko sebelum membayar.',
                        'Virtual Try On untuk melihat gambaran produk sebelum membeli.',
                        'Donasi Poin untuk menyerahkan pakaian layak pakai dan menerima poin.',
                        'Retur dan penukaran barang, termasuk untuk pembelian di toko.',
                    ],
                ],
                [
                    'judul' => 'Daftar toko',
                    'isi' => ['Alamat, jam buka, dan layanan tiap toko dapat dilihat pada halaman daftar toko.'],
                    'tautan' => [['Lihat daftar toko', 'toko.index', null]],
                ],
            ],
        ];

        return $daftar[$halaman];
    }
}
