<div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mr-4">
    <div class="flex flex-col leading-tight items-end">
        <span id="realtime-date" class="text-[11px] font-medium opacity-80"></span>
        
        <span id="realtime-time" class="font-bold tabular-nums font-mono  "></span>
    </div>
</div>

<script>
    // 1. Bersihkan interval lama jika ada (mencegah memory leak/lemot)
    if (window.clockInterval) {
        clearInterval(window.clockInterval);
    }

    function updateClock() {
        const now = new Date();

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const formattedDate = `${dayName}, ${day} ${month} ${year}`;
        const formattedTime = `${hours}:${minutes}:${seconds} WIB`;

        const dateEl = document.getElementById('realtime-date');
        const timeEl = document.getElementById('realtime-time');

        // Jika elemen ada di layar, update isinya
        if (dateEl && timeEl) {
            dateEl.textContent = formattedDate;
            timeEl.textContent = formattedTime;
        } else {
            // Jika elemen hilang (pindah halaman tanpa reload), stop interval ini
            clearInterval(window.clockInterval);
        }
    }

    // 2. Jalankan langsung & set interval baru
    updateClock();
    window.clockInterval = setInterval(updateClock, 1000);
</script>