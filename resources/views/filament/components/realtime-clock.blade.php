<div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mr-4">
    <div class="flex flex-col leading-tight">
        <span id="realtime-date" class="text-sm"></span>
        <span id="realtime-time" class="font-medium tabular-nums"></span>
    </div>
</div>

<script>
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

        if (dateEl) dateEl.textContent = formattedDate;
        if (timeEl) timeEl.textContent = formattedTime;
    }

    updateClock();
    setInterval(updateClock, 1000);
</script>