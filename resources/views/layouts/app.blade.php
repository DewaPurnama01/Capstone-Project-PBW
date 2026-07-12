<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        :root {
            --green-dark: #0D530E;
            --green-mid: #306D29;
            --green-light: #E7E1B1;
            --cream: #FBF5DD;
            --cream-bg: #f5f2e8;
            --red-alert: #d4183d;
            --sidebar-w: 224px;
        }

        /* Layout */
        body { background: var(--cream-bg); min-height: 100vh; margin: 0; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(175deg, #0a3d0b 0%, #1a5c1c 60%, #2a6e2c 100%);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-logo {
            width: 34px;
            height: 34px;
            background: rgba(255,255,255,0.12);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-nav { flex: 1; padding: 12px 10px; overflow-y: auto; }
        .sidebar-nav-label {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 8px;
            margin: 16px 0 6px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
            border-radius: 9px;
            font-size: 13px;
            color: rgba(255,255,255,0.62);
            text-decoration: none;
            transition: all 0.12s;
            margin-bottom: 2px;
            font-weight: 400;
        }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.9); }
        .nav-link.active {
            background: rgba(255,255,255,0.16);
            color: #fff;
            font-weight: 600;
        }
        .nav-link-disabled {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
            border-radius: 9px;
            font-size: 13px;
            color: rgba(255,255,255,0.22);
            cursor: not-allowed;
            margin-bottom: 2px;
        }
        .sidebar-user {
            padding: 12px 14px;
            border-top: 1px solid rgba(255,255,255,0.09);
        }
        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .sidebar-avatar {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Main content */
        .main-content { flex: 1; overflow-y: auto; }

        /* Cards */
        .card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* Buttons */
        .btn-primary {
            background: #306D29;
            color: #fff;
            border-radius: 9px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary:hover { opacity: 0.87; }
        .btn-secondary {
            background: #E7E1B1;
            color: #306D29;
            border-radius: 9px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secondary:hover { background: #ddd8a5; }

        /* Forms */
        .form-input {
            border: 1px solid #ddd8c8;
            background: #fdfaf3;
            border-radius: 8px;
            padding: 9px 12px;
            width: 100%;
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus {
            outline: none;
            border-color: #306D29;
            box-shadow: 0 0 0 3px rgba(48,109,41,0.1);
        }

        /* Alerts */
        .alert-success { background: #ecf8ec; color: #1a6b1a; border: 1px solid #b5d9b5; border-radius: 10px; padding: 12px 16px; }
        .alert-error   { background: #fdecea; color: #c0392b; border: 1px solid #f5b8b8; border-radius: 10px; padding: 12px 16px; }
        .alert-warning { background: #fef9ec; color: #8B6914; border: 1px solid #f3e099; border-radius: 10px; padding: 12px 16px; }
        .alert-info    { background: #e8f4fd; color: #1a6da6; border: 1px solid #acd5f5; border-radius: 10px; padding: 12px 16px; }

        /* Table */
        .table-row { transition: background 0.1s; }
        .table-row:hover { background: #fdfaf3; }

        /* Badges */
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success  { background: #d4e8d0; color: #306D29; }
        .badge-warning  { background: #fff3cd; color: #8B6914; }
        .badge-danger   { background: #ffe4e4; color: #d4183d; }
        .badge-neutral  { background: #f0f0f0; color: #666; }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(0,0,0,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 24px;
        }

        /* Page content padding */
        .page-content { padding: 24px; }
    </style>
</head>
<body>
<div class="app-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                    <path d="M17 8h1a4 4 0 0 1 0 8h-1" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/>
                    <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="6" y1="2" x2="6" y2="4" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/>
                    <line x1="10" y1="2" x2="10" y2="4" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/>
                    <line x1="14" y1="2" x2="14" y2="4" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <p style="color:#fff;font-weight:700;font-size:14px;line-height:1.2;">Cafe CNS</p>
                <p style="color:rgba(255,255,255,0.38);font-size:10px;">Catch New Serenity</p>
            </div>
        </div>

        @php
            $user    = session('user');
            $role    = $user['role'] ?? '';
            $navGroups = [
                'Operasional' => [
                    ['path'=>'dashboard',      'label'=>'Dashboard',         'icon'=>'🏠', 'roles'=>['Owner','Admin','Kasir']],
                    ['path'=>'transaksi',       'label'=>'Transaksi / POS',   'icon'=>'🧾', 'roles'=>['Owner','Kasir']],
                    ['path'=>'pelanggan',       'label'=>'Pelanggan',         'icon'=>'👥', 'roles'=>['Owner','Kasir']],
                ],
                'Pengadaan' => [
                    ['path'=>'inventori',       'label'=>'Inventori Bahan',   'icon'=>'📦', 'roles'=>['Owner','Admin']],
                    ['path'=>'kemitraan',       'label'=>'Portal Kemitraan',  'icon'=>'🌾', 'roles'=>['Owner','Admin']],
                    ['path'=>'purchase-orders', 'label'=>'Purchase Orders',   'icon'=>'📋', 'roles'=>['Owner','Admin']],
                ],
                'Analitik' => [
                    ['path'=>'laporan',         'label'=>'Laporan',           'icon'=>'📊', 'roles'=>['Owner']],
                ],
            ];
        @endphp

        <nav class="sidebar-nav">
            @foreach($navGroups as $groupName => $items)
                <div class="sidebar-nav-label">{{ $groupName }}</div>
                @foreach($items as $item)
                    @if(in_array($role, $item['roles']))
                        @php $isActive = request()->is($item['path'].'*'); @endphp
                        <a href="/{{ $item['path'] }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <span style="font-size:14px;width:18px;text-align:center;">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="nav-link-disabled">
                            <span style="font-size:14px;width:18px;text-align:center;">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" style="margin-left:auto;opacity:0.4"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2"/></svg>
                        </span>
                    @endif
                @endforeach
            @endforeach
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <div class="sidebar-avatar">{{ $user['avatar'] ?? '??' }}</div>
                <div style="flex:1;min-width:0;">
                    <p style="color:#fff;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user['name'] ?? 'User' }}</p>
                    <p style="color:rgba(255,255,255,0.38);font-size:10px;">{{ $user['role'] ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;font-size:12px;color:rgba(255,255,255,0.55);background:transparent;border:none;cursor:pointer;transition:background 0.12s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <main class="main-content">
        {{-- Flash messages --}}
        @foreach(['success','error','warning','info'] as $type)
            @if(session($type))
            <div class="alert-{{ $type }} mx-6 mt-4 flex items-center gap-2" style="font-size:14px;">
                <span>{{ ['success'=>'✓','error'=>'✕','warning'=>'⚠','info'=>'ℹ'][$type] }}</span>
                {{ session($type) }}
            </div>
            @endif
        @endforeach
        @if(session('info'))
        <div class="alert-info mx-6 mt-4 flex items-center gap-2" style="font-size:14px;">
            ℹ {{ session('info') }}
        </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
