<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir Ritel - GAS Blogger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root {
            --primary: #2563eb;
            --accent: #f59e0b;
            --ritel-bg: #f8fafc;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--ritel-bg);
            margin: 0;
            overflow: hidden;
        }

        #app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Ritel Sidebar */
        .sidebar-ritel {
            width: 70px;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            transition: width 0.3s;
        }
        
        .sidebar-ritel:hover { width: 200px; }
        .sidebar-ritel:hover span { display: block; }
        .sidebar-ritel span { display: none; margin-left: 15px; font-weight: 600; }

        .nav-link {
            width: 100%;
            padding: 15px 20px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-link:hover, .nav-active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left: 4px solid var(--accent);
        }

        /* POS Layout */
        .pos-grid-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            flex-grow: 1;
            overflow: hidden;
        }

        .product-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            transition: transform 0.1s, box-shadow 0.2s;
            cursor: pointer;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .stok-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }

        /* Keranjang Ritel */
        .cart-panel {
            background: white;
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 6px -1px rgba(0,0,0,0.05);
        }

        .cart-table th {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            padding: 10px;
            border-bottom: 2px solid #f1f5f9;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .btn-pay {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Print Style */
        @media print {
            body * { visibility: hidden; }
            #print-area, #print-area * { visibility: visible; }
            #print-area { position: absolute; left: 0; top: 0; width: 80mm; font-size: 12px; }
        }
    </style>
</head>
<body>

    <div id="loading" class="loading-overlay hidden">
        <div class="bg-white p-6 rounded-2xl shadow-xl text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-600 mx-auto"></div>
            <p class="mt-4 font-bold text-gray-700">Memproses Data...</p>
        </div>
    </div>

    <!-- Area Cetak Tersembunyi -->
    <div id="print-area" class="hidden p-4 bg-white text-black font-mono text-[10px]">
        <div class="text-center mb-4">
            <h2 class="font-bold text-lg">RITEL PRO</h2>
            <p>Jl. Raya Blog No. 123, Jakarta</p>
            <p>Telp: 0812-3456-7890</p>
        </div>
        <div class="border-b border-dashed mb-2"></div>
        <p>No: #<span id="p-id"></span></p>
        <p>Tgl: <span id="p-date"></span></p>
        <div class="border-b border-dashed my-2"></div>
        <div id="p-items"></div>
        <div class="border-b border-dashed my-2"></div>
        <div class="flex justify-between"><span>TOTAL</span><span id="p-total"></span></div>
        <div class="flex justify-between"><span>BAYAR</span><span id="p-bayar"></span></div>
        <div class="flex justify-between font-bold"><span>KEMBALI</span><span id="p-kembali"></span></div>
        <div class="border-b border-dashed my-2"></div>
        <div class="text-center mt-4">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar</p>
        </div>
    </div>

    <div id="app-container">
        <!-- Sidebar Menu -->
        <aside class="sidebar-ritel">
            <div class="mb-10 text-accent">
                <i class="fas fa-bolt text-3xl"></i>
            </div>
            <div onclick="switchMenu('pos')" class="nav-link nav-active">
                <i class="fas fa-cash-register text-xl"></i>
                <span>KASIR</span>
            </div>
            <div onclick="switchMenu('produk')" class="nav-link">
                <i class="fas fa-boxes text-xl"></i>
                <span>STOK</span>
            </div>
            <div onclick="switchMenu('laporan')" class="nav-link">
                <i class="fas fa-chart-line text-xl"></i>
                <span>LAPORAN</span>
            </div>
            <div class="mt-auto nav-link text-red-400" onclick="location.reload()">
                <i class="fas fa-sign-out-alt text-xl"></i>
                <span>KELUAR</span>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-grow flex flex-col overflow-hidden">
            
            <!-- Header Ritel -->
            <header class="h-16 bg-white border-b px-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">RITEL<span class="text-blue-600">PRO</span></h1>
                    <div class="h-6 w-[1px] bg-slate-200"></div>
                    <span id="display-clock" class="mono text-slate-500 font-bold">00:00:00</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p id="user-name" class="text-sm font-bold text-slate-700 leading-none">Admin Kasir</p>
                        <span class="text-[10px] text-blue-500 font-bold uppercase tracking-widest">Shift Pagi</span>
                    </div>
                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center border">
                        <i class="fas fa-user text-slate-400"></i>
                    </div>
                </div>
            </header>

            <!-- POS Area -->
            <div id="section-pos" class="section-content flex-grow pos-grid-container">
                
                <!-- Kiri: Produk -->
                <div class="p-6 overflow-hidden flex flex-col">
                    <div class="flex space-x-4 mb-6">
                        <div class="relative flex-grow">
                            <i class="fas fa-barcode absolute left-4 top-4 text-slate-400"></i>
                            <input type="text" id="pos-search" onkeyup="renderPOS()" placeholder="Scan Barcode atau Ketik Nama Produk (F1)..." class="w-full p-4 pl-12 bg-white border-2 border-transparent focus:border-blue-500 rounded-2xl shadow-sm outline-none transition">
                        </div>
                        <select id="pos-filter-kat" onchange="renderPOS()" class="bg-white px-4 rounded-2xl border shadow-sm font-bold text-slate-600 outline-none">
                            <option value="">Semua Rak</option>
                        </select>
                    </div>

                    <div id="pos-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 overflow-y-auto pr-2 pb-10">
                        <!-- Produk di-render di sini -->
                    </div>
                </div>

                <!-- Kanan: Keranjang -->
                <div class="cart-panel">
                    <div class="p-4 bg-slate-50 border-b flex justify-between items-center">
                        <h3 class="font-black text-slate-800 tracking-tighter">DAFTAR BELANJA</h3>
                        <span id="cart-count" class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full font-bold">0 Item</span>
                    </div>
                    
                    <div class="flex-grow overflow-y-auto">
                        <table class="w-full cart-table text-left border-collapse">
                            <thead class="sticky top-0 bg-white z-10">
                                <tr>
                                    <th class="pl-4 w-1/2">Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="pr-4 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items" class="divide-y divide-slate-100"></tbody>
                        </table>
                        <div id="cart-empty" class="py-20 text-center flex flex-col items-center justify-center text-slate-300">
                            <i class="fas fa-shopping-basket text-6xl mb-4"></i>
                            <p class="font-bold">Belum Ada Belanjaan</p>
                        </div>
                    </div>

                    <!-- Panel Total & Bayar -->
                    <div class="p-6 bg-slate-900 text-white">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-slate-400 font-bold text-sm">Subtotal</span>
                            <span id="cart-subtotal" class="mono font-bold">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-slate-400 font-bold text-sm">Pajak (0%)</span>
                            <span class="mono font-bold">Rp 0</span>
                        </div>
                        <div class="h-[1px] bg-slate-800 mb-4"></div>
                        
                        <div class="flex justify-between items-end mb-6">
                            <span class="text-accent font-black text-xs uppercase tracking-widest">Grand Total</span>
                            <span id="cart-total" class="text-4xl font-black mono text-white leading-none">Rp 0</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase">Metode Pembayaran</label>
                                <div class="grid grid-cols-3 gap-2 mt-1">
                                    <button onclick="setMetode('Tunai')" class="metode-btn py-2 bg-blue-600 border border-blue-500 text-white rounded-lg text-xs font-bold active-metode">TUNAI</button>
                                    <button onclick="setMetode('QRIS')" class="metode-btn py-2 bg-slate-800 rounded-lg text-xs font-bold border border-slate-700">QRIS</button>
                                    <button onclick="setMetode('Debit')" class="metode-btn py-2 bg-slate-800 rounded-lg text-xs font-bold border border-slate-700">DEBIT</button>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase">Input Bayar (Rp)</label>
                                <input type="number" id="pos-input-bayar" onkeyup="updateKembali()" class="w-full bg-slate-800 border border-slate-700 p-3 rounded-xl text-2xl font-black text-accent outline-none mt-1 focus:border-accent">
                            </div>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-slate-800/50 rounded-xl mb-6">
                            <span class="text-xs font-bold text-slate-500">KEMBALIAN</span>
                            <span id="pos-val-kembali" class="text-xl font-black text-green-400 mono">Rp 0</span>
                        </div>

                        <button onclick="processTransaction()" class="btn-pay w-full py-5 rounded-2xl text-xl font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition">
                            PROSES TRANSAKSI (F10)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Manajemen Stok -->
            <div id="section-produk" class="section-content hidden p-8 flex-grow overflow-y-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800">Inventaris Barang</h2>
                        <p class="text-slate-500">Pantau stok dan kelola harga produk.</p>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="p-5 font-bold text-slate-400 text-xs">SKU</th>
                                <th class="p-5 font-bold text-slate-800">NAMA PRODUK</th>
                                <th class="p-5 font-bold text-slate-800">KATEGORI</th>
                                <th class="p-5 font-bold text-slate-800 text-right">HARGA JUAL</th>
                                <th class="p-5 font-bold text-slate-800 text-center">STOK</th>
                            </tr>
                        </thead>
                        <tbody id="table-produk" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>

            <!-- Laporan Penjualan -->
            <div id="section-laporan" class="section-content hidden p-8 flex-grow overflow-y-auto">
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-800">Laporan Penjualan</h2>
                    <p class="text-slate-500">Ringkasan transaksi harian Anda.</p>
                </div>

                <!-- Kartu Statistik -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Omzet</p>
                        <h3 id="stat-omzet" class="text-3xl font-black text-blue-600 mono">Rp 0</h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Transaksi</p>
                        <h3 id="stat-trx" class="text-3xl font-black text-slate-800 mono">0</h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Item Terjual</p>
                        <h3 id="stat-items" class="text-3xl font-black text-green-500 mono">0</h3>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="p-5 font-bold text-slate-800">ID TRX</th>
                                <th class="p-5 font-bold text-slate-800">WAKTU</th>
                                <th class="p-5 font-bold text-slate-800">METODE</th>
                                <th class="p-5 font-bold text-slate-800 text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="table-laporan" class="divide-y divide-slate-100">
                            <!-- Data Laporan -->
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal Receipt (UI) -->
    <div id="modal-struk" class="fixed inset-0 bg-black/80 z-[10002] flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-3xl w-80 text-center shadow-2xl">
            <div class="mb-4">
                <i class="fas fa-check-circle text-green-500 text-5xl"></i>
            </div>
            <h2 class="font-black text-xl mb-1">Pembayaran Sukses!</h2>
            <p class="text-slate-400 text-sm mb-6">Trx: #<span id="struk-id"></span></p>
            
            <div class="border-t border-b border-dashed py-4 mb-6 text-left mono text-xs">
                <div class="flex justify-between"><span>TOTAL</span><span id="struk-total"></span></div>
                <div class="flex justify-between text-slate-400"><span>BAYAR</span><span id="struk-bayar"></span></div>
                <div class="flex justify-between text-slate-400"><span>KEMBALI</span><span id="struk-kembali"></span></div>
            </div>
            
            <div class="space-y-3">
                <button onclick="closeStruk()" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold">Transaksi Baru</button>
                <button onclick="printReceipt()" class="w-full border py-3 rounded-xl font-bold text-slate-600"><i class="fas fa-print mr-2"></i> Cetak Struk</button>
            </div>
        </div>
    </div>

    <script>
        // DATA & STATE
        let db = { produk: [], laporan: [] };
        let cart = [];
        let activeMetode = 'Tunai';
        let currentTrx = null;

        const DUMMY_PRODUK = [
            { id_produk: 'P1', kode_barcode: '8991001', nama_produk: 'Susu UHT Full Cream 1L', kategori: 'Dairy', harga_beli: 15000, harga_jual: 19500, stok: 24 },
            { id_produk: 'P2', kode_barcode: '8991002', nama_produk: 'Minyak Goreng 2L', kategori: 'Kebutuhan Pokok', harga_beli: 28000, harga_jual: 34000, stok: 15 },
            { id_produk: 'P3', kode_barcode: '8991003', nama_produk: 'Mie Instan Goreng', kategori: 'Makanan Instan', harga_beli: 2500, harga_jual: 3100, stok: 120 },
            { id_produk: 'P4', kode_barcode: '8991004', nama_produk: 'Sabun Mandi Cair 450ml', kategori: 'Toiletries', harga_beli: 18000, harga_jual: 22500, stok: 8 },
            { id_produk: 'P5', kode_barcode: '8991005', nama_produk: 'Roti Tawar Kupas', kategori: 'Bakery', harga_beli: 12000, harga_jual: 15000, stok: 5 },
            { id_produk: 'P6', kode_barcode: '8991006', nama_produk: 'Air Mineral 600ml', kategori: 'Minuman', harga_beli: 2000, harga_jual: 3500, stok: 200 }
        ];

        window.onload = () => {
            db.produk = JSON.parse(JSON.stringify(DUMMY_PRODUK));
            startTime();
            renderAll();
        };

        function startTime() {
            const display = document.getElementById('display-clock');
            setInterval(() => {
                const now = new Date();
                display.innerText = now.toLocaleTimeString('id-ID');
            }, 1000);
        }

        // NAVIGATION
        function switchMenu(menu) {
            document.querySelectorAll('.section-content').forEach(s => s.classList.add('hidden'));
            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('nav-active'));
            
            const target = document.getElementById(`section-${menu}`);
            if(target) target.classList.remove('hidden');
            
            const btn = document.querySelector(`.nav-link[onclick="switchMenu('${menu}')"]`);
            if(btn) btn.classList.add('nav-active');
            
            if(menu === 'laporan') renderLaporan();
            renderAll();
        }

        // RENDER LOGIC
        function renderAll() {
            renderPOS();
            renderTableProduk();
            renderDropdowns();
        }

        function renderPOS() {
            const grid = document.getElementById('pos-grid');
            const search = document.getElementById('pos-search').value.toLowerCase();
            const filter = document.getElementById('pos-filter-kat').value;
            grid.innerHTML = '';
            
            db.produk.filter(p => {
                return (p.nama_produk.toLowerCase().includes(search) || p.kode_barcode.includes(search)) && 
                       (filter === "" || p.kategori === filter);
            }).forEach(p => {
                const lowStok = p.stok < 10;
                grid.innerHTML += `
                    <div onclick="addToCart('${p.id_produk}')" class="product-card group">
                        <span class="stok-badge ${lowStok ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}">
                            ${p.stok} UNIT
                        </span>
                        <p class="text-[10px] font-bold text-slate-400 mb-1">${p.kode_barcode}</p>
                        <h4 class="font-bold text-slate-800 text-sm leading-tight h-8 overflow-hidden">${p.nama_produk}</h4>
                        <p class="text-blue-600 font-black mt-2 mono">Rp ${p.harga_jual.toLocaleString()}</p>
                    </div>`;
            });
        }

        function renderTableProduk() {
            const table = document.getElementById('table-produk');
            if(!table) return;
            table.innerHTML = '';
            db.produk.forEach(p => {
                table.innerHTML += `
                    <tr>
                        <td class="p-5 font-mono text-xs text-slate-400">${p.kode_barcode}</td>
                        <td class="p-5 font-bold text-slate-700">${p.nama_produk}</td>
                        <td class="p-5 text-sm text-slate-500">${p.kategori}</td>
                        <td class="p-5 font-black text-blue-600 text-right">Rp ${p.harga_jual.toLocaleString()}</td>
                        <td class="p-5 text-center"><span class="px-3 py-1 rounded-full font-bold ${p.stok < 10 ? 'bg-red-100 text-red-600' : 'bg-slate-100'}">${p.stok}</span></td>
                    </tr>`;
            });
        }

        function renderLaporan() {
            const table = document.getElementById('table-laporan');
            const statOmzet = document.getElementById('stat-omzet');
            const statTrx = document.getElementById('stat-trx');
            const statItems = document.getElementById('stat-items');
            
            table.innerHTML = '';
            let totalOmzet = 0;
            let totalItemSold = 0;

            db.laporan.forEach(trx => {
                totalOmzet += trx.total;
                totalItemSold += trx.items.reduce((a, b) => a + b.qty, 0);
                table.innerHTML += `
                    <tr>
                        <td class="p-5 font-bold text-blue-600">#${trx.id}</td>
                        <td class="p-5 text-sm text-slate-500">${trx.waktu}</td>
                        <td class="p-5 text-xs font-black uppercase"><span class="bg-slate-100 px-2 py-1 rounded">${trx.metode}</span></td>
                        <td class="p-5 font-black text-right">Rp ${trx.total.toLocaleString()}</td>
                    </tr>`;
            });

            statOmzet.innerText = `Rp ${totalOmzet.toLocaleString()}`;
            statTrx.innerText = db.laporan.length;
            statItems.innerText = totalItemSold;
        }

        function renderDropdowns() {
            const katSet = [...new Set(db.produk.map(p => p.kategori))];
            const sel = document.getElementById('pos-filter-kat');
            if(sel.options.length > 1) return;
            katSet.forEach(k => sel.innerHTML += `<option value="${k}">${k}</option>`);
        }

        // CART & TRX LOGIC
        function addToCart(id) {
            const p = db.produk.find(x => x.id_produk === id);
            if(p.stok <= 0) return alert("Barang ini sedang kosong!");
            const exist = cart.find(x => x.id_produk === id);
            if(exist) {
                if(exist.qty < p.stok) exist.qty++; else return alert("Batas stok!");
            } else {
                cart.push({ ...p, qty: 1 });
            }
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cart-items');
            const empty = document.getElementById('cart-empty');
            const count = document.getElementById('cart-count');
            list.innerHTML = '';
            let total = 0;
            let totalItem = 0;

            if(cart.length > 0) {
                empty.classList.add('hidden');
                cart.forEach((item, i) => {
                    total += item.harga_jual * item.qty;
                    totalItem += item.qty;
                    list.innerHTML += `
                        <tr class="group hover:bg-slate-50 transition text-xs">
                            <td class="p-4"><p class="font-bold text-slate-800 leading-none">${item.nama_produk}</p></td>
                            <td class="p-4"><div class="flex items-center space-x-2">
                                <button onclick="updateQty(${i}, -1)" class="w-5 h-5 bg-slate-200 rounded text-[10px]">-</button>
                                <span class="mono font-bold w-4 text-center">${item.qty}</span>
                                <button onclick="updateQty(${i}, 1)" class="w-5 h-5 bg-slate-200 rounded text-[10px]">+</button>
                            </div></td>
                            <td class="p-4 text-right pr-4"><p class="font-black mono">Rp ${(item.harga_jual * item.qty).toLocaleString()}</p></td>
                        </tr>`;
                });
            } else { empty.classList.remove('hidden'); }

            count.innerText = `${totalItem} Item`;
            document.getElementById('cart-total').innerText = `Rp ${total.toLocaleString()}`;
            document.getElementById('cart-subtotal').innerText = `Rp ${total.toLocaleString()}`;
            updateKembali();
        }

        function updateQty(idx, delta) {
            const item = cart[idx];
            const p = db.produk.find(x => x.id_produk === item.id_produk);
            item.qty += delta;
            if(item.qty > p.stok) item.qty = p.stok;
            if(item.qty <= 0) cart.splice(idx, 1);
            renderCart();
        }

        function setMetode(m) {
            activeMetode = m;
            document.querySelectorAll('.metode-btn').forEach(b => {
                b.classList.remove('bg-blue-600', 'border-blue-500', 'text-white');
                b.classList.add('bg-slate-800', 'border-slate-700');
                if(b.innerText.toUpperCase() === m.toUpperCase()) {
                    b.classList.remove('bg-slate-800', 'border-slate-700');
                    b.classList.add('bg-blue-600', 'border-blue-500', 'text-white');
                }
            });
        }

        function updateKembali() {
            const total = parseInt(document.getElementById('cart-total').innerText.replace(/\D/g,'')) || 0;
            const bayar = parseInt(document.getElementById('pos-input-bayar').value) || 0;
            const kembali = bayar - total;
            document.getElementById('pos-val-kembali').innerText = `Rp ${kembali < 0 ? 0 : kembali.toLocaleString()}`;
        }

        function processTransaction() {
            if(cart.length === 0) return;
            const total = parseInt(document.getElementById('cart-total').innerText.replace(/\D/g,'')) || 0;
            const bayar = parseInt(document.getElementById('pos-input-bayar').value) || 0;
            if(activeMetode === 'Tunai' && bayar < total) return alert("Uang kurang!");

            document.getElementById('loading').classList.remove('hidden');
            setTimeout(() => {
                const trxId = Date.now().toString().slice(-6);
                const waktu = new Date().toLocaleString('id-ID');
                
                currentTrx = { 
                    id: trxId, waktu, total, bayar, 
                    kembali: bayar - total, 
                    metode: activeMetode, 
                    items: [...cart] 
                };

                // Update Stok & Simpan Laporan
                cart.forEach(item => {
                    const p = db.produk.find(x => x.id_produk === item.id_produk);
                    if(p) p.stok -= item.qty;
                });
                db.laporan.unshift(currentTrx);

                document.getElementById('struk-id').innerText = trxId;
                document.getElementById('struk-total').innerText = `Rp ${total.toLocaleString()}`;
                document.getElementById('struk-bayar').innerText = `Rp ${bayar.toLocaleString()}`;
                document.getElementById('struk-kembali').innerText = `Rp ${(bayar-total).toLocaleString()}`;
                
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('modal-struk').classList.remove('hidden');
            }, 800);
        }

        function printReceipt() {
            if(!currentTrx) return;
            const area = document.getElementById('print-area');
            document.getElementById('p-id').innerText = currentTrx.id;
            document.getElementById('p-date').innerText = currentTrx.waktu;
            document.getElementById('p-total').innerText = `Rp ${currentTrx.total.toLocaleString()}`;
            document.getElementById('p-bayar').innerText = `Rp ${currentTrx.bayar.toLocaleString()}`;
            document.getElementById('p-kembali').innerText = `Rp ${currentTrx.kembali.toLocaleString()}`;
            
            const pItems = document.getElementById('p-items');
            pItems.innerHTML = '';
            currentTrx.items.forEach(item => {
                pItems.innerHTML += `
                    <div class="flex justify-between">
                        <span>${item.nama_produk} x${item.qty}</span>
                        <span>${(item.harga_jual * item.qty).toLocaleString()}</span>
                    </div>`;
            });

            area.classList.remove('hidden');
            window.print();
            area.classList.add('hidden');
        }

        function closeStruk() {
            cart = [];
            currentTrx = null;
            document.getElementById('pos-input-bayar').value = '';
            document.getElementById('modal-struk').classList.add('hidden');
            renderAll();
            renderCart();
        }

        window.onkeydown = (e) => {
            if(e.key === 'F1') { e.preventDefault(); document.getElementById('pos-search').focus(); }
            if(e.key === 'F10') { e.preventDefault(); processTransaction(); }
        }
    </script>
</body>
</html>
