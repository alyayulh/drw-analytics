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
                'Krim Wajah', 'Pembersih Wajah', 'Toner & Essence',
                'Serum', 'Exfoliating', 'Masker & Peeling', 'Sunscreen',
                'Makeup', 'Perawatan Tubuh', 'Perawatan Rambut', 'Suplemen & Lainnya',
            ];

            $kategoriMap = [];
            foreach ($kategoriList as $nama) {
                $kat = KategoriProduk::firstOrCreate(['nama_kategori' => $nama]);
                $kategoriMap[$nama] = $kat->id_kategori;
            }

            $produks  = Produk::all();
            $assigned = 0;
            $skipped  = 0;

            foreach ($produks as $produk) {
                $kat = $this->resolveKategori($produk->nama_produk, $kategoriMap);
                if ($kat) {
                    $produk->update(['id_kategori' => $kat]);
                    $assigned++;
                } else {
                    $skipped++;
                }
            }

            $this->command->info("✓ {$assigned} produk berhasil dikategorikan");
            if ($skipped > 0) {
                $this->command->warn("⚠ {$skipped} produk tidak cocok keyword manapun:");
                foreach (Produk::whereNull('id_kategori')->pluck('nama_produk') as $nama) {
                    $this->command->warn("  - {$nama}");
                }
            }
        });
    }

    private function resolveKategori(string $namaProduk, array $kategoriMap): ?int
    {
        $upper = strtoupper($namaProduk);

        $rules = [
            // KRIM WAJAH
            'DAY ACNE CREAM'            => 'Krim Wajah',
            'ACNE CREAM'                => 'Krim Wajah',
            'SOFT ACNE CREAM'           => 'Krim Wajah',
            'SOFT BRIGHTENING CREAM'    => 'Krim Wajah',
            'DAY WHITE CREAM'           => 'Krim Wajah',
            'DAY PINK CREAM'            => 'Krim Wajah',
            'DAY CREAM'                 => 'Krim Wajah',
            'BRIGHTENING CREAM'         => 'Krim Wajah',
            'SNAIL CREAM'               => 'Krim Wajah',
            'CNR PLUS'                  => 'Krim Wajah',
            'RADIANT BRIGHT'            => 'Krim Wajah',
            'RADIANT GLOW'              => 'Krim Wajah',
            'BREAST CREAM'              => 'Krim Wajah',
            'ANTI AGING EYE GEL'        => 'Krim Wajah',
            // PEMBERSIH WAJAH
            'FACIAL WASH'               => 'Pembersih Wajah',
            'CLEANSING MILK'            => 'Pembersih Wajah',
            'MILK CLEANSER'             => 'Pembersih Wajah',
            'MICELLAR'                  => 'Pembersih Wajah',
            // TONER & ESSENCE
            'EXFOLIATING COMPLEX TONER' => 'Toner & Essence',
            'HYDRATING ESSENCE'         => 'Toner & Essence',
            'FACE MIST'                 => 'Toner & Essence',
            'T- CHAMOMILE'              => 'Toner & Essence',
            'TONER'                     => 'Toner & Essence',
            // SERUM
            'LUMINOUS BRIGHTENING'      => 'Serum',
            'BEAUTY DNA SALMON'         => 'Serum',
            'DNA SALMON EXTRA'          => 'Serum',
            'GLOWTECH'                  => 'Serum',
            'SERUM'                     => 'Serum',
            // EXFOLIATING
            'EXFOLIATING'               => 'Exfoliating',
            // MASKER & PEELING
            'BRIGHTENING PEEL'          => 'Masker & Peeling',
            'PEEL OFF MASK'             => 'Masker & Peeling',
            'PEEL OF MASK'              => 'Masker & Peeling',
            'PEELING GEL'               => 'Masker & Peeling',
            'GREEN TEA MASK'            => 'Masker & Peeling',
            'HONEY MASK'                => 'Masker & Peeling',
            'TEA TREE OIL MASK'         => 'Masker & Peeling',
            'RICE MASK'                 => 'Masker & Peeling',
            'FACE MASK'                 => 'Masker & Peeling',
            // SUNSCREEN
            'SUNSCREEN'                 => 'Sunscreen',
            'SUNCREEN'                  => 'Sunscreen',
            'SUNBLOK'                   => 'Sunscreen',
            // MAKEUP
            'DAILY COMPACT POWDER'      => 'Makeup',
            'COMPACT POWDER'            => 'Makeup',
            'SILKY SOFT FACE POWDER'    => 'Makeup',
            'LIGHT SILKY SOFT POWDER'   => 'Makeup',
            'LIGHTENING SILKY'          => 'Makeup',
            'BB -'                      => 'Makeup',
            'BB CUSHION'                => 'Makeup',
            'BB CREAM'                  => 'Makeup',
            'DAY BODY FOUNDATION'       => 'Makeup',
            'AMOUR MATTE LIP'           => 'Makeup',
            'LIPS CREAM'                => 'Makeup',
            'LIPS MATTE'                => 'Makeup',
            'LIPS CARE'                 => 'Makeup',
            'LIPGLOSS'                  => 'Makeup',
            'LIPSTIK'                   => 'Makeup',
            'LIPSKRIM'                  => 'Makeup',
            // PERAWATAN TUBUH
            'BODY FIRMING'              => 'Perawatan Tubuh',
            'FIRMING BODY'              => 'Perawatan Tubuh',
            'DAY BODY LOTION'           => 'Perawatan Tubuh',
            'NIGHT BODY LOTION'         => 'Perawatan Tubuh',
            'LOTION BODY'               => 'Perawatan Tubuh',
            'BODY LOTION'               => 'Perawatan Tubuh',
            'BODY SCRUB'                => 'Perawatan Tubuh',
            'BODY WASH'                 => 'Perawatan Tubuh',
            'HAND BODY'                 => 'Perawatan Tubuh',
            'LULUR'                     => 'Perawatan Tubuh',
            'STRETCH MARK'              => 'Perawatan Tubuh',
            'STRETCHMARK'               => 'Perawatan Tubuh',
            'SULFUR SOAP'               => 'Perawatan Tubuh',
            'KOJIC'                     => 'Perawatan Tubuh',
            'BAMBOO CHARCOAL'           => 'Perawatan Tubuh',
            'COOLBRIGHT'                => 'Perawatan Tubuh',
            'MOISTURIZER GEL'           => 'Perawatan Tubuh',
            'DAILY CERAMOIST'           => 'Perawatan Tubuh',
            // PERAWATAN RAMBUT — HAIR SERUM harus di atas SERUM
            'HAIR SERUM'                => 'Perawatan Rambut',
            'HAIR TONIC'                => 'Perawatan Rambut',
            'ALOE VERA SHAMPOO'         => 'Perawatan Rambut',
            'SHAMPOO'                   => 'Perawatan Rambut',
            // SUPLEMEN & LAINNYA
            "D'ETAWA"                   => 'Suplemen & Lainnya',
            'DETAWA'                    => 'Suplemen & Lainnya',
            'DRW KAPSUL'                => 'Suplemen & Lainnya',
            'DRW SLIMMING'              => 'Suplemen & Lainnya',
            'HB DOSTING'                => 'Suplemen & Lainnya',
            'POUCH'                     => 'Suplemen & Lainnya',
        ];

        foreach ($rules as $keyword => $namaKategori) {
            if (str_contains($upper, strtoupper($keyword))) {
                return $kategoriMap[$namaKategori] ?? null;
            }
        }

        return null;
    }
}