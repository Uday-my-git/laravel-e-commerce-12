<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $orders->id }}</title>
    <style>
        @page { 
            margin: 20px 25px;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fdfbfb;
            line-height: 1.4;
        }

        .invoice-container {
            padding: 0;
            max-width: 100%;
        }

        header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        header h2 {
            margin: 0 0 5px 0;
            color: #007bff;
            font-size: 26px;
            font-weight: bold;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.5;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .col:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        address {
            font-style: normal;
            line-height: 1.5;
        }

        .info-line {
            margin: 3px 0;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: #fff;
            font-size: 10px;
            display: inline-block;
        }

        .bg-danger { background-color: #dc3545; }
        .bg-success { background-color: #28a745; }
        .bg-info { background-color: #17a2b8; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 10px 0;
            font-size: 12px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 12px;
        }

        table td {
            vertical-align: top;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .summary-table {
            width: 100%;
            margin-top: 10px;
            border: none;
        }

        .summary-table td {
            border: none;
            padding: 4px 8px;
            font-size: 12px;
        }

        .summary-table tr td:first-child {
            text-align: right;
            width: 70%;
            font-weight: 500;
        }

        .summary-table tr td:last-child {
            text-align: right;
            width: 30%;
        }

        .summary-table tr.grand-total td {
            border-top: 2px solid #333;
            padding-top: 6px;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        footer {
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            margin-top: 12px;
            padding-top: 8px;
            color: #777;
        }

        p, h3 {
            margin: 0;
            padding: 0;
        }

        strong, b {
            font-weight: 600;
        }

        .small-text {
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <header>
            <div class="header-left">
                <h2>INVOICE</h2>
                <p class="small-text">Invoice #{{ $orders->id }} | Order #{{ $orders->id }}</p>
                <p class="small-text">Generated: {{ now()->format('d M, Y h:i A') }}</p>
            </div>
            <div class="header-right company-info">
                <strong style="font-size: 13px;">Your Company Name</strong><br>
                123 Business Street<br>
                City, State, ZIP<br>
                Email: info@company.com<br>
                Phone: +91 9876543210
            </div>
        </header>

        <div class="row">
            <div class="col">
                <h3 class="section-title">Shipping Address</h3>
                <address>
                    <strong>{{ $orders->first_name }} {{ $orders->last_name }}</strong><br>
                    {{ $orders->address }}<br>
                    {{ $orders->city }}, {{ $orders->state }}, {{ $orders->countriesName }}<br>
                    Zip: {{ $orders->zip }}<br>
                    Phone: {{ $orders->mobile }}<br>
                    Email: {{ $orders->email }}
                </address>
            </div>

            <div class="col">
                <h3 class="section-title">Order Details</h3>
                <div class="info-line"><b>Total Amount:</b> R.s {{ number_format($orders->grand_total, 2) }}</div>
                <div class="info-line"><b>Status:</b>
                    @if ($orders->status == 'pending')
                        <span class="badge bg-danger">Pending</span>
                    @elseif ($orders->status == 'shipped')
                        <span class="badge bg-info">Shipped</span>
                    @elseif ($orders->status == 'deliverd')
                        <span class="badge bg-success">Delivered</span>
                    @else
                        <span class="badge bg-danger">Cancelled</span>
                    @endif
                </div>
                <div class="info-line"><b>Shipped Date:</b>
                    @if (!empty($orders->shipped_date))
                        {{ \Carbon\Carbon::parse($orders->shipped_date)->format('d M, Y') }}
                    @else
                        n/a
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th style="width: 17%;" class="text-right">Price</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 23%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orderItems as $orderItem)
                    <tr>
                        <td>{{ $orderItem->name }}</td>
                        <td class="text-right">R.s {{ number_format($orderItem->price, 2) }}</td>
                        <td class="text-center">{{ $orderItem->qty }}</td>
                        <td class="text-right">R.s {{ number_format($orderItem->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No order items found</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td>R.s {{ number_format($orders->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td>R.s {{ number_format($orders->shipping, 2) }}</td>
            </tr>
            <tr>
                <td>Discount{{ (!empty($orders->coupon_code)) ? ' ('.$orders->coupon_code.')' : '' }}:</td>
                <td>R.s {{ number_format($orders->discount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Grand Total:</td>
                <td>R.s {{ number_format($orders->grand_total, 2) }}</td>
            </tr>
        </table>

        <footer>
            <p>Thank you for your purchase!</p>
            <p>This invoice was generated automatically and is valid without a signature.</p>
        </footer>
    </div>
</body>
</html>