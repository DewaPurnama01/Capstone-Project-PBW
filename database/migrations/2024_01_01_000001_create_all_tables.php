<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users - Akun Internal Sistem (Owner, Admin, Kasir)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->enum('role', ['Owner', 'Admin', 'Kasir'])->default('Kasir');
            $table->timestamps();
        });

        // tb_bahan - Data Stok Bahan Baku (Tabel 3.2 dari dokumen SI)
        Schema::create('tb_bahan', function (Blueprint $table) {
            $table->increments('id_bahan');
            $table->string('nama_bahan', 100);
            $table->string('kategori', 50)->default('Bahan Baku');
            $table->string('satuan', 20);
            $table->decimal('jumlah_stok', 10, 2)->default(0);
            $table->decimal('batas_minimum', 10, 2)->default(0);
            $table->decimal('batas_maksimum', 10, 2)->default(0);
            $table->decimal('harga_per_unit', 15, 2)->default(0);
            $table->string('supplier', 100)->nullable();
            $table->enum('status_stok', ['NORMAL', 'RENDAH', 'HABIS'])->default('NORMAL');
            $table->boolean('is_coffee')->default(false);
            $table->dateTime('tanggal_update')->nullable();
        });

        // tb_mitra - Data Profil Mitra/Petani (Tabel 3.3)
        Schema::create('tb_mitra', function (Blueprint $table) {
            $table->increments('id_mitra');
            $table->string('nama_mitra', 100);
            $table->string('no_hp', 15);
            $table->text('alamat')->nullable();
            $table->string('komoditas', 100)->default('Biji Kopi');
            $table->tinyInteger('status_aktif')->default(1);
            $table->decimal('rating', 3, 1)->default(4.5);
            $table->integer('total_order')->default(0);
            $table->integer('persen_on_time')->default(100);
            $table->integer('persen_kualitas')->default(100);
            $table->string('catatan', 255)->nullable();
            $table->date('tanggal_daftar');
        });

        // tb_broadcast - Data Pengiriman Permintaan (Tabel 3.4)
        Schema::create('tb_broadcast', function (Blueprint $table) {
            $table->increments('id_broadcast');
            $table->unsignedInteger('id_bahan');
            $table->decimal('jumlah_dibutuhkan', 10, 2);
            $table->decimal('harga_target', 15, 2);
            $table->dateTime('tanggal_kirim');
            $table->dateTime('batas_respon');
            $table->text('catatan')->nullable();
            $table->enum('status_broadcast', ['AKTIF', 'DITUTUP', 'SELESAI'])->default('AKTIF');
            $table->foreign('id_bahan')->references('id_bahan')->on('tb_bahan');
        });

        // tb_broadcast_token - token unik per mitra untuk form penawaran
        Schema::create('tb_broadcast_token', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('broadcast_id');
            $table->unsignedInteger('mitra_id');
            $table->string('token', 64)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->foreign('broadcast_id')->references('id_broadcast')->on('tb_broadcast')->onDelete('cascade');
            $table->foreign('mitra_id')->references('id_mitra')->on('tb_mitra')->onDelete('cascade');
        });

        // tb_penawaran - Data Penawaran dari Mitra (Tabel 3.5)
        Schema::create('tb_penawaran', function (Blueprint $table) {
            $table->increments('id_penawaran');
            $table->unsignedInteger('id_broadcast');
            $table->unsignedInteger('id_mitra');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah_tersedia', 10, 2);
            $table->date('estimasi_kirim');
            $table->text('catatan_mitra')->nullable();
            $table->enum('status_penawaran', ['MENUNGGU', 'DITERIMA', 'DITOLAK'])->default('MENUNGGU');
            $table->dateTime('tanggal_input');
            $table->foreign('id_broadcast')->references('id_broadcast')->on('tb_broadcast');
            $table->foreign('id_mitra')->references('id_mitra')->on('tb_mitra');
        });

        // tb_purchase_order - Data Purchase Order (Tabel 3.6)
        Schema::create('tb_purchase_order', function (Blueprint $table) {
            $table->increments('id_po');
            $table->string('nomor_po', 30)->unique();
            $table->unsignedInteger('id_penawaran');
            $table->date('tanggal_terbit');
            $table->decimal('total_nilai', 15, 2);
            $table->enum('status_po', ['DITERBITKAN', 'DIKIRIM', 'SELESAI', 'DIBATALKAN'])->default('DITERBITKAN');
            $table->foreign('id_penawaran')->references('id_penawaran')->on('tb_penawaran');
        });

        // tb_penerimaan - Data Penerimaan Barang (Tabel 3.7)
        Schema::create('tb_penerimaan', function (Blueprint $table) {
            $table->increments('id_penerimaan');
            $table->unsignedInteger('id_po');
            $table->date('tanggal_terima');
            $table->decimal('jumlah_diterima', 10, 2);
            $table->text('kondisi_fisik')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->foreign('id_po')->references('id_po')->on('tb_purchase_order');
            $table->foreign('id_admin')->references('id')->on('users')->nullOnDelete();
        });

        // tb_quality_control - Data Hasil QC (Tabel 3.8)
        Schema::create('tb_quality_control', function (Blueprint $table) {
            $table->increments('id_qc');
            $table->unsignedInteger('id_penerimaan');
            $table->enum('hasil_qc', ['LOLOS', 'TIDAK_LOLOS']);
            $table->text('catatan_qc')->nullable();
            $table->string('foto_dokumentasi', 255)->nullable();
            $table->tinyInteger('skor_aroma')->default(0);
            $table->tinyInteger('skor_warna')->default(0);
            $table->tinyInteger('skor_ukuran')->default(0);
            $table->tinyInteger('skor_kebersihan')->default(0);
            $table->dateTime('tanggal_qc');
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->foreign('id_penerimaan')->references('id_penerimaan')->on('tb_penerimaan');
            $table->foreign('id_admin')->references('id')->on('users')->nullOnDelete();
        });

        // tb_hutang - Data Rekonsiliasi dan Pembayaran (Tabel 3.9)
        Schema::create('tb_hutang', function (Blueprint $table) {
            $table->increments('id_hutang');
            $table->unsignedInteger('id_qc');
            $table->unsignedInteger('id_mitra');
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status_bayar', ['BELUM_BAYAR', 'SUDAH_BAYAR'])->default('BELUM_BAYAR');
            $table->date('tanggal_lunas')->nullable();
            $table->string('bukti_bayar', 255)->nullable();
            $table->foreign('id_qc')->references('id_qc')->on('tb_quality_control');
            $table->foreign('id_mitra')->references('id_mitra')->on('tb_mitra');
        });

        // pelanggan - CRM Pelanggan
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('no_hp', 15);
            $table->string('email', 100)->nullable();
            $table->enum('segmen', ['VIP', 'Member', 'Reguler', 'Baru'])->default('Baru');
            $table->string('menu_favorit', 100)->nullable();
            $table->integer('poin')->default(0);
            $table->integer('total_kunjungan')->default(0);
            $table->decimal('total_belanja', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'tidak aktif'])->default('aktif');
            $table->date('tanggal_daftar');
            $table->date('terakhir_kunjungan')->nullable();
        });

        // transaksi - Transaksi Penjualan
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->enum('segmen', ['VIP', 'Member', 'Reguler', 'Baru'])->default('Reguler');
            $table->enum('metode_bayar', ['QRIS', 'Tunai', 'Transfer']);
            $table->decimal('total', 15, 2);
            $table->enum('status', ['proses', 'selesai', 'batal'])->default('proses');
            $table->string('kasir', 100)->nullable();
            $table->timestamps();
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan')->nullOnDelete();
        });

        // detail_transaksi - Item dalam Transaksi
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->string('nama_item', 100);
            $table->integer('qty');
            $table->decimal('harga', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('tb_hutang');
        Schema::dropIfExists('tb_quality_control');
        Schema::dropIfExists('tb_penerimaan');
        Schema::dropIfExists('tb_purchase_order');
        Schema::dropIfExists('tb_penawaran');
        Schema::dropIfExists('tb_broadcast_token');
        Schema::dropIfExists('tb_broadcast');
        Schema::dropIfExists('tb_mitra');
        Schema::dropIfExists('tb_bahan');
        Schema::dropIfExists('users');
    }
};
