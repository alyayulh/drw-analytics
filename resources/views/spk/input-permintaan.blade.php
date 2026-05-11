<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Input Permintaan — DRW Skincare SPK</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --pink:#e91e63;
    --pink-soft:#fff1f6;
    --pink-border:#f5c5d7;
    --text:#222;
    --muted:#888;
    --bg:#fff8fb;
    --white:#fff;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg);
    color:var(--text);
}

.main{
    padding:32px;
    max-width:1400px;
    margin:auto;
}

.step-wrapper{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:10px;
    margin-bottom:24px;
}

.step{
    width:28px;
    height:28px;
    border-radius:50%;
    background:#f5d9e4;
    color:#999;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    font-weight:700;
}

.step.active{
    background:var(--pink);
    color:white;
}

.step-line{
    width:40px;
    height:2px;
    background:#f5d9e4;
}

.page-header{
    margin-bottom:24px;
}

.page-header h1{
    font-size:26px;
    font-weight:800;
    margin-bottom:6px;
}

.page-header p{
    color:var(--muted);
    font-size:13px;
}

.top-filter{
    display:flex;
    gap:14px;
    margin-bottom:18px;
}

.search-input,
.category-filter{
    height:44px;
    border:1px solid var(--pink-border);
    border-radius:14px;
    padding:0 16px;
    background:white;
    outline:none;
    font-family:inherit;
}

.search-input{
    flex:1;
}

.selected-summary{
    background:white;
    border:1px solid var(--pink-border);
    border-radius:18px;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.summary-label{
    font-size:12px;
    color:var(--muted);
}

.selected-summary h3{
    color:var(--pink);
    margin-top:4px;
}

.btn-next,
.btn-save{
    border:none;
    background:#f7e7ef;
    padding:12px 20px;
    border-radius:14px;
    cursor:pointer;
    font-weight:600;
    transition:0.2s;
}

.btn-next:hover,
.btn-save:hover{
    background:#f2d7e3;
}

.category-card{
    background:white;
    border:1px solid var(--pink-border);
    border-radius:18px;
    margin-bottom:14px;
    overflow:hidden;
}

.category-header{
    padding:18px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
}

.category-title{
    font-weight:700;
    display:flex;
    align-items:center;
    gap:10px;
}

.category-count{
    background:#fde8f1;
    color:var(--pink);
    font-size:11px;
    padding:4px 10px;
    border-radius:999px;
}

.category-body{
    padding:0 20px 20px;
}

.product-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.product-item{
    border:1px solid var(--pink-border);
    border-radius:999px;
    padding:12px 14px;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:10px;
    transition:0.2s;
    font-size:13px;
}

.product-item:hover{
    background:var(--pink-soft);
}

.product-checkbox{
    accent-color:var(--pink);
}

.progress-box{
    background:white;
    border:1px solid var(--pink-border);
    border-radius:18px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:18px;
}

.btn-back{
    border:1px solid var(--pink);
    background:white;
    color:var(--pink);
    padding:10px 16px;
    border-radius:999px;
    cursor:pointer;
    font-weight:600;
}

.table-wrapper{
    background:white;
    border:1px solid var(--pink-border);
    border-radius:18px;
    overflow:hidden;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:16px 20px;
    font-size:12px;
    border-bottom:1px solid #f5d9e4;
}

td{
    padding:18px 20px;
    border-bottom:1px solid #f8dfe8;
    font-size:13px;
}

.rating-group{
    display:flex;
    gap:8px;
}

.rating-btn{
    width:34px;
    height:34px;
    border-radius:50%;
    border:1px solid var(--pink);
    background:white;
    color:var(--pink);
    cursor:pointer;
    transition:0.2s;
}

.rating-btn.active{
    background:var(--pink);
    color:white;
}

.status-text{
    font-size:12px;
    color:#c18ca0;
    font-weight:600;
}

.save-wrapper{
    display:flex;
    justify-content:flex-end;
    margin-top:20px;
}

@media(max-width:900px){

    .product-grid{
        grid-template-columns:1fr;
    }

    .top-filter{
        flex-direction:column;
    }

}

</style>
</head>

<body>

<div class="main">

    {{-- STEPPER --}}
    <div class="step-wrapper">
        <div class="step active" id="step1Indicator">1</div>
        <div class="step-line"></div>
        <div class="step" id="step2Indicator">2</div>
    </div>

    {{-- STEP 1 --}}
    <div id="step1">

        <div class="page-header">
            <h1>Input Permintaan Produk</h1>
            <p>
                Pilih maksimal 5 produk di setiap kategori sebelum melakukan penilaian kriteria.
            </p>
        </div>

        <div class="top-filter">

            <input
                type="text"
                class="search-input"
                id="searchInput"
                placeholder="Cari produk..."
            >

            <select
                class="category-filter"
                id="kategoriFilter"
            >

                <option value="all">
                    Semua Kategori
                </option>

                @foreach($produkByKategori as $kategori => $items)

                    <option value="{{ strtolower($kategori) }}">
                        {{ $kategori }}
                    </option>

                @endforeach

            </select>

        </div>

        <div class="selected-summary">

            <div>
                <div class="summary-label">
                    Total Produk Dipilih
                </div>

                <h3 id="totalSelected">
                    0 produk
                </h3>
            </div>

            <button
                class="btn-next"
                onclick="goToStep2()"
            >
                Lanjut ke Penilaian →
            </button>

        </div>

        {{-- LIST KATEGORI --}}
        @foreach($produkByKategori as $kategori => $items)

        <div
            class="category-card"
            data-category="{{ strtolower($kategori) }}"
        >

            <div
                class="category-header"
                onclick="toggleCategory(this)"
            >

                <div class="category-title">

                    {{ $kategori }}

                    <span class="category-count">

                        <span id="count-{{ Str::slug($kategori) }}">
                            0
                        </span>

                        /5 dipilih

                    </span>

                </div>

                <div>⌄</div>

            </div>

            <div class="category-body">

                <div class="product-grid">

                    @foreach($items as $produk)

                    <label class="product-item">

                        <input
                            type="checkbox"
                            class="product-checkbox"
                            data-id="{{ $produk->id_produk }}"
                            data-name="{{ $produk->nama_produk }}"
                            data-category="{{ $kategori }}"
                            onchange="toggleProduct(this)"
                        >

                        <span>
                            {{ $produk->nama_produk }}
                        </span>

                    </label>

                    @endforeach

                </div>

            </div>

        </div>

        @endforeach

    </div>

    {{-- STEP 2 --}}
    <div id="step2" style="display:none;">

        <div class="page-header">
            <h1>Input Permintaan Produk</h1>
        </div>

        <div class="progress-box">

            <button
                class="btn-back"
                onclick="backToStep1()"
            >
                ← Kembali
            </button>

            <div>

                <div class="summary-label">
                    Progress Penilaian
                </div>

                <h3 id="progressText">
                    0 / 0 produk
                </h3>

            </div>

        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Skor Permintaan</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody id="penilaianTable">

                </tbody>

            </table>

        </div>

        <div class="save-wrapper">

            <button
                class="btn-save"
                onclick="savePenilaian()"
            >
                Simpan Penilaian
            </button>

        </div>

    </div>

</div>

<script>

let selectedProducts = [];

function toggleCategory(el){

    const body = el.nextElementSibling;

    body.style.display =
        body.style.display === 'none'
        ? 'block'
        : 'none';
}

function toggleProduct(el){

    const category = el.dataset.category;

    const categorySlug =
        category.toLowerCase().replace(/\s+/g,'-');

    const checked =
        document.querySelectorAll(
            `input[data-category="${category}"]:checked`
        );

    if(checked.length > 5){

        el.checked = false;

        alert('Maksimal 5 produk per kategori');

        return;
    }

    document.getElementById(
        `count-${categorySlug}`
    ).innerText = checked.length;

    updateSelectedProducts();
}

function updateSelectedProducts(){

    selectedProducts = [];

    document.querySelectorAll(
        '.product-checkbox:checked'
    ).forEach(item => {

        selectedProducts.push({
            id:item.dataset.id,
            name:item.dataset.name,
        });

    });

    document.getElementById(
        'totalSelected'
    ).innerText =
        selectedProducts.length + ' produk';
}

function goToStep2(){

    if(selectedProducts.length === 0){

        alert('Pilih produk terlebih dahulu');

        return;
    }

    document.getElementById('step1')
        .style.display='none';

    document.getElementById('step2')
        .style.display='block';

    document.getElementById('step1Indicator')
        .classList.remove('active');

    document.getElementById('step2Indicator')
        .classList.add('active');

    renderTable();
}

function backToStep1(){

    document.getElementById('step1')
        .style.display='block';

    document.getElementById('step2')
        .style.display='none';

    document.getElementById('step2Indicator')
        .classList.remove('active');

    document.getElementById('step1Indicator')
        .classList.add('active');
}

function renderTable(){

    const tbody =
        document.getElementById('penilaianTable');

    tbody.innerHTML = '';

    selectedProducts.forEach((item,index)=>{

        tbody.innerHTML += `

            <tr>

                <td>${index + 1}</td>

                <td>${item.name}</td>

                <td>

                    <div class="rating-group">

                        ${[1,2,3,4,5].map(v=>`

                            <button
                                type="button"
                                class="rating-btn"
                                onclick="selectRating(this)"
                            >
                                ${v}
                            </button>

                        `).join('')}

                    </div>

                </td>

                <td>

                    <span class="status-text">
                        Belum Selesai
                    </span>

                </td>

            </tr>

        `;
    });

    document.getElementById(
        'progressText'
    ).innerText =
        `0 / ${selectedProducts.length} produk`;
}

function selectRating(el){

    const parent =
        el.parentElement;

    parent.querySelectorAll('.rating-btn')
        .forEach(btn=>{
            btn.classList.remove('active');
        });

    el.classList.add('active');

    updateProgress();
}

function updateProgress(){

    const rows =
        document.querySelectorAll('#penilaianTable tr');

    let done = 0;

    rows.forEach(row=>{

        const active =
            row.querySelector('.rating-btn.active');

        const status =
            row.querySelector('.status-text');

        if(active){

            done++;

            status.innerText = 'Selesai';

            status.style.color = '#28a745';

        }else{

            status.innerText = 'Belum Selesai';

            status.style.color = '#c18ca0';
        }

    });

    document.getElementById(
        'progressText'
    ).innerText =
        `${done} / ${rows.length} produk`;
}

function savePenilaian(){

    alert('Penilaian berhasil disimpan');
}

document.getElementById('searchInput')
.addEventListener('input',function(){

    const keyword =
        this.value.toLowerCase();

    document.querySelectorAll('.product-item')
    .forEach(item=>{

        item.style.display =
            item.innerText
                .toLowerCase()
                .includes(keyword)
            ? 'flex'
            : 'none';

    });

});

document.getElementById('kategoriFilter')
.addEventListener('change',function(){

    const value =
        this.value;

    document.querySelectorAll('.category-card')
    .forEach(card=>{

        card.style.display =
            value === 'all'
            || card.dataset.category === value
            ? 'block'
            : 'none';

    });

});

</script>

</body>
</html>