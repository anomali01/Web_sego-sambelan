<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorePaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        $settings = StorePaymentSetting::current();

        return view('admin.payment-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = StorePaymentSetting::current();

        $validated = $request->validate([
            'manual_enabled' => ['nullable', 'boolean'],
            'bank_name' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:30'],
            'account_name' => ['required', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'store_address' => ['required', 'string', 'max:500'],
            'qris_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'manual_enabled' => $request->boolean('manual_enabled'),
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'instructions' => $validated['instructions'] ?? null,
            'store_address' => $validated['store_address'],
        ];

        if ($request->hasFile('qris_image')) {
            if ($settings->qris_image) {
                Storage::disk('public')->delete($settings->qris_image);
            }
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        }

        $settings->update($data);

        return back()->with('success', 'Pengaturan pembayaran manual berhasil disimpan.');
    }
}
