<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(1);

$pageTitle = 'Kasir (POS)';
$depth = 1;

$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="row g-3">
    <!-- KIRI: Daftar Produk -->
    <div class="col-lg-7">
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-8">
                        <input type="text" id="searchProduk" class="form-control form-control-sm" placeholder="Cari nama / kode produk...">
                    </div>
                    <div class="col-4">
                        <select id="filterKategori" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <?php foreach ($kategoriList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2 produk-grid" id="produkGrid">
            <div class="text-center text-muted py-4 col-12">Memuat produk...</div>
        </div>
    </div>

    <!-- KANAN: Keranjang -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart3 me-1"></i>Keranjang Belanja</span>
                <button class="btn btn-sm btn-outline-danger" id="btnKosongkan"><i class="bi bi-x-circle"></i> Kosongkan</button>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <input type="text" id="namaPelanggan" class="form-control form-control-sm" placeholder="Nama pelanggan (opsional)" value="Umum">
                </div>
                <div class="cart-box" id="cartBox">
                    <p class="text-muted small text-center py-3" id="cartEmpty">Keranjang masih kosong</p>
                </div>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Subtotal</span><span id="subtotalText">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">Diskon (Rp)</span>
                    <input type="number" id="diskonInput" class="form-control form-control-sm text-end" style="width:120px;" value="0" min="0">
                </div>
                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span>Total</span><span class="text-mf" id="totalText">Rp 0</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Metode Bayar</label>
                    <select id="metodeBayar" class="form-select form-select-sm">
                        <option value="tunai">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="debit">Debit/Kredit</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Uang Dibayar (Rp)</label>
                    <input type="number" id="bayarInput" class="form-control" placeholder="0">
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span>Kembalian</span><span class="fw-semibold" id="kembalianText">Rp 0</span>
                </div>

                <button class="btn btn-mf w-100 py-2 fw-semibold" id="btnBayar" disabled>
                    <i class="bi bi-check-circle me-1"></i>Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const PREFIX = '<?= $prefix ?>';
let semuaProduk = [];
let cart = {}; // { produk_id: {nama, harga, qty, stok, satuan} }

async function muatProduk() {
    const res = await fetch(PREFIX + 'kasir/get_produk.php');
    semuaProduk = await res.json();
    renderProduk();
}

function renderProduk() {
    const q = document.getElementById('searchProduk').value.toLowerCase();
    const kat = document.getElementById('filterKategori').value;
    const grid = document.getElementById('produkGrid');
    const filtered = semuaProduk.filter(p => {
        const matchQ = p.nama_produk.toLowerCase().includes(q) || p.kode_produk.toLowerCase().includes(q);
        const matchKat = !kat || p.kategori_id == kat;
        return matchQ && matchKat;
    });
    if (!filtered.length) {
        grid.innerHTML = '<div class="text-center text-muted py-4 col-12">Produk tidak ditemukan</div>';
        return;
    }
    grid.innerHTML = filtered.map(p => `
        <div class="col-6 col-md-4">
            <div class="card produk-item p-2" onclick="tambahKeCart(${p.id})">
                <div class="p-nama">${p.nama_produk}</div>
                <div class="p-harga">Rp ${Number(p.harga_jual).toLocaleString('id-ID')}</div>
                <div class="p-stok">Stok: ${parseFloat(p.stok)} ${p.satuan}</div>
            </div>
        </div>
    `).join('');
}

function tambahKeCart(id) {
    const p = semuaProduk.find(x => x.id == id);
    if (!p) return;
    if (parseFloat(p.stok) <= 0) { alert('Stok produk habis!'); return; }

    if (cart[id]) {
        if (cart[id].qty + 1 > parseFloat(p.stok)) { alert('Stok tidak mencukupi!'); return; }
        cart[id].qty += 1;
    } else {
        cart[id] = { nama: p.nama_produk, harga: parseFloat(p.harga_jual), qty: 1, stok: parseFloat(p.stok), satuan: p.satuan };
    }
    renderCart();
}

function ubahQty(id, delta) {
    if (!cart[id]) return;
    const newQty = cart[id].qty + delta;
    if (newQty <= 0) { delete cart[id]; renderCart(); return; }
    if (newQty > cart[id].stok) { alert('Stok tidak mencukupi!'); return; }
    cart[id].qty = newQty;
    renderCart();
}

function setQty(id, val) {
    val = parseFloat(val);
    if (isNaN(val) || val <= 0) { delete cart[id]; renderCart(); return; }
    if (val > cart[id].stok) { val = cart[id].stok; }
    cart[id].qty = val;
    renderCart();
}

function hapusItem(id) {
    delete cart[id];
    renderCart();
}

function renderCart() {
    const box = document.getElementById('cartBox');
    const ids = Object.keys(cart);
    if (!ids.length) {
        box.innerHTML = '<p class="text-muted small text-center py-3">Keranjang masih kosong</p>';
    } else {
        box.innerHTML = ids.map(id => {
            const it = cart[id];
            const subtotal = it.harga * it.qty;
            return `
            <div class="cart-row d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 me-2">
                    <div class="fw-semibold">${it.nama}</div>
                    <div class="text-muted" style="font-size:.75rem;">Rp ${it.harga.toLocaleString('id-ID')} x ${it.qty}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${id},-1)">-</button>
                    <input type="number" step="0.01" class="form-control form-control-sm qty-input" value="${it.qty}" onchange="setQty(${id}, this.value)">
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${id},1)">+</button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 ms-1" onclick="hapusItem(${id})"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
        }).join('');
    }
    hitungTotal();
}

function hitungTotal() {
    const subtotal = Object.values(cart).reduce((s, it) => s + it.harga * it.qty, 0);
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const total = Math.max(subtotal - diskon, 0);
    document.getElementById('subtotalText').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('totalText').textContent = 'Rp ' + total.toLocaleString('id-ID');
    hitungKembalian();
    document.getElementById('btnBayar').disabled = Object.keys(cart).length === 0;
}

function hitungKembalian() {
    const subtotal = Object.values(cart).reduce((s, it) => s + it.harga * it.qty, 0);
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const total = Math.max(subtotal - diskon, 0);
    const bayar = parseFloat(document.getElementById('bayarInput').value) || 0;
    const kembali = bayar - total;
    document.getElementById('kembalianText').textContent = 'Rp ' + (kembali > 0 ? kembali.toLocaleString('id-ID') : 0);
}

document.getElementById('searchProduk').addEventListener('input', renderProduk);
document.getElementById('filterKategori').addEventListener('change', renderProduk);
document.getElementById('diskonInput').addEventListener('input', hitungTotal);
document.getElementById('bayarInput').addEventListener('input', hitungKembalian);
document.getElementById('btnKosongkan').addEventListener('click', () => { if(confirm('Kosongkan keranjang?')){ cart = {}; renderCart(); }});

document.getElementById('btnBayar').addEventListener('click', async () => {
    const subtotal = Object.values(cart).reduce((s, it) => s + it.harga * it.qty, 0);
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const total = Math.max(subtotal - diskon, 0);
    const bayar = parseFloat(document.getElementById('bayarInput').value) || 0;
    const metode = document.getElementById('metodeBayar').value;

    if (metode === 'tunai' && bayar < total) {
        alert('Uang dibayar kurang dari total belanja!');
        return;
    }

    const items = Object.entries(cart).map(([id, it]) => ({
        produk_id: id, nama: it.nama, harga: it.harga, qty: it.qty
    }));

    const btn = document.getElementById('btnBayar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
        const res = await fetch(PREFIX + 'kasir/proses_transaksi.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                items, subtotal, diskon, total,
                bayar: metode === 'tunai' ? bayar : total,
                metode,
                nama_pelanggan: document.getElementById('namaPelanggan').value || 'Umum',
                csrf_token: '<?= csrfToken() ?>'
            })
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = PREFIX + 'kasir/struk.php?id=' + data.transaksi_id;
        } else {
            alert(data.message || 'Gagal memproses transaksi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Proses Pembayaran';
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Proses Pembayaran';
    }
});

muatProduk();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
