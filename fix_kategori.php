<?php
// Jalankan dari root project Laravel:
// php fix_kategori.php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Produk;

$fix = [
    'Acne Care' => [
        'day cream acne', 'day acne cream',
    ],
    'Brightening Cream' => [
        'day cream pink', 'day cream white',
        'brightening night cream', 'glow cream for men',
    ],
    'Lip Matte / Lip Product' => [
        'lips matte', 'lipstik vit e', 'lipskrim', 'lipkrim',
    ],
    'Body Care' => [
        'lotion body tone up', 'stretch mark cream',
    ],
    'Facial Wash' => [
        'milk cleanser',
    ],
    'Face Mask' => [
        'peel of mask', 'rice mask',
    ],
    'Exfoliating' => [
        'peeling gel',
    ],
    'Sunscreen' => [
        'sunscreen normal skin',
    ],
    'Special Treatment / Spray' => [
        'anti aging eye gel',
    ],
];

$produkList = Produk::whereNull('kategori')->orWhere('kategori', '')->get();
$updated = 0;
$notFound = [];

foreach ($produkList as $p) {
    $nama = strtolower($p->nama_produk);
    $matched = null;

    foreach ($fix as $kat => $kws) {
        foreach ($kws as $kw) {
            if (str_contains($nama, $kw)) {
                $matched = $kat;
                break 2;
            }
        }
    }

    if ($matched) {
        $p->update(['kategori' => $matched]);
        echo "OK  {$p->nama_produk} => {$matched}\n";
        $updated++;
    } else {
        $notFound[] = $p->nama_produk;
    }
}

echo "\n✅ Updated: {$updated}\n";
echo "⚠️  Still not matched: " . count($notFound) . "\n";
foreach ($notFound as $n) {
    echo "   - {$n}\n";
}
