<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class KategoriProdukSeeder extends Seeder
{
    /**
     * Jalankan dengan: php artisan db:seed --class=KategoriProdukSeeder
     */
    public function run(): void
    {
        // Map kategori => keyword (lowercase)
        // Urutan PENTING: lebih spesifik di atas, lebih umum di bawah
        $kategoriMap = [
            'Acne Care' => [
                'acne cream', 'acne brightening', 'soft acne', 'day acne',
                'serum for acne', 'serum acne', 'facial wash oily acne',
                'facial wash tea tree', 'facial wash hijau acne',
                'acne brightening cream for men', 'sunscreen acne',
                'sunscreen for oily and acne', 'sunscreen oily acne',
                'peel off mask for oily and acne',
            ],
            'Brightening Cream' => [
                'brightening cream', 'day pink cream', 'day white cream',
                'night cream', 'snail cream', 'cnr', 'glowtech',
                'soft brightening cream for men', 'radiant bright',
                'luminous brightening', 'serum brightening',
            ],
            'Lip Matte / Lip Product' => [
                'lip cream matte', 'lips cream matte', 'amour matte lip',
                'lipstik matte', 'lipgloss', 'lips care', 'lip matte',
            ],
            'Cushion / BB Cream / Powder' => [
                'cushion', 'bb cream', 'bb -', 'compact powder',
                'daily compact powder', 'silky soft face powder',
                'silky soft powder', 'lightening silky', 'face powder',
                'setting powder', 'loose powder',
            ],
            'Facial Wash' => [
                'facial wash', 'face wash',
            ],
            'Toner' => [
                'toner', 'face toner', 'hydrating essence toner',
                'exfoliating complex toner',
            ],
            'Serum & Essence' => [
                'serum', 'essence', 'dna salmon extra marine collagen',
                'beauty dna salmon', 'radiant glow booster',
            ],
            'Moisturizer & Gel' => [
                'moisturizer gel', 'daily ceramoist', 'hydra gel',
                'aloe vera gel', 'face mist', 'tinted moisturizer',
            ],
            'Sunscreen' => [
                'sunscreen', 'sunblok', 'spf',
            ],
            'Face Mask' => [
                'face mask', 'mask premium', 'rice face mask',
                'green tea face mask', 'honey face mask', 'tea tree oil face mask',
                'brightening peel off mask',
            ],
            'Exfoliating' => [
                'exfoliating', 'aha bha', 'serum aha',
            ],
            'Cleansing & Micellar' => [
                'micellar', 'cleansing milk', 'bloome',
            ],
            'Body Care' => [
                'body lotion', 'body cream', 'body wash', 'body scrub',
                'body firming', 'firming body', 'night body lotion',
                'stretchmark', 'lulur', 'hand body', 'day body lotion',
                'day body foundation',
            ],
            'Hair Care' => [
                'shampoo', 'hair tonic', 'hair serum', 'aloe vera shampoo',
            ],
            'Soap' => [
                'soap', 'kojic',
            ],
            'Deodorant' => [
                'deo', 'deodorant', 'coolbright',
            ],
            'Supplement & Slimming' => [
                'slimming', 'kapsul gemuk', 'kapsul', 'd\'etawa', 'susu etawa',
                'collagen rasa',
            ],
            'Special Treatment / Spray' => [
                'spray', 'beauty dna', 'glowtech spicule', 'hb dosting',
                'stretchmark', 'breast cream',
            ],
        ];

        $produkList = Produk::all();
        $updated    = 0;
        $notFound   = [];

        foreach ($produkList as $produk) {
            $nama       = strtolower($produk->nama_produk);
            $matched    = null;

            foreach ($kategoriMap as $kategori => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($nama, strtolower($kw))) {
                        $matched = $kategori;
                        break 2;
                    }
                }
            }

            if ($matched) {
                $produk->update(['kategori' => $matched]);
                $updated++;
            } else {
                $notFound[] = $produk->nama_produk;
            }
        }

        $this->command->info("✅ {$updated} produk berhasil dikategorikan.");

        if (count($notFound) > 0) {
            $this->command->warn('⚠️  ' . count($notFound) . ' produk tidak masuk kategori manapun:');
            foreach ($notFound as $name) {
                $this->command->line('   - ' . $name);
            }
        }
    }
}