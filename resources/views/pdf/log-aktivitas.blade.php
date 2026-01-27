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
            border-collapse: collapse;
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
        <p>Tanggal Laporan: {{ $tanggal }}</p>
        @if(isset($periode))
            <p>Periode Data: {{ $periode }}</p>
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
            {{ $namaBagian ?? 'Tanpa Bagian' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Waktu</th>
                    <th>Barang</th>
                    <th>Kode</th>
                    <th style="width: 60px;">Tipe</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width: 50px;">Mutasi</th>
                    <th class="text-center" style="width: 60px;">Stok Awal</th>
                    <th class="text-center" style="width: 60px;">Stok Akhir</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y, H:i') }}</td>
                        <td>{{ $record->nama_barang_snapshot }}</td>
                        <td>{{ $record->kode_barang_snapshot }}</td>
                        <td>{{ $record->tipe }}</td>
                        
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
            <strong>Subtotal {{ $namaBagian ?? 'Tanpa Bagian' }}:</strong>
            Total: {{ count($records) }} |
            Masuk: {{ $bagianMasuk }} |
            Keluar: {{ $bagianKeluar }}
        </div>
    @endforeach

    <div class="total-summary">
        <strong>RINGKASAN KESELURUHAN:</strong><br>
        Total Data: {{ $totalData }} |
        Total Barang Masuk: {{ $totalMasuk }} |
        Total Barang Keluar: {{ $totalKeluar }}
    </div>

</body>
</html>
