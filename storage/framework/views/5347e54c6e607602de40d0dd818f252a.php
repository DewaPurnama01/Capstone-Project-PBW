<?php $__env->startSection('title','Transaksi Baru'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding:16px 24px 0;">
    <a href="<?php echo e(route('transaksi.index')); ?>" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;flex-shrink:0;">←</a>
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Transaksi Baru</h1>
        <p style="color:#5a6b57;font-size:13px;margin-top:1px;"><?php echo e(date('d M Y, H:i')); ?></p>
    </div>
</div>

<?php if($errors->any()): ?>
<div class="alert-error" style="margin:0 24px 16px;">
    <?php echo e($errors->first()); ?>

</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;padding:0 24px 24px;align-items:start;">

    
    <div>
        <div class="card" style="padding:12px;margin-bottom:12px;">
            <input type="text" id="menuSearch" placeholder="🔍 Cari menu..." class="form-input" oninput="filterMenu(this.value)" style="margin:0;">
        </div>

        
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <?php $__currentLoopData = ['Semua','KOPI','NON_KOPI','MAKANAN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="filterKategori('<?php echo e($cat); ?>')" data-cat="<?php echo e($cat); ?>"
                class="tab-btn <?php echo e($cat === 'Semua' ? 'tab-active' : ''); ?>"
                style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:none;cursor:pointer;
                       background:<?php echo e($cat === 'Semua' ? '#306D29' : '#E7E1B1'); ?>;
                       color:<?php echo e($cat === 'Semua' ? '#fff' : '#306D29'); ?>;">
                <?php echo e($cat === 'NON_KOPI' ? 'Non-Kopi' : ucfirst(strtolower($cat))); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div id="menuGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1px;background:#f0ede0;">
                <?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="menu-item"
                     data-cat="<?php echo e($m->kategori); ?>"
                     data-name="<?php echo e(strtolower($m->nama_menu)); ?>"
                     data-id="<?php echo e($m->id); ?>"
                     data-nama="<?php echo e($m->nama_menu); ?>"
                     data-harga="<?php echo e($m->harga); ?>"
                     data-resep="<?php echo e(json_encode($m->resep)); ?>"
                     onmouseenter="showRecipeTooltip(this)"
                     onmouseleave="hideRecipeTooltip()"
                     onclick="addToCart(<?php echo e($m->id); ?>, '<?php echo e(addslashes($m->nama_menu)); ?>', <?php echo e($m->harga); ?>)"
                     style="background:#fff;padding:14px;cursor:pointer;transition:background 0.15s;user-select:none;position:relative;">
                    <div style="font-size:22px;margin-bottom:5px;">
                        <?php if($m->kategori === 'KOPI'): ?> ☕
                        <?php elseif($m->kategori === 'NON_KOPI'): ?> 🥤
                        <?php else: ?> 🍽️
                        <?php endif; ?>
                    </div>
                    <div style="font-size:13px;font-weight:600;color:#1a2a1a;line-height:1.3;"><?php echo e($m->nama_menu); ?></div>
                    <div style="font-size:13px;font-weight:700;color:#0D530E;margin-top:4px;">Rp <?php echo e(number_format($m->harga,0,',','.')); ?></div>
                    <?php if(!empty($m->resep)): ?>
                    <div style="font-size:10px;color:#9ca3af;margin-top:6px;padding-top:6px;border-top:1px solid #f0ede0;cursor:help;text-decoration:underline;">📋 Lihat resep</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div style="position:sticky;top:80px;">
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:14px 16px;border-bottom:1px solid #E7E1B1;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:14px;font-weight:600;color:#0D530E;">🛒 Keranjang</span>
                <span id="cartCount" style="background:#0D530E;color:#fff;border-radius:20px;padding:2px 10px;font-size:12px;">0</span>
            </div>

            
            <div id="cartItems" style="min-height:100px;max-height:260px;overflow-y:auto;padding:8px;">
                <div id="emptyCart" style="padding:28px;text-align:center;color:#bbb;font-size:13px;">
                    Pilih menu dari daftar
                </div>
            </div>

            
            <div style="padding:12px 16px;border-top:1px solid #E7E1B1;background:#FBF5DD;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:13px;color:#5a6b57;">
                    <span>Subtotal</span><span id="subtotal">Rp 0</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:13px;color:#d4183d;" id="diskonRow" style="display:none;">
                    <span>Diskon Poin</span><span id="discountDisplay">- Rp 0</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#0D530E;margin-top:8px;padding-top:8px;border-top:1px solid #E7E1B1;">
                    <span>Total</span><span id="totalDisplay">Rp 0</span>
                </div>
            </div>

            
            <div style="padding:14px 16px;border-top:1px solid #E7E1B1;">
                <form method="POST" action="<?php echo e(route('transaksi.store')); ?>" id="posForm">
                    <?php echo csrf_field(); ?>
                    
                    <input type="hidden" name="cart_data" id="cartData">
                    <input type="hidden" name="total_bayar" id="totalBayar" value="0">
                    <input type="hidden" name="diskon" id="diskonHidden" value="0">

                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px;font-weight:600;color:#5a6b57;display:block;margin-bottom:4px;">Pelanggan</label>
                        <select name="id" id="pelangganSelect" class="form-input" style="font-size:13px;" onchange="loadPelangganPoin(this.value)">
                            <option value="">Walk-in / Tamu</option>
                            <?php $__currentLoopData = $pelanggan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" data-poin="<?php echo e($p->poin); ?>" data-segmen="<?php echo e($p->segmen); ?>">
                                <?php echo e($p->nama); ?> <?php if($p->poin > 0): ?>(<?php echo e(number_format($p->poin)); ?> poin)<?php endif; ?>
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div id="pelangganInfo" style="display:none;margin-top:6px;font-size:12px;background:#e8f5e8;border-radius:6px;padding:6px 10px;color:#0D530E;">
                            Poin: <strong id="poinDisplay">0</strong> · Segmen: <strong id="segmenDisplay">-</strong>
                        </div>
                    </div>

                    
                    <div id="redeemSection" style="display:none;margin-bottom:12px;">
                        <label style="font-size:12px;font-weight:600;color:#5a6b57;display:block;margin-bottom:4px;">
                            Gunakan Poin <span style="font-weight:400;">(1 poin = Rp 100)</span>
                        </label>
                        <div style="display:flex;gap:6px;">
                            <input type="number" id="poinRedeem" name="poin_digunakan" class="form-input"
                                style="font-size:13px;" placeholder="0" min="0" value="0" oninput="hitungDiskon()">
                            <button type="button" onclick="maxRedeem()"
                                style="padding:8px 10px;background:#f0f9f0;color:#0D530E;border:1px solid #b8dbb8;border-radius:8px;font-size:12px;cursor:pointer;white-space:nowrap;">
                                Maks
                            </button>
                        </div>
                    </div>

                    
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#5a6b57;display:block;margin-bottom:6px;">Metode Pembayaran</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                            <?php $__currentLoopData = ['CASH' => 'Tunai', 'QRIS' => 'QRIS', 'TRANSFER' => 'Transfer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="metode_bayar" value="<?php echo e($val); ?>" style="display:none;"
                                    <?php echo e($val === 'CASH' ? 'checked' : ''); ?> onchange="updateMetodeUI()">
                                <div class="metode-btn" data-val="<?php echo e($val); ?>"
                                    style="padding:8px;text-align:center;border:2px solid <?php echo e($val === 'CASH' ? '#0D530E' : '#dde8dd'); ?>;border-radius:8px;font-size:12px;font-weight:600;color:<?php echo e($val === 'CASH' ? '#0D530E' : '#888'); ?>;background:<?php echo e($val === 'CASH' ? '#e8f5e8' : '#fff'); ?>;transition:all 0.15s;">
                                    <?php echo e($label); ?>

                                </div>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#5a6b57;display:block;margin-bottom:4px;">Catatan <span style="font-weight:400;">(opsional)</span></label>
                        <textarea name="catatan" class="form-input" rows="2"
                            placeholder="Pesanan khusus, kurang manis, dll..."
                            style="font-size:13px;resize:none;"></textarea>
                    </div>

                    <button type="button" onclick="submitOrder()" class="btn-primary"
                        style="width:100%;font-size:14px;padding:13px;" id="btnBayar" disabled>
                        Proses Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.menu-item:hover { background: #f0f9f0 !important; }
.menu-item:active { background: #d4edda !important; }
.menu-item.hidden { display: none !important; }
</style>

<script>
let cart = {};
let poinTersedia = 0;

function formatRp(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function addToCart(id, nama, harga) {
    if (!cart[id]) cart[id] = { id, nama, harga, qty: 0 };
    cart[id].qty++;
    renderCart();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function renderCart() {
    const items = Object.values(cart);
    document.getElementById('cartCount').textContent = items.reduce((s,i) => s + i.qty, 0);

    if (!items.length) {
        document.getElementById('cartItems').innerHTML =
            '<div id="emptyCart" style="padding:28px;text-align:center;color:#bbb;font-size:13px;">Pilih menu dari daftar</div>';
        document.getElementById('btnBayar').disabled = true;
        updateTotals();
        return;
    }

    document.getElementById('cartItems').innerHTML = items.map(i => `
        <div style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;margin-bottom:4px;background:#fafffe;border:1px solid #f0ede0;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:#1a2a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${i.nama}</div>
                <div style="font-size:12px;color:#5a6b57;">${formatRp(i.harga)}</div>
            </div>
            <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                <button onclick="changeQty(${i.id},-1)" style="width:24px;height:24px;border:1px solid #dde8dd;border-radius:6px;background:#fff;cursor:pointer;font-size:16px;line-height:1;">−</button>
                <span style="font-size:13px;font-weight:700;min-width:20px;text-align:center;">${i.qty}</span>
                <button onclick="changeQty(${i.id},1)"  style="width:24px;height:24px;border:1px solid #dde8dd;border-radius:6px;background:#fff;cursor:pointer;font-size:16px;line-height:1;">+</button>
            </div>
            <div style="font-size:13px;font-weight:700;color:#0D530E;min-width:64px;text-align:right;">${formatRp(i.harga*i.qty)}</div>
        </div>
    `).join('');

    document.getElementById('btnBayar').disabled = false;
    updateTotals();
}

function updateTotals() {
    const sub = Object.values(cart).reduce((s,i) => s + i.harga * i.qty, 0);
    const poin = parseInt(document.getElementById('poinRedeem')?.value || 0);
    const diskon = Math.min(poin * 100, sub);
    const total  = Math.max(0, sub - diskon);

    document.getElementById('subtotal').textContent = formatRp(sub);
    document.getElementById('discountDisplay').textContent = '- ' + formatRp(diskon);
    document.getElementById('diskonRow').style.display = diskon > 0 ? 'flex' : 'none';
    document.getElementById('totalDisplay').textContent = formatRp(total);
    document.getElementById('totalBayar').value = total;
    document.getElementById('diskonHidden').value = diskon;
}

function hitungDiskon() { updateTotals(); }

function loadPelangganPoin(id) {
    const sel  = document.getElementById('pelangganSelect');
    const opt  = sel.options[sel.selectedIndex];
    const info = document.getElementById('pelangganInfo');
    const redeem = document.getElementById('redeemSection');

    if (id) {
        poinTersedia = parseInt(opt.dataset.poin || 0);
        document.getElementById('poinDisplay').textContent = Number(poinTersedia).toLocaleString('id-ID');
        document.getElementById('segmenDisplay').textContent = opt.dataset.segmen || '-';
        info.style.display   = 'block';
        redeem.style.display = poinTersedia > 0 ? 'block' : 'none';
    } else {
        poinTersedia = 0;
        info.style.display   = 'none';
        redeem.style.display = 'none';
        const pr = document.getElementById('poinRedeem');
        if (pr) pr.value = 0;
    }
    updateTotals();
}

function maxRedeem() {
    const sub     = Object.values(cart).reduce((s,i) => s + i.harga * i.qty, 0);
    const maxPoin = Math.floor(sub / 100);
    document.getElementById('poinRedeem').value = Math.min(poinTersedia, maxPoin);
    updateTotals();
}

function updateMetodeUI() {
    document.querySelectorAll('.metode-btn').forEach(b => {
        const radio = document.querySelector(`input[value="${b.dataset.val}"]`);
        const sel = radio.checked;
        b.style.borderColor = sel ? '#0D530E' : '#dde8dd';
        b.style.color       = sel ? '#0D530E' : '#888';
        b.style.background  = sel ? '#e8f5e8' : '#fff';
    });
}

function filterMenu(q) {
    document.querySelectorAll('.menu-item').forEach(el => {
        const match = el.dataset.name.includes(q.toLowerCase());
        el.style.display = match ? '' : 'none';
    });
}

function filterKategori(cat) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        const active = btn.dataset.cat === cat;
        btn.style.background = active ? '#306D29' : '#E7E1B1';
        btn.style.color      = active ? '#fff'    : '#306D29';
    });
    document.querySelectorAll('.menu-item').forEach(el => {
        el.style.display = (cat === 'Semua' || el.dataset.cat === cat) ? '' : 'none';
    });
    // reset text search
    const s = document.getElementById('menuSearch');
    if (s) s.value = '';
}

function submitOrder() {
    if (!Object.keys(cart).length) {
        alert('Keranjang masih kosong!');
        return;
    }
    document.getElementById('cartData').value = JSON.stringify(Object.values(cart));
    document.getElementById('posForm').submit();
}

// Tooltip Resep
function showRecipeTooltip(menuEl) {
    const resepData = menuEl.dataset.resep;
    if (!resepData) return;
    
    try {
        const resep = JSON.parse(resepData);
        if (!resep || resep.length === 0) return;
        
        const tooltip = document.getElementById('recipeTooltip');
        const namaMenu = menuEl.dataset.nama;
        
        let html = `<strong>${namaMenu}</strong><br>`;
        resep.forEach(r => {
            html += `• ${r.bahan}: ${r.jumlah}g/ml<br>`;
        });
        
        tooltip.innerHTML = html;
        tooltip.style.display = 'block';
        
        const rect = menuEl.getBoundingClientRect();
        tooltip.style.left = (rect.left + rect.width / 2 - 70) + 'px';
        tooltip.style.top = (rect.top - 130) + 'px';
    } catch (e) {
        console.error('Error parsing recipe:', e);
    }
}

function hideRecipeTooltip() {
    const tooltip = document.getElementById('recipeTooltip');
    if (tooltip) tooltip.style.display = 'none';
}
</script>


<div id="recipeTooltip" style="display:none;position:fixed;background:#1a2a1a;color:#fff;padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;z-index:9999;pointer-events:none;min-width:140px;box-shadow:0 4px 12px rgba(0,0,0,0.3);white-space:nowrap;"></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hindi\OneDrive\Desktop\TUGAS AKHIR\cns\cns-laravel-v2\cns-fixed\resources\views/transaksi/create.blade.php ENDPATH**/ ?>