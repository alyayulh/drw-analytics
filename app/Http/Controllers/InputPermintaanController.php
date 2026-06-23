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

        #mengelompokkan produk berdasarkan kategori, jika tidak ada masuk ke tanpa kategori
        $grouped = Produk::with('kategoriProduk')->get()
            ->groupBy(fn($p) => $p->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori');


        $tanpaKategori = $grouped->pull('Tanpa Kategori');

        #Sort kategori alfabetis
        $produkByKategori = $grouped->sortKeys();

        #Tambahkan "Tanpa Kategori" di akhir (hanya jika ada produknya)
        if ($tanpaKategori) {
            $produkByKategori->put('Tanpa Kategori', $tanpaKategori);
        }
        #Supaya data input permintaan yang sudah pernah disimpan bisa muncul lagi di halaman, tapi tetap dibatasi maksimal 5 produk per kategori.
        #1. ambil data input manual yang pernah tersimpan & kelompokkan berdasarkan id
        #2. simpan data input manual tersebut ke collections untuk menyimpan id produk yang valid. 
            #valid maksudnya, produk yang sudah pernah di input dan masuk batas 5 produk.
        #3. jika ada, lanjut ke menerapkan batas 5 maksimal produk. 
        #4. id_produk yang valid digabungkan ke variabel validProdukIds.
        # data input lama di filter agar hanya input yang valid yang dikirim ke view.

        #mengambil data input manual yang pernah disimpan & kelompokkan berdasarkan id
        $allInputs = InputPermintaan::all()->groupBy('id_produk');

        #menentukan produk valid maksimal 5 produk dengan membuat collcetions/fungsi laravel. dengan batas maksimal 5 produk.
        $validProdukIds = collect();
        foreach ($produkByKategori as $kategori => $items) {
            $saved = $items
            #ambil produk yang sudah pernah diinputkan nilainya.
                ->filter(fn($p) => $allInputs->has((string)$p->id_produk)) #kenapa string? karna hasil groupby(id_produk) mengubah key jadi string.
                ->take(5) 
                ->pluck('id_produk')
                ->map(fn($id) => (string)$id); #mengubah id_produk jadi string.
            $validProdukIds = $validProdukIds->merge($saved);
        }

        #mengambil produk yang id produknya valid dan tampilkan.
        $inputs = $allInputs->filter(
            fn($rows, $id) => $validProdukIds->contains((string)$id)
        );

        return view('spk.input-permintaan', compact('produkByKategori', 'kriterias', 'inputs'));
    }

    public function store(Request $request)
    {
        #apakah permintaan yang dikirim ada datanya. jika tidak akan menolak.
        #422 data yg dikirim tidak valid
        if (!$request->has('data')) {
            return response()->json([
                'success' => false,
                'message' => 'Payload tidak berisi field "data".'
            ], 422);
        }
        #mengubah json jadi array
        $data = json_decode($request->input('data'), true);

        #validasi format data (kalo bukan array atau kosong, ditolak)
        if (!is_array($data) || empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak valid atau kosong.'
            ], 422);
        }
        // mengecek kriteria manual & produk yang dikirim beneran ada didatabase.
        #mengambil id kriteria manual
        $manualKriteriaIds = Kriteria::where('sumber_data', 'Manual')
            ->pluck('id_kriteria')
            ->map(fn($id) => (int) $id)
            ->all(); #diubah dari collections jadi array.

        #mengecek apakah ada kriteria manual. 
        if (empty($manualKriteriaIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada kriteria bertipe Manual. Tambahkan dulu di Kelola Kriteria.'
            ], 422);
        }

        // Ambil semua id_produk yang dikirim & cek produk benar-benar ada di DB
        #mengambil id produk dari data yang dikirim halaman lalu diubah jadi integer.
        $payloadProdukIds = array_map('intval', array_keys($data)); 
        #mengambil data produk sekalgius dengan kategori dari database.
        $produkValid = Produk::with('kategoriProduk')
            ->whereIn('id_produk', $payloadProdukIds) #mengambil produk yang id ada di daftar tadi.
            ->get() # ambil dari database
            ->keyBy('id_produk'); #ubah jadi collection dengan key=id_produk supaya mudah dicek has(idproduk)

        $errors    = [];   // data yang tidak ditemukan.
        $cleanData = [];  // data yang sudah lulus validasi.

        #mengecek satu per satu produk &mengubah jadi integer
        foreach ($data as $rawIdProduk => $kriteriaValues) {
            $idProduk = (int) $rawIdProduk;

            // 3a. id_produk harus ada di daftar produkvalid, jika tidak ada dilewati
            if (!$produkValid->has($idProduk)) {
                $errors[] = "Produk #{$idProduk} tidak ditemukan.";
                continue;
            }

            // 3b. cek apakah produk punya nilai kriteria harus array dan tidak boleh kosong.
            if (!is_array($kriteriaValues) || empty($kriteriaValues)) {
                $errors[] = "Produk #{$idProduk} tidak punya nilai kriteria.";
                continue;
            }

            $cleanRow = []; //data valid per produk
            #mengecek nilai kriteria produk & diubah jadi integer agar cocok saat di cek array $manualKriteriaIds
            foreach ($kriteriaValues as $rawIdKriteria => $rawNilai) {
                $idKriteria = (int) $rawIdKriteria;

                #id_kriteria harus Manual
                if (!in_array($idKriteria, $manualKriteriaIds, true)) {
                    $errors[] = "Kriteria #{$idKriteria} bukan kriteria Manual (atau tidak ada).";
                    continue;
                }

                #Nilai harus integer 1-5
                $nilaiInt = filter_var($rawNilai, FILTER_VALIDATE_INT);
                #3 kondisi salah: nilai kurang dari 1, nilai lebih dari 5 atau nilai bukan integer.
                if ($nilaiInt === false || $nilaiInt < 1 || $nilaiInt > 5) {
                    $errors[] = "Nilai untuk produk #{$idProduk} kriteria #{$idKriteria} harus integer 1–5 (diterima: " . json_encode($rawNilai) . ").";
                    continue;
                }
                $cleanRow[$idKriteria] = $nilaiInt;
            }
            #kalo tidak valid, lanjut ke produk berikutnya kalo valid simpan ke cleanRow
            if (empty($cleanRow)) {
                continue; 
            }
            #menyimpan 1 produk yg valid ke cleandata
            $cleanData[$idProduk] = $cleanRow;
        }
        #pesan error jika ada error.
        if (!empty($errors)) {
            $shown = array_slice($errors, 0, 5);
            #error lebih dari 5
            $more  = count($errors) > 5 ? ' (+' . (count($errors) - 5) . ' kesalahan lain)' : '';
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(' ', $shown) . $more, #implode gabungkan beberapa pesan error jadi 1 string dipisah spasi.
                'errors'  => $errors, #menyimpan error
            ], 422);
        }

        #jika data yang valid kosong, hentikan proses.
        if (empty($cleanData)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data valid untuk disimpan.'
            ], 422);
        }

        $perKategori = []; //kelompokkan produk per kategori.
        #mengambil idproduk dari data bersih, nama kategori, dan masukkan id produk ke kategori tersebut.
        foreach (array_keys($cleanData) as $idProduk) {
            $kat = $produkValid[$idProduk]->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori';
            $perKategori[$kat][] = $idProduk;
        }

        $allowedIds = []; //produk yang diizinkan
        $rejected   = [];
        #mengecek setiap produk perkategori  dengan 
        foreach ($perKategori as $kat => $ids) {
            $head = array_slice($ids, 0, 5); #mengambil 5 produk pertama.
            $tail = array_slice($ids, 5); #mengambil sisanya setelah 5 produk.
            $allowedIds = array_merge($allowedIds, $head); #memasukkan 5 produk pertama ke daftar yang diizinkan.
            #jika ada produk lebih dari 5, masuk ditolak. 
            if (!empty($tail)) {
                $rejected[$kat] = count($tail);
            }
        }
        #jika ada produk yang ditolak.
        if (!empty($rejected)) {
            #mengubah array rejected jadi collection agar bisa pakai method map dan implode.
            #kenapa ga array? karna kalo fungsi array cm bisa akses untuk nilai gabisa untuk kategori.collection lebih ringkas.  
            $msg = collect($rejected)->map(fn($n, $k) => "{$n} produk di kategori \"{$k}\"")->implode(', '); #mengubah jadi kalimat dengan menggabungkan dengan spasi.
            return response()->json([
                'success' => false,
                'message' => "Maksimal 5 produk per kategori. Kelebihan: {$msg}."
            ], 422);
        }
        #data final isinya data valid & id produk yang diizinkan.
        $finalData = array_intersect_key($cleanData, array_flip($allowedIds));
        #kenapa di flip posisinya value jadi key dluan? 
        #karna dipakai array_intersect_key yang mencocokkan berdasarkan key bukan value.

        #menyimpan data ke database pake transaction
        try {
            DB::transaction(function () use ($finalData, $manualKriteriaIds, $payloadProdukIds) {

               #mengambil produk lama yang pernah punya nilai input manual 
                $produkLama = NilaiProduk::whereIn('id_kriteria', $manualKriteriaIds)
                    ->pluck('id_produk')
                    ->unique() #menghapus id duplikat.
                    ->all(); #mengubah collection jadi array.

                // Gabungkan: produk lama  + produk yang baru dikirim & hapus id yang sama. 
                $allAffectedProdukIds = array_unique(array_merge(
                    $produkLama,
                    array_keys($finalData)
                ));
    
                // Hapus semua nilai manual lama 
                InputPermintaan::whereIn('id_kriteria', $manualKriteriaIds)->delete();
                NilaiProduk::whereIn('id_kriteria', $manualKriteriaIds)->delete();

                #waktu sekarang
                $now = now();
                $inputRows = []; // data yang masuk ke tabel inputpermintaan
                $nilaiRows = []; // data yang masuk ke nilaiproduk
                #ngecek setiap produk beserta nilai kriteria
                foreach ($finalData as $idProduk => $kriteriaValues) {
                    #ngecek  kriteria beserta nilai setiap produk    
                    foreach ($kriteriaValues as $idKriteria => $nilai) {
                        #menyimpan ke tabel input permintaan
                        $inputRows[] = [
                            'id_produk'   => $idProduk,
                            'id_kriteria' => $idKriteria,
                            'nilai_input' => $nilai,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                        #menyimpan ke tabel nilai_produk
                        $nilaiRows[] = [
                            'id_produk'   => $idProduk,
                            'id_kriteria' => $idKriteria,
                            'nilai'       => (float) $nilai,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    }
                }
                
                #jika tidak kosong, simpan ke tabel input permintaan & nilai produk. (bulk insert menyimpan data banyak sekaligus)
                if (!empty($inputRows)) {
                    InputPermintaan::insert($inputRows);
                    NilaiProduk::insert($nilaiRows);
                }

                #hitung jumlah kriteria
                $totalKriteria = Kriteria::count();

                #mengecek produk yang terdampak seperti, produk lama yang nilainya dihapus atau produk baru yang nilainya ditambahkan.
                foreach ($allAffectedProdukIds as $idProduk) {
                    #hitung jumlah nilai kriteria setiap produk.
                    $totalNilai = NilaiProduk::where('id_produk', $idProduk)->count();
                    #update status data produk. lebih dari 0 dan jumlah nilai dan kriteria sama.
                    Produk::where('id_produk', $idProduk)->update([
                        'status_data' => ($totalKriteria > 0 && $totalNilai >= $totalKriteria)
                            ? 'Lengkap'
                            : 'Belum Lengkap',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            #menyimpan error ke laravel 
            #Log ini berguna untuk developer/admin saat mencari penyebab error.
            \Log::error('InputPermintaan store error', [
                'msg'   => $e->getMessage(), //pesan error
                'file'  => $e->getFile(),   //file error
                'line'  => $e->getLine(),   //baris kode error
                'user'  => optional(auth()->user())->id_user, //user yang sedang login.
            ]);

            #mengirim pesan gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi atau hubungi administrator.'
            ], 500);
        }
        #pesan berhasil
        return response()->json([
            'success' => true,
            'message' => 'Penilaian berhasil disimpan untuk ' . count($finalData) . ' produk.'
        ]);
    }
}