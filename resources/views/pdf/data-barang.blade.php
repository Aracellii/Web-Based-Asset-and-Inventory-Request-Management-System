<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .total-stok {
            font-weight: bold;
            background-color: #e8f5e9;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>
            Tanggal Pembuatan Laporan :
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Total Stok (Semua Bidang)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->kode_barang }}</td>
                    <td>{{ $record->nama_barang }}</td>
                    <td class="total-stok">{{ $record->total_stok }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <p><strong>Total Jenis Barang:</strong> {{ $records->count() }} item</p>
        <p><strong>Total Semua Stok  :</strong> {{ $records->sum('total_stok') }} unit</p>
    </div>

</body>
</html>
