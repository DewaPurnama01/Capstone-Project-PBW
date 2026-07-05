export type Role = 'owner' | 'admin' | 'kasir';

export interface User {
  id: number;
  name: string;
  username: string;
  role: Role;
  avatar_initial: string;
}

export interface Customer {
  id: number;
  name: string;
  phone: string | null;
  email: string | null;
  segment: 'Baru' | 'Reguler' | 'Member' | 'VIP';
  loyalty_points: number;
  favorite_menu: string | null;
  visit_count: number;
  total_spent: number;
  joined_at: string | null;
  last_visit_at: string | null;
}

export interface Product {
  id: number;
  name: string;
  category: string;
  price: number;
  cost_price: number;
  is_active: boolean;
}

export interface TransactionItem {
  id: number;
  product_id: number | null;
  product_name: string;
  qty: number;
  price: number;
  subtotal: number;
}

export interface Transaction {
  id: number;
  code: string;
  customer: Customer | null;
  payment_method: 'QRIS' | 'Tunai' | 'Transfer';
  status: 'proses' | 'selesai' | 'dibatalkan';
  total: number;
  transacted_at: string;
  items: TransactionItem[];
}

export interface InventoryItem {
  id: number;
  name: string;
  category: 'Bahan Baku' | 'Kemasan' | 'Makanan';
  unit: string;
  current_stock: number;
  min_stock: number;
  max_stock: number;
  unit_price: number;
  supplier_name: string | null;
  is_coffee_bean: boolean;
  stock_status: 'kritis' | 'rendah' | 'aman';
  stock_percent: number;
}

export interface Partner {
  id: number;
  name: string;
  phone: string | null;
  address: string | null;
  commodity: string;
  is_active: boolean;
  on_time_rate: number;
  quality_score: number;
  joined_at: string | null;
}

export interface PartnerOffer {
  id: number;
  restock_request_id: number;
  partner_id: number;
  partner: Partner;
  price_per_unit: number;
  eta_days: number;
  status: 'menunggu' | 'dipilih' | 'ditolak';
}

export interface RestockRequest {
  id: number;
  code: string;
  inventory_item_id: number;
  inventory_item: InventoryItem;
  specification: string | null;
  qty_needed: number;
  unit: string;
  status: 'draft' | 'disiarkan' | 'ditawar' | 'po_dibuat' | 'selesai';
  offers: PartnerOffer[];
}

export interface PurchaseOrder {
  id: number;
  code: string;
  reference_code: string | null;
  partner: Partner;
  qty: number;
  unit: string;
  unit_price: number;
  total: number;
  delivery_status: 'dikirim' | 'diterima' | 'qc_lulus' | 'retur' | 'selesai';
  payment_status: 'belum_bayar' | 'sebagian' | 'lunas';
  estimated_delivery: string | null;
  received_at: string | null;
  paid_amount?: number;
  remaining_amount?: number;
}
