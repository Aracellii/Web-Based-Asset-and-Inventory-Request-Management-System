<div class="p-4">
    <h3 class="text-lg font-bold mb-4">Detail Stok Per Bidang - {{ $barang->nama_barang }}</h3>
    
    <div class="mb-4">
        <span class="text-gray-600">Kode Barang:</span>
        <span class="font-semibold">{{ $barang->kode_barang }}</span>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Bidang / Bagian</th>
                <th class="px-4 py-2 text-right">Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stokPerBidang as $stok)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $stok->bagian->nama_bagian ?? '-' }}</td>
                    <td class="px-4 py-2 text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($stok->stok <= 5) bg-red-100 text-red-800
                            @elseif($stok->stok <= 20) bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ $stok->stok }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 font-bold">
                <td class="px-4 py-2">Total Stok</td>
                <td class="px-4 py-2 text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $stokPerBidang->sum('stok') }}
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
