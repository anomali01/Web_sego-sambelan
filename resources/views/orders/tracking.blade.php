@extends('layouts.app')
@section('title', 'Tracking Pesanan - Sego Sambelan')

@section('content')
<div class="container py-2">
    <div class="tracking-page">
        {{-- Order Status Header --}}
        @if($order->status === 'canceled')
        <div class="tracking-status-card glass-card canceled">
            <span class="status-emoji">❌</span>
            <h1>Pesanan Dibatalkan</h1>
            <p>Order {{ $order->order_number }}</p>
        </div>
        @else
        <div class="tracking-status-card glass-card">
            @php
                $messages = [
                    0 => ['icon' => '⏳', 'text' => 'Menunggu pembayaran...'],
                    1 => ['icon' => '💳', 'text' => 'Pembayaran diterima!'],
                    2 => ['icon' => '👨‍🍳', 'text' => 'Sego Sambelan sedang disiapkan! 🔥'],
                    3 => $order->isDelivery() ? ['icon' => '🚚', 'text' => 'Pesanan sedang dalam perjalanan!'] : ['icon' => '🍽️', 'text' => 'Pesanan siap diambil!'],
                ];
                if ($order->payment?->isManual() && $order->payment->isPaid() && $order->status === 'pending') {
                    $messages[1] = ['icon' => '✅', 'text' => 'Pembayaran dikonfirmasi — menunggu mulai dimasak'];
                }
                if ($order->payment?->isManual() && $order->payment->isPending() && $order->payment->hasProof()) {
                    $messages[0] = ['icon' => '📤', 'text' => 'Bukti terkirim — menunggu konfirmasi penjual'];
                }
                $lastIdx = count($statusSteps) - 1;
                $msg = $currentStepIndex === $lastIdx
                    ? ['icon' => '✅', 'text' => 'Pesanan selesai! Selamat menikmati! 🎉']
                    : ($messages[$currentStepIndex] ?? ['icon' => '📋', 'text' => 'Memproses pesanan...']);
            @endphp
            <span class="status-emoji">{{ $msg['icon'] }}</span>
            <h1>{{ $msg['text'] }}</h1>
            <p>Order <strong>{{ $order->order_number }}</strong></p>
        </div>

        {{-- Status Timeline --}}
        <div class="timeline glass-card">
            @foreach($statusSteps as $i => $step)
            <div class="timeline-step {{ $i <= $currentStepIndex ? 'completed' : '' }} {{ $i === $currentStepIndex ? 'current' : '' }}">
                <div class="timeline-dot">{{ $step['icon'] }}</div>
                <span class="timeline-label">{{ $step['label'] }}</span>
            </div>
            @if(!$loop->last)
            <div class="timeline-line {{ $i < $currentStepIndex ? 'completed' : '' }}"></div>
            @endif
            @endforeach
        </div>
        @endif

        {{-- Pay Now (if pending payment) --}}
        @if($order->status === 'pending' && $order->payment && $order->payment->isPending())
        <div class="glass-card" style="text-align:center; padding:1.5rem;">
            @if($order->payment->isManual())
            <p style="margin-bottom:1rem;">Transfer ke rekening warung lalu upload bukti. Penjual akan verifikasi pembayaran, lalu mulai menyiapkan pesanan.</p>
            <a href="/checkout/manual/{{ $order->id }}" class="btn btn-primary btn-lg">🏦 Lihat Rekening & Upload Bukti</a>
            @elseif($order->payment->snap_token)
            <a href="/checkout/payment/{{ $order->id }}" class="btn btn-primary btn-lg">💳 Bayar Sekarang</a>
            @endif
        </div>
        @elseif($order->payment?->isManual() && $order->payment->isPending() && $order->payment->hasProof())
        <div class="glass-card alert alert-info" style="text-align:center;">
            Bukti transfer sudah dikirim. Menunggu penjual memverifikasi pembayaran.
        </div>
        @elseif($order->payment?->isManual() && $order->payment->isPaid() && $order->status === 'pending')
        <div class="glass-card alert alert-success" style="text-align:center;">
            Pembayaran sudah dikonfirmasi penjual. Pesanan akan segera mulai disiapkan.
        </div>
        @endif

        {{-- Order Details --}}
        <div class="glass-card tracking-details">
            <h2>Detail Pesanan</h2>
            <div class="detail-row">
                <span>Tipe</span>
                <span class="badge {{ $order->isDelivery() ? 'badge-info' : 'badge-warning' }}">
                    {{ $order->isDelivery() ? '🚚 Delivery' : '🍽️ Dine-In (Meja ' . $order->table_number . ')' }}
                </span>
            </div>
            @if($order->delivery_address)
            <div class="detail-row">
                <span>Alamat</span>
                <span>{{ $order->delivery_address }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span>Waktu Pesan</span>
                <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>

            <hr>
            @foreach($order->orderItems as $item)
            <div class="checkout-item">
                <span class="checkout-item-qty">{{ $item->quantity }}x</span>
                <span class="checkout-item-name">{{ $item->product->name ?? 'Menu' }}</span>
                <span class="checkout-item-price">{{ $item->formatted_subtotal }}</span>
            </div>
            @endforeach
            <hr>
            <div class="summary-row total">
                <span>Total</span>
                <span>{{ $order->formatted_total }}</span>
            </div>
        </div>

        <!-- Hubungi Admin Info Box -->
        <div class="glass-card" style="margin-top: 1.5rem; border-left: 4px solid #25D366; padding: 1rem; display: flex; align-items: center; gap: 1rem; background: rgba(37,211,102,0.05); text-align: left;">
            <div style="font-size: 2rem;">💬</div>
            <div style="flex-grow: 1;">
                <h4 style="color: #25D366; margin: 0; font-size: 0.95rem; font-weight: 700;">Butuh Bantuan atau Ingin Kirim Foto?</h4>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                    Hubungi admin kami langsung melalui WhatsApp untuk bantuan cepat, perubahan pesanan, atau melampirkan foto bukti.
                </p>
            </div>
        </div>

        <div class="tracking-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
            <a href="/menu" class="btn btn-primary">Pesan Lagi 🍛</a>
            <a href="https://wa.me/{{ env('STORE_WHATSAPP', '628123456789') }}?text=Halo%20Admin%20Sego%20Sambelan%2C%20saya%20ingin%20bertanya%20mengenai%20pesanan%20saya%20dengan%20nomor%20order%20*{{ urlencode($order->order_number) }}*." target="_blank" class="btn" style="background: #25D366; color: white; display: inline-flex; align-items: center; gap: 0.5rem; border: none; font-weight: bold; box-shadow: 0 4px 15px rgba(37,211,102,0.3);">
                💬 Tanya via WhatsApp
            </a>
            <a href="/orders/history" class="btn btn-outline">Riwayat Pesanan</a>
        </div>
    </div>
</div>

{{-- Auto-refresh and Live Notifications --}}
@push('scripts')
<script>
    const orderId = "{{ $order->id }}";
    const currentStatus = "{{ $order->status }}";
    const currentPaymentStatus = "{{ $order->payment?->status ?? 'none' }}";

    function playNotificationChime() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const notes = [523.25, 659.25, 783.99]; // C5, E5, G5 (Ascending Premium Chime)
            notes.forEach((freq, idx) => {
                setTimeout(() => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.45);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.45);
                }, idx * 150);
            });
        } catch (e) {
            console.warn("Audio Context blocked by browser autoplay policy or unsupported.", e);
        }
    }

    function showOrderToast(message) {
        // Create toast dynamically
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.borderLeft = '4px solid var(--primary)';
        toast.innerHTML = `
            <div style="font-weight: 800; color: var(--primary); margin-bottom: 4px; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
                <span>🔔</span> Update Pesanan
            </div>
            <div style="font-size: 0.88rem; color: #F5E6C8; line-height: 1.4;">${message}</div>
        `;
        document.body.appendChild(toast);
        
        // Animate Slide In
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Auto Remove after 7 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 7000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const storageKey = 'order_status_' + orderId;
        const storedStateRaw = localStorage.getItem(storageKey);
        
        const currentState = {
            status: currentStatus,
            payment: currentPaymentStatus
        };
        
        if (storedStateRaw) {
            const storedState = JSON.parse(storedStateRaw);
            
            // Check if status or payment status changed
            if (storedState.status !== currentState.status || storedState.payment !== currentState.payment) {
                playNotificationChime();
                
                let msg = 'Status pesanan Anda telah diperbarui!';
                if (currentState.status === 'processing') {
                    msg = 'Pesanan Anda sedang disiapkan di dapur! 🔥 Harap ditunggu.';
                } else if (currentState.status === 'delivering') {
                    msg = 'Pesanan Anda sedang dalam perjalanan ke lokasi Anda! 🚚';
                } else if (currentState.status === 'delivered') {
                    msg = 'Pesanan Anda sudah sampai! Menunggu verifikasi admin. 📦';
                } else if (currentState.status === 'shipping') {
                    msg = 'Pesanan Anda sedang dalam perjalanan ke lokasi Anda! 🚚';
                } else if (currentState.status === 'completed') {
                    msg = 'Pesanan selesai! Selamat menikmati hidangan lezat Sego Sambelan Anda! 🎉🍽️';
                } else if (currentState.status === 'canceled') {
                    msg = 'Pesanan Anda telah dibatalkan oleh penjual. ❌';
                } else if (currentState.payment === 'paid' && storedState.payment !== 'paid') {
                    msg = 'Pembayaran berhasil dikonfirmasi! Pesanan segera masuk antrean dapur. 💳';
                }
                
                showOrderToast(msg);
            }
        }
        
        // Save state for future comparison
        localStorage.setItem(storageKey, JSON.stringify(currentState));
    });

    // Auto reload status every 15 seconds if active
    @if(!in_array($order->status, ['completed', 'canceled']))
        setTimeout(() => location.reload(), 15000);
    @endif
</script>
@endpush
@endsection
