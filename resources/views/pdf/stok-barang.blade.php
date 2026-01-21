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

        .bagian-title {
            margin-top: 30px;
            font-weight: bold;
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

    @foreach ($groupedRecords as $namaBagian => $records)

        <table>
         <caption><strong> <div class="bagian-title">
             {{ $namaBagian }}
        </div></strong></caption>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $record->barang->kode_barang }}</td>
                        <td>{{ $record->barang->nama_barang }}</td>
                        <td>{{ $record->stok }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

</body>
</html>
