<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\InputPermintaan;
use App\Models\NilaiProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputPermintaanController extends Controller
{
    public function index()
    {
        $kriterias = Kriteria::where('sumber_data', 'Manual')->get();

        // FIX BUG #2 & #5: eager load relasi 'kategori' agar $p->kategori adalah object,
        // lalu akses ->nama_kategori untuk groupBy yang benar.
        // Sebelumnya: ->groupBy(fn($p) => $p->kategori ?: 'Tanpa Kategori')
        //   → $p->kategori memanggil relasi Eloquent dan mengembalikan object/null, bukan string!
        //   → Sehingga semua produk masuk 'Tanpa Kategori'.
        $grouped = Produk::with('kategoriProduk')->get()
            ->groupBy(fn($p) => $p->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori');

        // Pisahkan "Tanpa Kategori" agar bisa ditaruh di paling bawah.
        // sortKeys() sebelumnya mengurutkan alfabet biasa, sehingga "Tanpa Kategori"
        // muncul di tengah daftar (di antara huruf S dan T).
        $tanpaKategori = $grouped->pull('Tanpa Kategori');

        // Sort kategori normal alfabetis
        $produkByKategori = $grouped->sortKeys();

        // Tambahkan "Tanpa Kategori" di akhir (hanya jika ada produknya)
        if ($tanpaKategori) {
            $produkByKategori->put('Tanpa Kategori', $tanpaKategori);
        }

        // Ambil semua inputs, filter max 5 per kategori
        $allInputs = InputPermintaan::all()->groupBy('id_produk');

        $validProdukIds = collect();
        foreach ($produkByKategori as $kategori => $items) {
            $saved = $items
                ->filter(fn($p) => $allInputs->has((string)$p->id_produk))
                ->take(5)
                ->pluck('id_produk')
                ->map(fn($id) => (string)$id);
            $validProdukIds = $validProdukIds->merge($saved);
        }

        $inputs = $allInputs->filter(
            fn($rows, $id) => $validProdukIds->contains((string)$id)
        );

        return view('spk.input-permintaan', compact('produkByKategori', 'kriterias', 'inputs'));
    }

    public function store(Request $request)
    {
        // =========================================================
        // STEP 1 — Decode & validasi struktur payload
        // =========================================================
        if (!$request->has('data')) {
            return response()->json([
                'success' => false,
                'message' => 'Payload tidak berisi field "data".'
            ], 422);
        }

        $data = json_decode($request->input('data'), true);

        if (!is_array($data) || empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak valid atau kosong.'
            ], 422);
        }

        // =========================================================
        // STEP 2 — Whitelist id_kriteria Manual + id_produk yang ada
        //
        // Tujuan: cegah payload menyisipkan id_kriteria milik Excel
        // (akan menimpa nilai_produk dari Excel) atau id_produk palsu.
        // =========================================================
        $manualKriteriaIds = Kriteria::where('sumber_data', 'Manual')
            ->pluck('id_kriteria')
            ->map(fn($id) => (int) $id)
            ->all();

        if (empty($manualKriteriaIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada kriteria bertipe Manual. Tambahkan dulu di Kelola Kriteria.'
            ], 422);
        }

        // Ambil semua id_produk yang dikirim, ke integer, filter yang benar-benar ada di DB
        $payloadProdukIds = array_map('intval', array_keys($data));
        $produkValid = Produk::with('kategoriProduk')
            ->whereIn('id_produk', $payloadProdukIds)
            ->get()
            ->keyBy('id_produk');

        // =========================================================
        // STEP 3 — Validasi tiap baris payload secara strict
        // =========================================================
        $errors    = [];
        $cleanData = [];  // payload yang sudah lulus validasi

        foreach ($data as $rawIdProduk => $kriteriaValues) {
            $idProduk = (int) $rawIdProduk;

            // 3a. id_produk harus ada
            if (!$produkValid->has($idProduk)) {
                $errors[] = "Produk #{$idProduk} tidak ditemukan.";
                continue;
            }

            // 3b. kriteriaValues harus array (dict {id_kriteria: nilai})
            if (!is_array($kriteriaValues) || empty($kriteriaValues)) {
                $errors[] = "Produk #{$idProduk} tidak punya nilai kriteria.";
                continue;
            }

            $cleanRow = [];
            foreach ($kriteriaValues as $rawIdKriteria => $rawNilai) {
                $idKriteria = (int) $rawIdKriteria;

                // 3c. id_kriteria harus Manual
                if (!in_array($idKriteria, $manualKriteriaIds, true)) {
                    $errors[] = "Kriteria #{$idKriteria} bukan kriteria Manual (atau tidak ada).";
                    continue;
                }

                // 3d. Nilai harus integer 1..5
                //     (FILTER_VALIDATE_INT menolak '3.5', '-1', '', 'abc', dst.)
                $nilaiInt = filter_var($rawNilai, FILTER_VALIDATE_INT);
                if ($nilaiInt === false || $nilaiInt < 1 || $nilaiInt > 5) {
                    $errors[] = "Nilai untuk produk #{$idProduk} kriteria #{$idKriteria} harus integer 1–5 (diterima: " . json_encode($rawNilai) . ").";
                    continue;
                }

                $cleanRow[$idKriteria] = $nilaiInt;
            }

            if (empty($cleanRow)) {
                continue; // Semua kriteria row ini gagal validasi; skip produk ini
            }

            $cleanData[$idProduk] = $cleanRow;
        }

        if (!empty($errors)) {
            // Tampilkan max 5 error pertama supaya pesan tidak meledak
            $shown = array_slice($errors, 0, 5);
            $more  = count($errors) > 5 ? ' (+' . (count($errors) - 5) . ' kesalahan lain)' : '';
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(' ', $shown) . $more,
                'errors'  => $errors,
            ], 422);
        }

        if (empty($cleanData)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data valid untuk disimpan.'
            ], 422);
        }

        // =========================================================
        // STEP 4 — Batas max 5 produk per kategori (sesuai aturan UI)
        // =========================================================
        $perKategori = [];
        foreach (array_keys($cleanData) as $idProduk) {
            $kat = $produkValid[$idProduk]->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori';
            $perKategori[$kat][] = $idProduk;
        }

        $allowedIds = [];
        $rejected   = [];
        foreach ($perKategori as $kat => $ids) {
            $head = array_slice($ids, 0, 5);
            $tail = array_slice($ids, 5);
            $allowedIds = array_merge($allowedIds, $head);
            if (!empty($tail)) {
                $rejected[$kat] = count($tail);
            }
        }

        if (!empty($rejected)) {
            $msg = collect($rejected)->map(fn($n, $k) => "{$n} produk di kategori \"{$k}\"")->implode(', ');
            return response()->json([
                'success' => false,
                'message' => "Maksimal 5 produk per kategori. Kelebihan: {$msg}."
            ], 422);
        }

        $finalData = array_intersect_key($cleanData, array_flip($allowedIds));

        // =========================================================
        // STEP 5 — Tulis ke DB dalam transaction
        //
        // WORKFLOW ASLI: payload = "set aktif lengkap" untuk sesi penilaian.
        // Produk yang di-uncheck di Step 1 = harus dihapus nilai manualnya.
        // Jadi: hapus SEMUA nilai manual dulu, lalu reinsert berdasarkan payload.
        //
        // PERBEDAAN DARI KODE LAMA:
        // 1. Pakai DB::transaction dengan throw → atomic, rollback total kalau error
        // 2. Validasi dilakukan SEBELUM masuk transaction (di Step 3) — kalau ada
        //    nilai invalid, kita NGGAK hapus apa-apa, langsung reject di awal
        // 3. Status_data direcalc untuk SEMUA produk yang pernah punya nilai
        //    manual (tidak cuma yang ada di payload). Bug lama: subquery
        //    jalan SETELAH delete sehingga selalu kosong.
        // 4. Pakai bulk insert (lebih cepat dari N kali updateOrCreate)
        // =========================================================
        try {
            DB::transaction(function () use ($finalData, $manualKriteriaIds, $payloadProdukIds) {

                // 5a. Ambil dulu daftar SEMUA produk yang punya nilai manual saat ini.
                //     Ini PENTING untuk recalc status_data di akhir — kalau cuma
                //     pakai $payloadProdukIds, produk yang di-uncheck tidak akan
                //     di-recalc, status_data-nya tetap 'Lengkap' padahal nilainya
                //     sudah dihapus.
                $produkLama = NilaiProduk::whereIn('id_kriteria', $manualKriteriaIds)
                    ->pluck('id_produk')
                    ->unique()
                    ->all();

                // Gabungkan: produk lama (yang akan kehilangan nilai) + produk baru di payload
                $allAffectedProdukIds = array_unique(array_merge(
                    $produkLama,
                    array_keys($finalData)
                ));

                // 5b. Hapus SEMUA nilai manual lama (Excel-driven nilai tidak disentuh
                //     karena where-nya cuma kriteria Manual).
                InputPermintaan::whereIn('id_kriteria', $manualKriteriaIds)->delete();
                NilaiProduk::whereIn('id_kriteria', $manualKriteriaIds)->delete();

                // 5c. Insert nilai baru (bulk insert, lebih cepat)
                $now = now();
                $inputRows = [];
                $nilaiRows = [];

                foreach ($finalData as $idProduk => $kriteriaValues) {
                    foreach ($kriteriaValues as $idKriteria => $nilai) {
                        $inputRows[] = [
                            'id_produk'   => $idProduk,
                            'id_kriteria' => $idKriteria,
                            'nilai_input' => $nilai,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                        $nilaiRows[] = [
                            'id_produk'   => $idProduk,
                            'id_kriteria' => $idKriteria,
                            'nilai'       => (float) $nilai,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    }
                }

                if (!empty($inputRows)) {
                    InputPermintaan::insert($inputRows);
                    NilaiProduk::insert($nilaiRows);
                }

                // 5d. Recalc status_data untuk SEMUA produk yang affected
                //     (yang lama kehilangan nilai + yang baru dapat nilai)
                $totalKriteria = Kriteria::count();

                foreach ($allAffectedProdukIds as $idProduk) {
                    $totalNilai = NilaiProduk::where('id_produk', $idProduk)->count();
                    Produk::where('id_produk', $idProduk)->update([
                        'status_data' => ($totalKriteria > 0 && $totalNilai >= $totalKriteria)
                            ? 'Lengkap'
                            : 'Belum Lengkap',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            \Log::error('InputPermintaan store error', [
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'user'  => optional(auth()->user())->id_user,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi atau hubungi administrator.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Penilaian berhasil disimpan untuk ' . count($finalData) . ' produk.'
        ]);
    }
}