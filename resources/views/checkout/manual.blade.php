@extends('layouts.app')
@section('title', 'Transfer Manual - Sego Sambelan')

@section('content')
<div class="container py-2">
    <div class="payment-page">
        <div class="payment-card glass-card">
            <div class="payment-header">
                <img src="{{ asset('images/icons/icon_bank.svg') }}" alt="Bank" style="height: 48px; width: 48px; object-fit: contain; margin-bottom: 0.5rem;">
                <h1>Transfer ke Rekening Warung</h1>
                <p>Order: <strong>{{ $order->order_number }}</strong></p>
            </div>

            <div class="manual-bank-box" style="background:var(--surface-2); padding:1.25rem; border-radius:12px; margin-bottom:1rem;">
                <p style="margin:0 0 .5rem; font-size:.9rem; color:var(--text-muted);">Transfer tepat sebesar:</p>
                <p style="margin:0 0 1rem; font-size:1.5rem; font-weight:800; color:var(--primary);">{{ $order->formatted_total }}</p>
                <div class="detail-list">
                    <div class="detail-row"><span>Bank</span><strong>{{ $settings->bank_name }}</strong></div>
                    <div class="detail-row"><span>No. Rekening</span><strong id="account-number">{{ $settings->account_number }}</strong></div>
                    <div class="detail-row"><span>Atas Nama</span><strong>{{ $settings->account_name }}</strong></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="margin-top:.75rem;" onclick="copyAccount()">Salin No. Rekening</button>
            </div>

            @if($settings->qris_url)
            <div style="text-align:center; margin-bottom:1.25rem;">
                <p style="font-weight:600; margin-bottom:.75rem;">Atau scan QRIS warung</p>
                <img src="{{ $settings->qris_url }}" alt="QRIS Sego Sambelan" style="max-width:220px; border-radius:12px; border:2px solid var(--border);">
            </div>
            @endif

            @if($settings->instructions)
            <div class="alert alert-info" style="margin-bottom:1rem;">
                <strong>Instruksi:</strong> {{ $settings->instructions }}
            </div>
            @endif

            <hr>

            <h2 style="font-size:1.1rem; margin-bottom:1rem;">Upload Bukti Transfer</h2>
            <form method="POST" action="/checkout/manual/{{ $order->id }}/proof" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="sender_name">Nama Pengirim (sesuai rekening/e-wallet)</label>
                    <input type="text" id="sender_name" name="sender_name" value="{{ old('sender_name', $order->payment->sender_name) }}" required class="form-input @error('sender_name') input-error @enderror">
                    @error('sender_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="proof">Foto bukti transfer</label>
                    <input type="file" id="proof" name="proof" accept="image/*" required class="form-input @error('proof') input-error @enderror">
                    @error('proof')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-full btn-lg">
                    {{ $order->payment->hasProof() ? 'Perbarui Bukti Transfer' : 'Kirim Bukti Transfer' }}
                </button>
            </form>

            <a href="/orders/{{ $order->id }}/tracking" class="btn btn-outline btn-full" style="margin-top:.75rem;">Lihat Status Pesanan</a>

            <form method="POST" action="/checkout/{{ $order->id }}/cancel" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?\nItem akan dikembalikan ke keranjang Anda.')" style="margin-top: 0.5rem;">
                @csrf
                <button type="submit" class="btn btn-full" style="background: none; color: #EF4444; border: 1px solid #FCA5A5; font-weight: 600;">
                    ✕ Batalkan Pesanan & Kembali ke Keranjang
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyAccount() {
    const text = document.getElementById('account-number').textContent;
    navigator.clipboard.writeText(text).then(() => alert('No. rekening disalin: ' + text));
}
</script>
@endpush
@endsection
