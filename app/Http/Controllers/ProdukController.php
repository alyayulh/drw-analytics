<?php

namespace App\Http\Controllers; 

use App\Models\Produk;
use App\Models\NilaiProduk;
use App\Models\Kriteria;
use App\Models\KategoriProduk;
use App\Models\InputPermintaan;
use Illuminate\Http\Request; #untuk menangani request HTTP, validasi input, dll.
use Illuminate\Support\Facades\DB; #untuk transaksi database saat import produk dari Excel
use Shuchkin\SimpleXLSX; #membaca file Excel untuk fitur import produk dari Excel. di install package shuchkin/simplexlsx via composer.

class ProdukController extends Controller
{
    #membuat method index() untuk menampilkan halaman data produk. 
    #method ini akan mengambil data produk beserta relasi nilaiProduk, kriteria, dan kategoriProduk, disimpan dalam variabel $produks 
    #lalu ditampilkan di view spk.data-produk.
    public function index()
    {
        #untuk sorting produk berdasarkan query parameter 'sort' (default: 'abjad'). opsi: 'abjad', 'terbaru', 'terlama'.
        $sortBy = request('sort', 'abjad');
    
        $produks = Produk::with(['nilaiProduk.kriteria', 'kategoriProduk'])
            ->when($sortBy === 'abjad',   fn($q) => $q->orderBy('nama_produk', 'asc'))
            ->when($sortBy === 'terbaru', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when($sortBy === 'terlama', fn($q) => $q->orderBy('created_at', 'asc'))
            ->get(); 

        #ambil data kriteria yang sumber datanya 'Manual' untuk ditampilkan di halaman data produk (misal: di form input nilai kriteria manual)
        $kriteriaManual = Kriteria::where('sumber_data', 'Manual')->get();
        $semuaKriteria  = Kriteria::all(); #ambil semua data kriteria untuk kebutuhan lain (misal: mapping kolom Excel ke kriteria)
        $kriteriaExcel  = Kriteria::where('sumber_data', 'Excel') #ambil kriteria yang sumber datanya 'Excel' untuk kebutuhan mapping kolom Excel di fitur import produk dari Excel
            ->whereNotNull('nama_kolom_excel') #hanya ambil kriteria Excel yang punya nama_kolom_excel karena ini yang relevan untuk mapping saat import Excel
            ->get(); 

        // List kategori untuk dropdown di modal Tambah & Edit
        $kategoris = KategoriProduk::orderBy('nama_kategori')->get();
        // Untuk warning banner: berapa produk yang belum berkategori
        $produkBelumBerkategori = $produks->whereNull('id_kategori')->count();


        #mengirim data produk, kriteria manual dll ke view spk.data-produk untuk ditampilkan di halaman data produk
        return view('spk.data-produk', compact(
            'produks', 'kriteriaManual', 'semuaKriteria', 'kriteriaExcel',
            'kategoris', 'produkBelumBerkategori'
        ));
    }
    #mengambil data produk manual
    #membuat method store() untuk menyimpan data produk baru yang diinput melalui form di halaman data produk.
    #method ini akan menerima request dari form, melakukan validasi input, menyimpan data produk baru ke database, dan mengarahkan kembali ke halaman data produk.
    #memanggil method validate() milik $request untuk melakukan validasi input.
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'id_kategori'      => 'nullable|integer|exists:kategori_produk,id_kategori', #nulable karena kategori boleh kosong (produk tanpa kategori),  exists untuk memastikan id_kategori yang dipilih valid dan ada di tabel kategori_produk
            'nilai_kriteria'   => 'nullable|array', #array karena nilai_kriteria akan dikirim sebagai array dengan format nilai_kriteria[id_kriteria] => nilai, nullable karena input nilai kriteria bersifat opsional (tidak wajib diisi saat tambah produk baru)
            // Skala 1-5 sesuai constraint CHECK di tabel input_permintaan
            'nilai_kriteria.*' => 'nullable|integer|between:1,5',
        ]);

        
        #mengambil daftar id kriteria yang valid untuk input nilainya secara manual.
        #lalu ubah  setiap id_kriteria menjadi string agar bisa dibandingkan dengan nilai id_kriteria yang dikirim dari form (yang berupa string) saat validasi input nilai kriteria manual.
        #hasilnya disimpan dalam array $manualKriteriaIds yang berisi string id_kriteria yang valid untuk input manual.
        #untuk mengecek apakah id_kriteria yang dikirim dari form input nilai kriteria manual termasuk dalam daftar kriteria yang valid untuk input manual.
        #jika tidak termasuk, maka nilai kriteria tersebut akan diabaikan dan tidak disimpan ke database.
        $manualKriteriaIds = Kriteria::where('sumber_data', 'Manual')
                ->pluck('id_kriteria')->map(fn($id) => (string)$id)->all(); 
        

        // Prioritas penentuan kategori:
        // 1) Dropdown eksplisit dari admin, 2) Auto-resolve dari nama, 3) null (tanpa kategori)
        $idKategori = $request->filled('id_kategori')
            ? (int) $request->id_kategori
            : $this->resolveKategori($request->nama_produk);

    
        #DB::transaction() untuk memastikan semua operasi database di dalamnya (membuat produk baru, menyimpan nilai kriteria) berhasil. 
        #Jika ada error di tengah proses, seluruh perubahan akan di-rollback sehingga database tetap konsisten.
        DB::transaction(function () use ($request, $idKategori, $manualKriteriaIds) {
            #menyimpan data produk baru ke database dengan menggunakan method create() dari model Produk.
            #status_data di-set 'Belum Lengkap' karena saat tambah produk baru, nilai kriteria belum diisi sehingga data dianggap belum lengkap.
            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $idKategori,
                'status_data' => 'Belum Lengkap',
            ]);

            #validasi dan simpan nilai kriteria manual jika ada.
            #disimpan ke dua tabel: input_permintaan (nilai_input) dan nilai_produk (nilai) agar konsisten antara data yang diinput melalui halaman Input Permintaan dengan data dalam perhitungan.
            #nilai produk yang akan dibaca dalam perhitungan ranking produk baik manual maupun excel.
            #awalnya nilai_produk untuk menyimpan nilai dari excel saja, tapi untuk konsistensi dan kemudahan perhitungan, kita simpan juga nilai manual ke nilai_produk.
            if ($request->filled('nilai_kriteria')) {
                foreach ($request->nilai_kriteria as $idKriteria => $nilai) {
                    if ($nilai === null || $nilai === '') continue;
                    if (!in_array((string)$idKriteria, $manualKriteriaIds, true)) continue;

                    $nilaiInt = (int) $nilai;

                    // Tulis ke DUA tabel agar konsisten dgn halaman Input Permintaan
                    #updateOrCreate mengecek apakah sudah ada baris dengan id_produk dan id_kriteria yang sama di tabel input_permintaan dan nilai_produk.
                    InputPermintaan::updateOrCreate(
                        ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                        ['nilai_input' => $nilaiInt]
                    );
                    NilaiProduk::updateOrCreate(
                        ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                        ['nilai' => (float) $nilaiInt]
                    );
                }
            }
            #mengupdate status data produk.
            $this->updateStatusProduk($produk);
        });
        #diarahkan kembali ke halaman data produk dengan menampilkan pesan sukses bahwa produk berhasil ditambahkan.
        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    #method preview() untuk menampilkan preview data produk yang akan diimpor dari file Excel. 
    #method ini  menerima request dari form upload file Excel, melakukan validasi file, membaca isi file Excel, memetakan kolom Excel ke kriteria, dan menyimpan data preview ke session agar bisa ditampilkan di halaman preview.
    public function preview(Request $request) 
    {
        #validasi file yang diupload. harus berupa file Excel (xlsx/xls) dan ukuran maksimal 10MB.
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);
        #ambil file Excel yang diupload dan simpan sementara di folder storage/app/temp dengan nama unik (timestamp + nama file asli) agar tidak menimpa file yang namanya sama.
       #disimpan sementara karena belum diimpor ke database, hanya untuk preview dulu. nanti setelah konfirmasi import, file ini akan dibaca lagi untuk disimpan ke database.
        $file     = $request->file('file_excel');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName()); #membersihkan nama file dari karakter yang tidak aman untuk nama file.

        #buat folder baru jika belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        #pindahkan file ke folder yang dibuat (temp).
        $file->move(storage_path('app/temp'), $filename);
        $path = storage_path('app/temp/' . $filename);

        #menggunakan library SimpleXLSX untuk membaca isi file Excel. 
        #jika gagal membaca, hapus file sementara dan kembalikan ke halaman sebelumnya dengan pesan error.  
        if (!$xlsx = SimpleXLSX::parse($path)) {
            @unlink($path);
            return back()->with('error', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        }

        $rows = $xlsx->rows();

        #mengecek apakah file Excel memiliki cukup baris data (minimal 2 baris: header + 1 data).
        if (count($rows) < 2) {
            @unlink($path);
            return back()->with('error', 'File Excel kosong atau tidak punya cukup baris data.');
        }

        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();
        #mendeteksi baris header di file Excel dengan memanggil method detectHeaderRow().
        [$headerRowIdx, $headers] = $this->detectHeaderRow($rows, $kriteriaExcel);

        #jika kolom "NAMA BARANG" tidak ditemukan di file Excel, hapus file sementara dan kembalikan ke halaman sebelumnya dengan pesan error.
        if ($headerRowIdx === null) {
            @unlink($path); 
            return back()->with('error',
                'Kolom "NAMA BARANG" tidak ditemukan di file Excel (dicari di 15 baris pertama).'
            );
        }
        
        #mencocokkan kolom-kolom di file Excel dengan kriteria yang ada di database.
        #dipisah menjadi 3 array: kolom yang ditemukan, kolom yang tidak ada untuk informasi di halaman preview.
        $colNama        = array_search('NAMA BARANG', $headers);
        $kolomMap       = [];
        $kolomDitemukan = [];
        $kolomTidakAda  = [];

        foreach ($kriteriaExcel as $kriteria) {
            #mencocokkan nama kriteria dengan kolom excel. trim = hapus spasi, 
            $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
            #mencari kolomexcel ada di posisi berapa pada headers.
            $colIdx     = array_search($kolomExcel, $headers);

            if ($colIdx !== false) {
                $kolomMap[$kriteria->id_kriteria] = $colIdx;
                $kolomDitemukan[] = [
                    'nama_kriteria'    => $kriteria->nama_kriteria,
                    'nama_kolom_excel' => $kriteria->nama_kolom_excel,
                ];
            } else {
                $kolomTidakAda[] = [
                    'nama_kriteria'    => $kriteria->nama_kriteria,
                    'nama_kolom_excel' => $kriteria->nama_kolom_excel,
                ];
            }
        }

        #menghitung total baris data yang valid di file Excel (baris yang memiliki nama produk).
        #untuk preview, hanya menampilkan maksimal 5 baris data agar tidak terlalu panjang di halaman preview.
        $dataStart   = $headerRowIdx + 1; #posisi baris header. 
        $previewRows = [];
        $totalData   = 0;

        #perulangan untuk membaca data produk dari baris pertama sampai baris akhir. 
        for ($i = $dataStart; $i < count($rows); $i++) {
            #mengambil nama produk dari colnama / nama barang. kalo gada dianggap kosong.
            $nama = trim($rows[$i][$colNama] ?? '');
            if (!$nama) continue;
            $totalData++; #hitung total produk.

            if (count($previewRows) < 5) {
                $rowData = ['nama_produk' => $nama, 'nilai' => []];
                foreach ($kolomMap as $idKriteria => $colIdx) {
                    #mengambil kriteria excel dan mengisi nilai sesuai kolom
                    $k = $kriteriaExcel->firstWhere('id_kriteria', $idKriteria);
                    $rowData['nilai'][$k->nama_kriteria] = $this->parseAngka($rows[$i][$colIdx] ?? ''); #parseAngka = pastikan nilai berubah jadi angka
                }
                $previewRows[] = $rowData;
            }
        }

        if ($totalData === 0) {
            @unlink($path);
            return back()->with('error', 'Tidak ada baris data yang valid ditemukan di file Excel.');
        }
        #menyimpan data preview ke session agar bisa ditampilkan di halaman preview. 
        #data yang disimpan: nama file, total data, kolom yang ditemukan, kolom yang tidak ada, dan baris preview.
        session([
            'import_filename'        => $filename,
            'import_total_data'      => $totalData,
            'import_kolom_ditemukan' => $kolomDitemukan,
            'import_kolom_tidak_ada' => $kolomTidakAda,
            'import_preview_rows'    => $previewRows,
        ]);

        return redirect()->route('produk.preview.show');
    }
    #method showPreview() untuk menampilkan halaman preview data produk yang sudah diunggah dari file Excel.
    public function showPreview()
    {
        #mengecek apakah ada file yang sedang diproses (disimpan di session).
        #jika tidak ada, maka diarahkan kembali ke halaman data produk
        if (!session('import_filename')) {
            return redirect()->route('produk.index')
                ->with('error', 'Tidak ada file yang sedang diproses. Silakan upload ulang.');
        }
        #mengambil data kriteria yang sumber datanya 'Excel' dan memiliki nama_kolom_excel untuk ditampilkan di halaman preview.
        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();
        
        #mengirim data preview ke view spk.import-preview untuk ditampilkan di halaman preview.
        return view('spk.import-preview', [
            'filename'       => session('import_filename'),
            'totalData'      => session('import_total_data'),
            'kolomDitemukan' => session('import_kolom_ditemukan'),
            'kolomTidakAda'  => session('import_kolom_tidak_ada'),
            'previewRows'    => session('import_preview_rows'),
            'kriteriaExcel'  => $kriteriaExcel,
        ]);
    }

    #method importConfirm() untuk mengonfirmasi dan menyimpan data produk dari file Excel ke database.
    public function importConfirm(Request $request)
    {
        #mengambil nama file yang sedang diproses dari session.
        $filename = session('import_filename');

        #jika tidak ada file yang sedang diproses (misal karena session sudah kedaluwarsa), maka diarahkan kembali ke halaman data produk
        if (!$filename) {
            return redirect()->route('produk.index')
                ->with('error', 'Sesi import sudah kedaluwarsa. Silakan upload ulang.');
        }

        #mengecek apakah file sementara masih ada di folder storage/app/temp. jika tidak ada, maka diarahkan kembali ke halaman data produk dengan pesan error.
        $path = storage_path('app/temp/' . $filename);

        if (!file_exists($path)) {
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'File sementara tidak ditemukan. Silakan upload ulang.');
        }
        #membaca file Excel menggunakan library SimpleXLSX. jika gagal membaca, hapus file sementara dan kembalikan ke halaman data produk dengan pesan error.
        if (!$xlsx = SimpleXLSX::parse($path)) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        }

        #mengambil semua baris dari file Excel dan kriteria yang sumber datanya 'Excel' untuk memetakan kolom Excel ke kriteria.
        $rows          = $xlsx->rows();
        #mengambil semua kriteria yang sumber datanya 'Excel' dan memiliki nama_kolom_excel untuk memetakan kolom Excel ke kriteria.
        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        #mendeteksi baris header di file Excel dengan memanggil method detectHeaderRow().
        [$headerRowIdx, $headers] = $this->detectHeaderRow($rows, $kriteriaExcel);

        #jika baris header tidak ditemukan, hapus file sementara dan kembalikan ke halaman data produk dengan pesan error.
        if ($headerRowIdx === null) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Gagal menemukan header saat konfirmasi import.');
        }

        #mencari kolom "NAMA BARANG" di file Excel
        $colNama  = array_search('NAMA BARANG', $headers);
        $kolomMap = [];

        #mencocokkan kolom-kolom di file Excel dengan kriteria yang ada di database.
        foreach ($kriteriaExcel as $kriteria) {
            $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
            $colIdx     = array_search($kolomExcel, $headers);
            #jika kolom ditemukan, simpan mapping id_kriteria ke index kolom di array $kolomMap. nanti akan digunakan untuk membaca nilai kriteria dari file Excel saat menyimpan ke database.
            if ($colIdx !== false) {
                $kolomMap[$kriteria->id_kriteria] = $colIdx;
            }
        }
        #variabel untuk menghitung jumlah produk yang berhasil diimpor, diperbarui, dan dilewati (baris kosong).
        $imported = 0;
        $updated  = 0;
        $skipped  = 0;

        #menggunakan DB::transaction() untuk memastikan seluruh proses import berjalan atomik. jika ada error di tengah proses, seluruh perubahan akan di-rollback sehingga database tetap konsisten.
        try {
            DB::transaction(function () use (
                $rows, $headerRowIdx, $colNama, $kolomMap,
                &$imported, &$updated, &$skipped
            ) {
                $dataStart = $headerRowIdx + 1;

                #membaca setiap baris data dari file Excel mulai dari baris setelah header. 
                #jika nama produk kosong, maka baris dilewati dan jumlah skipped bertambah. jika nama produk ada, maka dicek apakah produk sudah ada di database. jika belum ada, maka dibuat baru; jika sudah ada, maka diperbarui nilainya.
                for ($i = $dataStart; $i < count($rows); $i++) {
                    $nama = trim($rows[$i][$colNama] ?? '');
                    if (!$nama) { $skipped++; continue; }

                    #menentukan kategori produk berdasarkan nama produk dengan memanggil method resolveKategori().
                    $idKategori = $this->resolveKategori($nama);
                    #mengecek apakah produk dengan nama yang sama sudah ada di database. jika belum ada, maka dibuat baru; jika sudah ada, maka diperbarui nilainya.
                    $isNew      = !Produk::where('nama_produk', $nama)->exists();

                    $produk = Produk::firstOrCreate(
                        ['nama_produk' => $nama],
                        ['status_data' => 'Belum Lengkap', 'id_kategori' => $idKategori]
                    );

                    // Update kategori jika produk sudah ada tapi belum punya kategori
                    if (!$isNew && !$produk->id_kategori && $idKategori) {
                        $produk->update(['id_kategori' => $idKategori]);
                    }
                    #mengecek apakah produk baru atau sudah ada. jika produk baru, jumlah imported bertambah; jika produk sudah ada, jumlah updated bertambah.
                    if ($isNew) $imported++; else $updated++;

                    #foreach itu untuk menyimpan nilai kriteria dari file Excel ke database.
                    foreach ($kolomMap as $idKriteria => $colIdx) {
                        $nilai = $this->parseAngka($rows[$i][$colIdx] ?? '');
                        #updateOrCreate() untuk menyimpan nilai kriteria ke tabel nilai_produk. jika sudah ada baris dengan id_produk dan id_kriteria yang sama, maka nilainya diperbarui; jika belum ada, maka dibuat baru.
                        NilaiProduk::updateOrCreate(
                            ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                            ['nilai' => $nilai]
                        );
                    }
                    #memanggil method updateStatusProduk() untuk memperbarui status data produk (Lengkap/Belum Lengkap) berdasarkan apakah semua kriteria sudah memiliki nilai atau belum.
                    $this->updateStatusProduk($produk);
                }
            });
        #jika ada error saat proses import, maka file sementara akan dihapus, session akan dibersihkan, dan diarahkan kembali ke halaman data produk dengan pesan error.
        } catch (\Throwable $e) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Import gagal dan dibatalkan seluruhnya: ' . $e->getMessage());
        }
        #hapus file sementara setelah proses import selesai, dan bersihkan session yang menyimpan data preview.
        @unlink($path);
        $this->clearImportSession();

        $msg = "{$imported} produk baru ditambahkan";
        if ($updated > 0) $msg .= ", {$updated} produk diperbarui nilainya";
        if ($skipped > 0) $msg .= ", {$skipped} baris kosong dilewati";

        #mengembalikan ke halaman data produk dengan menampilkan pesan sukses yang berisi jumlah produk yang berhasil diimpor, diperbarui, dan dilewati.
        return redirect()->route('produk.index')->with('success', $msg . ' dari Excel.');
    }

    #method cancelPreview() untuk membatalkan proses import data produk dari file Excel.
    #method ini akan menghapus file sementara yang diunggah, membersihkan session yang menyimpan data preview, dan mengarahkan kembali ke halaman data produk dengan pesan informasi bahwa import dibat
    public function cancelPreview()
    {
        #mengambil nama file yang sedang diproses dari session.
        $filename = session('import_filename');
        #jika ada file sementara, maka dihapus dari folder storage/app/temp. lalu session dibersihkan dan diarahkan kembali ke halaman data produk dengan pesan informasi bahwa import dibatalkan.
        if ($filename) @unlink(storage_path('app/temp/' . $filename));
        $this->clearImportSession();
        return redirect()->route('produk.index')->with('info', 'Import dibatalkan.');
    }

    #method update() untuk memperbarui data produk yang sudah ada di database.
   public function update(Request $request, $id)
    {
        #nencari produk berdasarkan id yang diterima dari parameter. jika produk tidak ditemukan, maka akan menampilkan error 404.
        $produk = Produk::findOrFail($id);

        #validasi nama produk dan id_kategori yang diterima dari request. nama produk wajib diisi, sedangkan id_kategori boleh kosong (nullable) dan harus berupa integer yang ada di tabel kategori_produk.
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|integer|exists:kategori_produk,id_kategori',
        ]);

        // Prioritas kategori saat update:
        // - Kalau dropdown muncul di payload -> hormati pilihan admin (termasuk kosong)
        // - Kalau dropdown tidak ada -> coba auto-resolve, fallback ke kategori lama
        if ($request->has('id_kategori')) {
            $idKategori = $request->filled('id_kategori') ? (int) $request->id_kategori : null;
        } else {
            $idKategori = $this->resolveKategori($request->nama_produk) ?? $produk->id_kategori;
        }

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $idKategori,
        ]);

        return back()->with('success', 'Produk berhasil diupdate.');
    }

    #method destroy() untuk menghapus produk berdasarkan id yang diterima dari parameter.
    public function destroy($id)
    {
        #mencari produk berdasarkan id yang diterima dari parameter untuk dihapus. jika produk tidak ditemukan, maka akan menampilkan error 404.
        $produk = Produk::findOrFail($id);

        #menggunakan DB::transaction() untuk memastikan seluruh proses penghapusan berjalan dengan aman. jika ada error di tengah proses, seluruh perubahan akan di-rollback sehingga database tetap konsisten.
        DB::transaction(function () use ($produk, $id) {
            // Hapus data operasional terkait (tidak dipertahankan)
            \App\Models\NilaiProduk::where('id_produk', $id)->delete();
            \App\Models\InputPermintaan::where('id_produk', $id)->delete();

            // PENTING: HasilPerhitungan TIDAK dihapus.
            // Riwayat ranking tetap utuh — tabel hasil_perhitungan sudah
            // menyimpan snapshot nama_produk dan nilai-nilai ranking.

            // Soft delete produk artinya hanya menandai deleted_at, tidak benar-benar menghapus baris dari DB.
            $produk->delete();
        });

        return back()->with('success', 'Produk berhasil dihapus. Riwayat perhitungan tetap tersimpan.');
    }
    #method resolveKategori() untuk menentukan id_kategori dari nama produk berdasarkan aturan keyword tertentu.
    private function resolveKategori(string $namaProduk): ?int
    {
        #nama produk diubah menjadi huruf kapital dan spasi di awal/akhir dihapus. 
        $upper = strtoupper(trim($namaProduk));
        #menyamakan tanda kutip tunggal yang berbeda menjadi tanda kutip tunggal standar agar pencarian keyword lebih konsisten.
        $upper = str_replace(['’', '‘', '`', '´'], "'", $upper);

        // Paket produk tidak diberi kategori.
        // Nanti otomatis masuk grup "Tanpa Kategori".
        if (str_starts_with($upper, 'PAKET')) {
            return null;
        }

        #aturan keyword untuk menentukan kategori produk berdasarkan nama produk.
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

        #menyimpan aturan keyword ke dalam database jika belum ada.
        foreach ($rules as $keyword => $namaKategori) {
            #jika nama produk mengandung keyword tertentu, maka kategori produk akan ditentukan berdasarkan nama kategori yang sesuai dengan keyword tersebut.
            if (str_contains($upper, strtoupper($keyword))) {
                #jika kategori produk dengan nama kategori tersebut belum ada di database, maka akan dibuat baru menggunakan firstOrCreate(). jika sudah ada, maka akan mengambil id_kategori yang sudah ada.
                $kat = KategoriProduk::firstOrCreate([
                    'nama_kategori' => $namaKategori
                ]);

                return $kat->id_kategori;
            }
        }
        #jika tidak ada keyword yang cocok, maka method ini akan mengembalikan null, artinya produk tidak memiliki kategori.
        return null;
    }

    #method detectHeaderRow() untuk mendeteksi baris header di file Excel. method ini akan mencari baris yang mengandung kolom "NAMA BARANG" dan mencocokkan kolom-kolom lainnya dengan kriteria yang ada di database.
    private function detectHeaderRow(array $rows, $kriteriaExcel): array
    {
        #membuat daftar kolom yang dicari dari kriteria yang ada di database. 
        #kolom-kolom ini akan dicocokkan dengan baris-baris di file Excel untuk menemukan baris header yang sesuai.
        #kolom "NAMA BARANG" selalu ditambahkan ke daftar kolom yang dicari.
        $kolomDicari = collect($kriteriaExcel->pluck('nama_kolom_excel'))
            ->map(fn($k) => strtoupper(trim($k)))
            ->push('NAMA BARANG')
            ->unique()->values()->toArray();

        #memeriksa maksimal 15 baris pertama di file Excel untuk mencari baris header. 
        foreach ($rows as $idx => $row) {
            if ($idx >= 15) break;

            #setiap baris diubah menjadi huruf kapital dan spasi di awal/akhir dihapus agar pencocokan kolom lebih konsisten. jika ada nilai null, maka diganti dengan string kosong.
            $normalizedRow = array_map(fn($v) => strtoupper(trim($v ?? '')), $row);

            #jika baris tidak mengandung kolom "NAMA BARANG", maka baris tersebut dilewati dan pencarian dilanjutkan ke baris berikutnya.
            if (!in_array('NAMA BARANG', $normalizedRow)) continue;


            #membaca baris berikutnya untuk menggabungkan header jika ada sub-header di baris berikutnya.
            $nextRow        = $rows[$idx + 1] ?? []; #kode ini mengambil baris berikutnya dari file Excel. jika tidak ada baris berikutnya, maka akan menghasilkan array kosong.
            $nextNormalized = array_map(fn($v) => strtoupper(trim($v ?? '')), $nextRow); #kode ini mengubah baris berikutnya menjadi huruf kapital dan spasi di awal/akhir dihapus agar pencocokan kolom lebih konsisten. jika ada nilai null, maka diganti dengan string kosong.
            $mergedHeaders  = []; #kode ini akan menyimpan hasil penggabungan header dari baris saat ini dan baris berikutnya. jika ada sub-header di baris berikutnya, maka akan digabungkan dengan header di baris saat ini.

            #mengecek setiap kolom di baris saat ini dan baris berikutnya. jika ada sub-header di baris berikutnya, maka akan digabungkan dengan header di baris saat ini. jika tidak ada sub-header, maka header di baris saat ini tetap digunakan.
            foreach ($normalizedRow as $i => $val) {
                $sub = $nextNormalized[$i] ?? '';
                $mergedHeaders[$i] = ($sub && $val !== 'NAMA BARANG') ? $sub : $val;
            }

            #menghitung jumlah kolom yang ditemukan di baris saat ini dan jumlah kolom yang ditemukan setelah digabungkan dengan baris berikutnya. 
            #jika jumlah kolom yang ditemukan setelah digabungkan lebih banyak, maka baris berikutnya dianggap sebagai sub-header dan akan digunakan sebagai header gabungan.
            $baseFound   = count(array_intersect($kolomDicari, $normalizedRow));
            $mergedFound = count(array_intersect($kolomDicari, $mergedHeaders));

            #jika jumlah kolom yang ditemukan setelah digabungkan lebih banyak, maka baris berikutnya dianggap sebagai sub-header dan akan digunakan sebagai header gabungan. jika tidak, maka baris saat ini tetap digunakan sebagai header.
            if ($mergedFound > $baseFound) {
                return [$idx + 1, $mergedHeaders];
            }

            return [$idx, $normalizedRow];
        }

        return [null, []];
    }
    
    #method parseAngka() untuk mengubah string angka dari file Excel menjadi tipe float. 
    private function parseAngka($raw): float
    {   
        #jika nilai yang diterima null atau string kosong, maka dianggap 0.0.
        if ($raw === null || $raw === '') return 0.0;

        #menghapus karakter "Rp" dan spasi dari string angka agar bisa diubah menjadi float.
        $str = preg_replace('/[Rp\s]/u', '', (string) $raw);

        #menangani format angka yang menggunakan titik dan koma sebagai pemisah ribuan dan desimal.
        if (substr_count($str, '.') > 1) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        #menangani format angka yang menggunakan koma sebagai pemisah ribuan dan desimal.
        } elseif (substr_count($str, ',') > 1) {
            $str = str_replace(',', '', $str);
        #menangani format angka yang dengan kombinasi titik dan koma, misalnya "1.234,56" atau "1,234.56".
        } elseif (strpos($str, ',') !== false && strpos($str, '.') !== false) {
            #jika posisi koma lebih besar dari posisi titik terakhir, maka diasumsikan formatnya adalah "1.234,56" (titik sebagai ribuan, koma sebagai desimal).
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
                #jika posisi titik lebih besar dari posisi koma terakhir, maka diasumsikan formatnya adalah "1,234.56" (koma sebagai ribuan, titik sebagai desimal).
            } else {
                $str = str_replace(',', '', $str);
            }
            #jika hanya ada satu koma atau satu titik, maka diasumsikan formatnya adalah "1,234" atau "1.234" (koma atau titik sebagai desimal).
        } elseif (strpos($str, ',') !== false) {
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }

    #mengecek apakah data produk sudah lengkap atau belum berdasarkan jumlah kriteria yang ada di database dan jumlah nilai produk yang sudah diisi.   
    private function updateStatusProduk(Produk $produk): void
    {
        #menghitung total kriteria yang ada di database dan total nilai produk yang sudah diisi untuk produk tertentu. 
        #jika jumlah nilai produk sama dengan jumlah kriteria, maka status data produk akan diubah menjadi "Lengkap"; jika tidak, maka status data produk akan diubah menjadi "Belum Lengkap".
        $totalKriteria = Kriteria::count();
        $totalNilai    = NilaiProduk::where('id_produk', $produk->id_produk)->count();

        $produk->update([
            'status_data' => ($totalKriteria > 0 && $totalNilai >= $totalKriteria)
                ? 'Lengkap'
                : 'Belum Lengkap',
        ]);
    }

    #method clearImportSession() untuk membersihkan session yang menyimpan data preview import produk dari file Excel. 
    private function clearImportSession(): void
    {
        #menghapus semua data yang terkait dengan proses import dari session, termasuk nama file, total data, kolom yang ditemukan, kolom yang tidak ada, dan baris preview.
        session()->forget([
            'import_filename', 'import_total_data',
            'import_kolom_ditemukan', 'import_kolom_tidak_ada',
            'import_preview_rows',
        ]);
    }
}