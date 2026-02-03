<div class="p-4 bg-gray-50 dark:bg-gray-800">
    <p class="font-bold mb-2">Daftar Barang:</p>
    <ul>
        @foreach($record->detailPermintaans as $detail)
            <li>{{ $detail->barang->nama_barang }} ({{ $detail->jumlah }})</li>
        @endforeach
    </ul>
</div>