<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Order — {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #1e293b; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0284c7; padding-bottom: 16px; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: 800; color: #0284c7; }
        .school-sub  { font-size: 11px; color: #64748b; margin-top: 2px; }
        .order-num   { text-align: right; }
        .order-num .num { font-size: 20px; font-weight: 800; font-family: monospace; color: #0284c7; }
        .section     { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 6px; }
        .info-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .info-item dt { font-size: 10px; color: #94a3b8; }
        .info-item dd { font-weight: 600; color: #1e293b; margin-top: 1px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
        tfoot td { background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0369a1; }
        .notes { background: #fafafa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-top: 4px; font-size: 11px; color: #475569; }
        .footer { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .sign-box { border-top: 1px solid #cbd5e1; padding-top: 8px; text-align: center; font-size: 11px; color: #64748b; }
        @media print {
            body { padding: 20px; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div>
            <div class="school-name">SMKN 9 Muaro Jambi</div>
            <div class="school-sub">Sistem Manajemen Produk — Unit Percetakan & Konveksi</div>
        </div>
        <div class="order-num">
            <div class="num">{{ $order->order_number }}</div>
            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $order->created_at->format('d F Y') }}</div>
            <div class="status-badge" style="margin-top:4px;">{{ strtoupper(str_replace('_',' ',$order->status)) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Informasi Pelanggan & Order</div>
        <div class="info-grid">
            <dl class="info-item">
                <dt>Nama Pelanggan</dt>
                <dd>{{ $order->customer->name }}</dd>
            </dl>
            <dl class="info-item">
                <dt>No. Telp / WA</dt>
                <dd>{{ $order->customer->phone ?? '—' }}</dd>
            </dl>
            <dl class="info-item">
                <dt>Deadline</dt>
                <dd>{{ $order->deadline ? $order->deadline->format('d F Y') : '—' }}</dd>
            </dl>
            <dl class="info-item">
                <dt>Status Pembayaran</dt>
                <dd>{{ ['unpaid' => 'Belum Bayar', 'dp' => 'DP / Uang Muka', 'paid' => 'Lunas'][$order->payment_status] ?? $order->payment_status }}</dd>
            </dl>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detail Item Pesanan</div>
        <table>
            <thead>
                <tr>
                    <th style="width:32px">No</th>
                    <th>Produk</th>
                    <th>Spesifikasi</th>
                    <th style="text-align:right">Qty</th>
                    <th style="text-align:right">Harga Satuan</th>
                    <th style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:600">{{ $item->product_name }}</td>
                    <td style="color:#64748b;font-size:11px">
                        @if($item->specifications)
                            {{ is_array($item->specifications) ? implode(', ', $item->specifications) : $item->specifications }}
                        @else —
                        @endif
                    </td>
                    <td style="text-align:right">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td style="text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-weight:600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right">TOTAL</td>
                    <td style="text-align:right;font-size:14px">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                @if($order->dp_amount > 0)
                <tr>
                    <td colspan="5" style="text-align:right;font-weight:400;color:#64748b">DP</td>
                    <td style="text-align:right;font-weight:400;color:#64748b">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right">SISA PEMBAYARAN</td>
                    <td style="text-align:right">Rp {{ number_format($order->total_price - $order->dp_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    @if($order->notes)
    <div class="section">
        <div class="section-title">Catatan</div>
        <div class="notes">{{ $order->notes }}</div>
    </div>
    @endif

    {{-- QR Code untuk tracking --}}
    <div class="section" style="text-align: center; border-top: 2px solid #f1f5f9; padding-top: 16px; margin: 24px 0;">
        <div class="section-title">Lacak Pesanan Anda</div>
        <p style="font-size: 11px; color: #64748b; margin-bottom: 8px;">Scan QR code di bawah untuk melihat status pengerjaan pesanan Anda</p>
        <img src="{{ $qrCodeDataUri }}" alt="QR Code Tracking" style="width: 120px; height: 120px; margin: 0 auto; display: block;">
        <p style="font-size: 10px; color: #94a3b8; margin-top: 6px;">{{ route('tracking.show', $order->order_number) }}</p>
    </div>

    <div class="footer">
        <div class="sign-box">
            Pemesan<br><br><br><br>
            ({{ $order->customer->name }})
        </div>
        <div class="sign-box">
            Petugas<br><br><br><br>
            ( ________________________ )
        </div>
    </div>

</body>
</html>
