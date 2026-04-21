<script>
    /**
     * Download Kartu Peserta via Iframe
     */
    function downloadKartu(url, btnElement) {
        if (!btnElement || btnElement.getAttribute('disabled')) return;

        // Visual State: Loading
        btnElement.setAttribute('disabled', 'true');
        const spinner = btnElement.querySelector('.spinner-border');
        const icon = btnElement.querySelector('i');
        const textSpan = btnElement.querySelector('.btn-text');

        if (spinner) spinner.classList.remove('d-none');
        if (icon) icon.classList.add('d-none');

        // Processing Download
        const ifr = document.createElement('iframe');
        ifr.style.display = 'none';
        ifr.src = url;
        document.body.appendChild(ifr);

        // Reset state after estimated download initiation
        setTimeout(() => {
            btnElement.removeAttribute('disabled');
            if (spinner) spinner.classList.add('d-none');
            if (icon) icon.classList.remove('d-none');
        }, 4000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial setup
        console.log('Pengumuman Page System Initialized');
    });
</script>
