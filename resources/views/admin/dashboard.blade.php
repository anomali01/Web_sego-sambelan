@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Penjual')

@section('content')
<!-- Banner Selamat Datang Premium -->
<div class="admin-welcome-banner card" style="display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; margin-bottom: 2rem; border-left: 6px solid var(--primary); background: var(--primary); border-radius: var(--radius); overflow: hidden; position: relative;">
    <div style="flex-grow: 1; z-index: 2; padding-right: 1.5rem;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: white; margin: 0 0 0.5rem 0; line-height: 1.2;">Selamat Datang Kembali, Penjual! 🔥</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; max-width: 650px; line-height: 1.6; margin: 0;">
            Kelola menu lezat, pantau pesanan pelanggan, dan analisa pergerakan kas masuk Sego Sambelan Anda di satu tempat terpusat yang premium.
        </p>
    </div>
    <div class="banner-image-wrapper" style="max-height: 140px; display: flex; align-items: center; justify-content: center; z-index: 2;">
        <img src="{{ asset('images/hero_food_banner.png') }}" alt="Sego Sambelan Banner" style="max-height: 160px; transform: scale(1.15) rotate(-3deg); transition: transform 0.3s ease;">
    </div>
    
    {{-- Aksen pancaran bercahaya --}}
    <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); z-index: 1; pointer-events: none;"></div>
</div>

<style>
    @media (max-width: 768px) {
        .admin-welcome-banner {
            flex-direction: column;
            text-align: center;
            padding: 2rem 1.5rem !important;
        }
        .admin-welcome-banner div {
            padding-right: 0 !important;
            margin-bottom: 1.5rem;
        }
        .admin-welcome-banner h1 {
            font-size: 1.8rem !important;
        }
        .banner-image-wrapper {
            max-height: 110px !important;
        }
        .banner-image-wrapper img {
            max-height: 120px !important;
            transform: scale(1.1) rotate(0deg) !important;
        }
    }
</style>

<!-- Ringkasan Statistik Pendapatan -->
<div class="stats-grid">
    <div class="stat-card card">
        <div class="stat-icon">🍽️</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_products'] }}</span>
            <span class="stat-label">Total Menu</span>
        </div>
    </div>
    <div class="stat-card card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['active_products'] }}</span>
            <span class="stat-label">Menu Aktif</span>
        </div>
    </div>
    <div class="stat-card card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['today_orders'] }}</span>
            <span class="stat-label">Pesanan Hari Ini</span>
        </div>
    </div>
    <div class="stat-card card" style="border-left: 4px solid var(--success);">
        <div class="stat-icon" style="background: rgba(34,197,94,0.15); color: var(--success);">💵</div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</span>
            <span class="stat-label">Pendapatan Hari Ini</span>
        </div>
    </div>
    <div class="stat-card card" style="border-left: 4px solid var(--info);">
        <div class="stat-icon" style="background: rgba(96,165,250,0.15); color: var(--info);">📅</div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($stats['weekly_revenue'], 0, ',', '.') }}</span>
            <span class="stat-label">Pendapatan Minggu Ini</span>
        </div>
    </div>
    <div class="stat-card card" style="border-left: 4px solid var(--primary);">
        <div class="stat-icon" style="background: var(--primary-light); color: var(--primary);">📈</div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($stats['monthly_revenue'], 0, ',', '.') }}</span>
            <span class="stat-label">Pendapatan Bulan Ini</span>
        </div>
    </div>
</div>

@if($stats['pending_orders'] > 0)
<div class="alert alert-warning">
    ⚠️ Ada <strong>{{ $stats['pending_orders'] }}</strong> pesanan yang menunggu diproses!
    <a href="/admin/orders?status=pending" class="link-primary">Lihat Sekarang →</a>
</div>
@endif

<!-- Ringkasan Arus Kas Masuk (Payment Breakdown) -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 class="card-title" style="display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #E5E7EB; padding-bottom: 0.5rem; margin-bottom: 1rem;">
        <span>🏦</span> Arus Uang Kas Masuk (Berdasarkan Metode)
    </h2>
    <div class="cashflow-summary-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div class="cashflow-item-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(232,184,75,0.1); border-radius: var(--radius-sm); padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div class="cashflow-icon" style="font-size: 2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: rgba(34,197,94,0.1); border-radius: 50%; color: var(--success);">💰</div>
            <div>
                <h4 style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; margin: 0;">Total Kas Masuk (Lunas)</h4>
                <p style="font-size: 1.4rem; font-weight: 700; color: var(--success); margin: 0; margin-top: 0.25rem;">Rp {{ number_format($stats['total_cash_inflow'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="cashflow-item-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(232,184,75,0.1); border-radius: var(--radius-sm); padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div class="cashflow-icon" style="font-size: 2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: rgba(96,165,250,0.1); border-radius: 50%; color: var(--info);">⚡</div>
            <div>
                <h4 style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; margin: 0;">Metode Otomatis (Midtrans)</h4>
                <p style="font-size: 1.4rem; font-weight: 700; color: var(--info); margin: 0; margin-top: 0.25rem;">Rp {{ number_format($stats['midtrans_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="cashflow-item-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(232,184,75,0.1); border-radius: var(--radius-sm); padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div class="cashflow-icon" style="font-size: 2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: rgba(232,184,75,0.1); border-radius: 50%; color: var(--primary);">🏦</div>
            <div>
                <h4 style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; margin: 0;">Transfer Manual (Konfirmasi)</h4>
                <p style="font-size: 1.4rem; font-weight: 700; color: var(--primary); margin: 0; margin-top: 0.25rem;">Rp {{ number_format($stats['manual_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-layout-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem; align-items: start;">
    <!-- Pesanan Terbaru -->
    <div class="card" style="margin: 0;">
        <h2 class="card-title" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; padding-bottom: 0.5rem; margin-bottom: 1rem;">
            <span>📦 Pesanan Terbaru</span>
            <a href="/admin/orders" style="font-size: 0.85rem; font-weight: 500;" class="link-primary">Semua →</a>
        </h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="/admin/orders/{{ $order->id }}" class="link-primary" style="font-weight: 600;">{{ $order->order_number }}</a></td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->formatted_total }}</td>
                        <td><span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span></td>
                        <td>{{ $order->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Arus Kas Uang Masuk (Detailed Payments Logs) -->
    <div class="card" style="margin: 0;">
        <h2 class="card-title" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; padding-bottom: 0.5rem; margin-bottom: 1rem;">
            <span>💸 Arus Kas Masuk (Transaksi Sukses)</span>
            <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); background: var(--bg-cream); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm);">Lunas</span>
        </h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>No. Order</th>
                        <th>Metode</th>
                        <th>Keterangan</th>
                        <th class="text-right" style="text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashInflows as $payment)
                    <tr>
                        <td style="font-size: 0.85rem; color: var(--text-primary);">
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m H:i') : $payment->updated_at->format('d/m H:i') }}
                        </td>
                        <td>
                            @if($payment->order)
                            <a href="/admin/orders/{{ $payment->order->id }}" class="link-primary" style="font-weight: 600;">{{ $payment->order->order_number }}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($payment->isManual())
                            <span class="badge badge-warning" style="background-color: rgba(232,184,75,0.15); color: var(--primary); text-transform: capitalize;">Manual</span>
                            @else
                            <span class="badge badge-info" style="background-color: rgba(96,165,250,0.15); color: var(--info); text-transform: capitalize;">Midtrans</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-secondary);">
                            @if($payment->isManual())
                                Pengirim: <strong>{{ $payment->sender_name ?? '-' }}</strong>
                            @else
                                ID: <span>{{ substr($payment->transaction_id ?? 'Auto-System', 0, 10) }}...</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; color: var(--success); font-size: 0.95rem;">
                            +Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada aliran kas masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    SmartRefresh.init({ pollUrl: '/admin/poll', interval: 10 });
</script>
@endpush
@endsection
