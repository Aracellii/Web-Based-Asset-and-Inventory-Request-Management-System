<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: colollapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bagian-title {
            margin-top: 30px;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #e5e7eb;
            font-weight: bold;
            font-size: 13px;
        }

        .summary {
            margin-top: 10px;
            margin-bottom: 20px;
            padding: 8px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }

        .total-summary {
            margin-top: 30px;
            padding: 10px;
            background-color: #dbeafe;
            border: 1px solid #93c5fd;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Report Date: {{ $tanggal }}</p>
        @if(isset($periode))
            <p>Data Period: {{ $periode }}</p>
        @endif
    </div>

    @php
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalData = 0;
    @endphp

    @foreach ($groupedRecords as $namaBagian => $records)
        @php
            $bagianMasuk = $records->where('tipe', 'Masuk')->count();
            $bagianKeluar = $records->where('tipe', 'Keluar')->count();
            $totalMasuk += $bagianMasuk;
            $totalKeluar += $bagianKeluar;
            $totalData += count($records);
        @endphp

        <div class="bagian-title">
            {{ $namaBagian ?? 'Unassigned Unit' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Time</th>
                    <th>Item</th>
                    <th>Code</th>
                    <th style="width: 60px;">Type</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 50px;">Movement</th>
                    <th class="text-center" style="width: 60px;">Opening Stock</th>
                    <th class="text-center" style="width: 60px;">Closing Stock</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y, H:i') }}</td>
                        <td>{{ $record->nama_barang_snapshot }}</td>
                        <td>{{ $record->kode_barang_snapshot }}</td>
                        <td>{{ $record->tipe === 'Masuk' ? 'Inbound' : ($record->tipe === 'Keluar' ? 'Outbound' : $record->tipe) }}</td>
                        
                        <td>{{ $record->keterangan }}</td>
                        <td class="text-center">
                            {{ $record->stok_akhir < $record->stok_awal ? '-' : '+' }}{{ $record->jumlah }}
                        </td>
                        <td class="text-center">{{ $record->stok_awal }}</td>
                        <td class="text-center">{{ $record->stok_akhir }}</td>
                        <td>{{ $record->user_snapshot }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <strong>Subtotal {{ $namaBagian ?? 'Unassigned Unit' }}:</strong>
            Total: {{ count($records) }} |
            Inbound: {{ $bagianMasuk }} |
            Outbound: {{ $bagianKeluar }}
        </div>
    @endforeach

    <div class="total-summary">
        <strong>OVERALL SUMMARY:</strong><br>
        Total Data: {{ $totalData }} |
        Total Inbound Items: {{ $totalMasuk }} |
        Total Outbound Items: {{ $totalKeluar }}
    </div>

</body>
</html>
