<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2 f2 f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
     <div class="header">
        <h2>{{ $title }}</h2>
        <p>Tanggal Laporan: {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
     </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Bidang / Bagian</th>
                <th>Jumlah Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record->barang->nama_barang }}</td>
                <td>{{ $record->bagian->nama_bagian ?? 'N/A' }}</td>
                <td>{{ $record->stok }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 