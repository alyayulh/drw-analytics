<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class KategoriProdukSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $kategoriList = [
                'Krim Wajah Acne',
                'Krim Wajah Brightening',
                'Krim Wajah Anti Aging',
                'Moisturizer & Treatment Wajah',
                'Serum Wajah',
                'Pembersih Wajah',
                'Toner & Essence',
                'Exfoliating',
                'Masker & Peeling',
                'Sunscreen',
                'Makeup Wajah',
                'Lip Product',
                'Perawatan Tubuh',
                'Sabun',
                'Perawatan Rambut',
                'Suplemen & Minuman',
                'Aksesoris / Pouch',
            ];

            $kategoriMap = [];

            foreach ($kategoriList as $nama) {
                $kategori = KategoriProduk::firstOrCreate([
                    'nama_kategori' => $nama
                ]);

                $kategoriMap[$nama] = $kategori->id_kategori;
            }

            $assigned = 0;
            $tanpaKategori = 0;

            foreach (Produk::all() as $produk) {
                $idKategori = $this->resolveKategori($produk->nama_produk, $kategoriMap);

                $produk->update([
                    'id_kategori' => $idKategori
                ]);

                if ($idKategori) {
                    $assigned++;
                } else {
                    $tanpaKategori++;
                }
            }

            // Hapus kategori lama yang sudah tidak dipakai, seperti:
            // Krim Wajah, Serum, Makeup, Suplemen & Lainnya, dan kategori lama lainnya.
            KategoriProduk::whereNotIn('nama_kategori', $kategoriList)->delete();

            $this->command->info("✓ {$assigned} produk berhasil dikategorikan.");
            $this->command->info("✓ {$tanpaKategori} produk masuk ke Tanpa Kategori.");
            $this->command->info("✓ Kategori lama yang tidak dipakai sudah dibersihkan.");

            if ($tanpaKategori > 0) {
                $this->command->warn("Produk Tanpa Kategori:");
                foreach (Produk::whereNull('id_kategori')->pluck('nama_produk') as $namaProduk) {
                    $this->command->warn("- {$namaProduk}");
                }
            }
        });
    }

    private function resolveKategori(string $namaProduk, array $kategoriMap): ?int
    {
        $upper = strtoupper(trim($namaProduk));
        $upper = str_replace(['’', '‘', '`', '´'], "'", $upper);

        // Paket produk tidak diberi kategori.
        // Nanti otomatis masuk grup "Tanpa Kategori".
        if (str_starts_with($upper, 'PAKET')) {
            return null;
        }

        $rules = [
            // PERAWATAN RAMBUT
            'HAIR SERUM'             => 'Perawatan Rambut',
            'HAIR TONIC'             => 'Perawatan Rambut',
            'ALOE VERA SHAMPOO'      => 'Perawatan Rambut',
            'SHAMPOO'                => 'Perawatan Rambut',

            // LIP PRODUCT
            'AMOUR MATTE LIP'        => 'Lip Product',
            'LIPS CREAM'             => 'Lip Product',
            'LIP CREAM'              => 'Lip Product',
            'LIPS CARE'              => 'Lip Product',
            'LIP CARE'               => 'Lip Product',
            'LIPGLOSS'               => 'Lip Product',
            'LIPSTIK'                => 'Lip Product',
            'LIPS MATTE'             => 'Lip Product',
            'LIPSKRIM'               => 'Lip Product',
            'LIPS STAIN'             => 'Lip Product',
            'LIP STAIN'              => 'Lip Product',

            // SABUN
            'KOJIC ACID MILK SOAP'   => 'Sabun',
            'KOJIC SULFUR SOAP'      => 'Sabun',
            'SULFUR SOAP'            => 'Sabun',
            'MILK SOAP'              => 'Sabun',
            'BAMBOO CHARCOAL'        => 'Sabun',
            'SOAP'                   => 'Sabun',

            // KRIM WAJAH ACNE
            'ACNE BRIGHTENING CREAM' => 'Krim Wajah Acne',
            'DAY ACNE CREAM'         => 'Krim Wajah Acne',
            'DAY CREAM ACNE'         => 'Krim Wajah Acne',
            'SOFT ACNE CREAM'        => 'Krim Wajah Acne',
            'ACNE CREAM'             => 'Krim Wajah Acne',

            // KRIM WAJAH BRIGHTENING
            'SOFT BRIGHTENING CREAM' => 'Krim Wajah Brightening',
            'BRIGHTENING CREAM'      => 'Krim Wajah Brightening',
            'DAY WHITE CREAM'        => 'Krim Wajah Brightening',
            'DAY CREAM WHITE'        => 'Krim Wajah Brightening',
            'DAY PINK CREAM'         => 'Krim Wajah Brightening',
            'DAY CREAM PINK'         => 'Krim Wajah Brightening',
            'RADIANT BRIGHT'         => 'Krim Wajah Brightening',
            'RADIANT GLOW'           => 'Krim Wajah Brightening',
            'BRIGHTENING NIGHT CREAM'=> 'Krim Wajah Brightening',
            'GLOW CREAM'             => 'Krim Wajah Brightening',
            
            // KRIM WAJAH ANTI AGING
            'SNAIL CREAM'            => 'Krim Wajah Anti Aging',
            'ANTI AGING EYE GEL'     => 'Krim Wajah Anti Aging',

            // MOISTURIZER & TREATMENT WAJAH
            'CNR PLUS'               => 'Moisturizer & Treatment Wajah',
            'DAILY CERAMOIST'        => 'Moisturizer & Treatment Wajah',
            'MOISTURIZER GEL'        => 'Moisturizer & Treatment Wajah',
            'GLOWTECH SPICULE'       => 'Moisturizer & Treatment Wajah',
            'REJUVENATION'           => 'Moisturizer & Treatment Wajah',

            // PEMBERSIH WAJAH
            'FACIAL WASH'            => 'Pembersih Wajah',
            'CLEANSING MILK'         => 'Pembersih Wajah',
            'MILK CLEANSER'          => 'Pembersih Wajah',
            'MICELLAR CLEAN'         => 'Pembersih Wajah',
            'MICELLAR WATER'         => 'Pembersih Wajah',
            'MICELLAR'               => 'Pembersih Wajah',

            // TONER & ESSENCE
            'EXFOLIATING COMPLEX TONER' => 'Toner & Essence',
            'HYDRATING ESSENCE TONER'   => 'Toner & Essence',
            'HYDRATING ESSENCE'         => 'Toner & Essence',
            'FACE MIST'                 => 'Toner & Essence',
            'T- CHAMOMILE'              => 'Toner & Essence',
            'TONER'                     => 'Toner & Essence',

            // EXFOLIATING
            'EXFOLIATING APPLE'      => 'Exfoliating',
            'EXFOLIATING STRAWBERRY' => 'Exfoliating',
            '3 IN 1 EXFOLIATING'     => 'Exfoliating',
            'EXFOLIATING DERMA'      => 'Exfoliating',
            'EXFOLIATING GEL'        => 'Exfoliating',
            'EXFOLIATING'            => 'Exfoliating',

            // MASKER & PEELING
            'BRIGHTENING PEEL'       => 'Masker & Peeling',
            'PEEL OFF MASK'          => 'Masker & Peeling',
            'PEEL OF MASK'           => 'Masker & Peeling',
            'PEELING GEL'            => 'Masker & Peeling',
            'GREEN TEA FACE MASK'    => 'Masker & Peeling',
            'HONEY FACE MASK'        => 'Masker & Peeling',
            'TEA TREE OIL FACE MASK' => 'Masker & Peeling',
            'RICE FACE MASK'         => 'Masker & Peeling',
            'FACE MASK'              => 'Masker & Peeling',
            'MASK'                   => 'Masker & Peeling',

            // SUNSCREEN
            'SUNSCREEN'              => 'Sunscreen',
            'SUNCREEN'               => 'Sunscreen',
            'SUNBLOK'                => 'Sunscreen',
            'SUNBLOCK'               => 'Sunscreen',

            // MAKEUP WAJAH
            'DAILY COMPACT POWDER'   => 'Makeup Wajah',
            'COMPACT POWDER'         => 'Makeup Wajah',
            'SILKY SOFT FACE POWDER' => 'Makeup Wajah',
            'SILKY SOFT POWDER'      => 'Makeup Wajah',
            'LIGHT SILKY SOFT POWDER'=> 'Makeup Wajah',
            'LIGHTENING SILKY'       => 'Makeup Wajah',
            'BB -'                   => 'Makeup Wajah',
            'BB CUSHION'             => 'Makeup Wajah',
            'BB CREAM'               => 'Makeup Wajah',
            'BODY FOUNDATION'        => 'Makeup Wajah',

            // SERUM WAJAH
            'LUMINOUS BRIGHTENING'   => 'Serum Wajah',
            'BEAUTY DNA SALMON'      => 'Serum Wajah',
            'DNA SALMON EXTRA'       => 'Serum Wajah',
            'SERUM'                  => 'Serum Wajah',

            // PERAWATAN TUBUH
            'BREAST CREAM'           => 'Perawatan Tubuh',
            'BODY FIRMING'           => 'Perawatan Tubuh',
            'FIRMING BODY'           => 'Perawatan Tubuh',
            'DAY BODY LOTION'        => 'Perawatan Tubuh',
            'NIGHT BODY LOTION'      => 'Perawatan Tubuh',
            'BODY LOTION'            => 'Perawatan Tubuh',
            'BODY SCRUB'             => 'Perawatan Tubuh',
            'BODY WASH'              => 'Perawatan Tubuh',
            'HAND BODY'              => 'Perawatan Tubuh',
            'LULUR'                  => 'Perawatan Tubuh',
            'STRETCH MARK'           => 'Perawatan Tubuh',
            'STRETCHMARK'            => 'Perawatan Tubuh',
            'COOLBRIGHT'             => 'Perawatan Tubuh',
            'DEO HERBA'              => 'Perawatan Tubuh',
            'LOTION BODY TONE UP'    => 'Perawatan Tubuh',
            'BODY TONE UP'           => 'Perawatan Tubuh',

            // SUPLEMEN & MINUMAN
            "D'ETAWA"                => 'Suplemen & Minuman',
            'DETAWA'                 => 'Suplemen & Minuman',
            'SUSU ETAWA'             => 'Suplemen & Minuman',
            'DRW KAPSUL'             => 'Suplemen & Minuman',
            'DRW SLIMMING'           => 'Suplemen & Minuman',
            'SLIMMING CAPSUL'        => 'Suplemen & Minuman',
            'KAPSUL GEMUK'           => 'Suplemen & Minuman',
            'HB DOSTING'             => 'Suplemen & Minuman',

            // AKSESORIS
            'POUCH'                  => 'Aksesoris / Pouch',
        ];

        foreach ($rules as $keyword => $namaKategori) {
            if (str_contains($upper, strtoupper($keyword))) {
                return $kategoriMap[$namaKategori] ?? null;
            }
        }

        return null;
    }
}
