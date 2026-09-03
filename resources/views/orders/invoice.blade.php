<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }} - FOODCART360</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace, 'Tiro Bangla', sans-serif;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .receipt-card {
            background: #ffffff;
            width: 80mm;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
            color: #000000;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .cart-name {
            font-size: 13px;
            font-weight: 700;
            margin-top: 4px;
        }

        .meta-text {
            font-size: 11px;
            color: #333;
            margin-top: 2px;
        }

        .order-info {
            font-size: 11px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
        }

        .items-table td {
            padding: 4px 0;
        }

        .totals-section {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: 900;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #000;
        }

        .payment-info {
            font-size: 11px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #000;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #444;
        }

        .no-print-bar {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
        }

        .btn {
            background: #10b981;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .btn-back {
            background: #64748b;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none;
                border-radius: 0;
                width: 100% !important;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <button onclick="window.print()" class="btn">🖨️ Print Thermal Receipt (80mm)</button>
        <a href="{{ route('orders.index') }}" class="btn btn-back">Back to Orders</a>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div class="logo-title">FOODCART360</div>
            <div class="cart-name">{{ $cartName }}</div>
            <div class="meta-text">{{ $cartAddress }}</div>
            <div class="meta-text">Phone: {{ $cartPhone }}</div>
        </div>

        <div class="order-info">
            <div class="info-row">
                <span>Order No:</span>
                <strong>{{ $order->order_number }}</strong>
            </div>
            <div class="info-row">
                <span>Date & Time:</span>
                <span>{{ $order->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span>Customer:</span>
                <span>{{ $order->customer?->name ?? 'Guest Customer' }}</span>
            </div>
            @if($order->customer?->phone)
                <div class="info-row">
                    <span>Customer Phone:</span>
                    <span>{{ $order->customer->phone }}</span>
                </div>
            @endif
            @if($order->user)
                <div class="info-row">
                    <span>Cashier/Server:</span>
                    <span>{{ $order->user->name }}</span>
                </div>
            @endif
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th style="width: 15%; text-align: center;">Qty</th>
                    <th style="width: 35%; text-align: right;">Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->food_name }}</strong>
                            <div style="font-size: 10px; color: #555;">@ ৳{{ number_format($item->unit_price, 2) }}</div>
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>৳{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="total-row" style="color: #047857;">
                    <span>Discount:</span>
                    <span>- ৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            @if($order->tax_amount > 0)
                <div class="total-row">
                    <span>Tax/VAT:</span>
                    <span>+ ৳{{ number_format($order->tax_amount, 2) }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL AMOUNT:</span>
                <span>৳{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="payment-info">
            <div class="info-row">
                <span>Payment Mode:</span>
                <strong style="text-transform: uppercase;">{{ $order->payment_method }}</strong>
            </div>
            @if($order->latestPayment?->transaction_id)
                <div class="info-row">
                    <span>Txn ID / Ref:</span>
                    <span>{{ $order->latestPayment->transaction_id }}</span>
                </div>
            @endif
            <div class="info-row">
                <span>Payment Status:</span>
                <span>PAID</span>
            </div>
        </div>

        <div class="footer">
            <p>ধন্যবাদ! আবার আসবেন।</p>
            <p style="margin-top: 3px;">Thank you for dining with us!</p>
            <p style="font-size: 9px; margin-top: 8px; color: #777;">Powered by FOODCART360 - Smart Food Cart System</p>
        </div>
    </div>
</body>
</html>
