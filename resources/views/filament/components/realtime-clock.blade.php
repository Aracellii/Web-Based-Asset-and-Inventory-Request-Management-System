<div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mr-4">
    <div class="flex flex-col leading-tight items-end">
        <span id="realtime-date" class="text-[11px] font-medium opacity-80"></span>
        
        <span id="realtime-time" class="font-bold tabular-nums font-mono  "></span>
    </div>
</div>

<script>
    // Clear any previous interval to avoid duplicates
    if (window.clockInterval) {
        clearInterval(window.clockInterval);
    }

    function updateClock() {
        const now = new Date();

        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const formattedDate = `${dayName}, ${day} ${month} ${year}`;
        const formattedTime = `${hours}:${minutes}:${seconds} UTC+7`;

        const dateEl = document.getElementById('realtime-date');
        const timeEl = document.getElementById('realtime-time');

        // Update the clock only when the elements exist on the page
        if (dateEl && timeEl) {
            dateEl.textContent = formattedDate;
            timeEl.textContent = formattedTime;
        } else {
            // Stop the interval if the elements are no longer present
            clearInterval(window.clockInterval);
        }
    }

    // Start immediately and then update every second
    updateClock();
    window.clockInterval = setInterval(updateClock, 1000);
</script>