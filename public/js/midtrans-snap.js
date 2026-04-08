/**
 * Midtrans Snap Payment Integration Snippet
 * 
 * Pastikan Anda memuat file script Midtrans di halaman HTML Anda HANYA di halaman pembayaran:
 * Sandbox: <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<YOUR_CLIENT_KEY>"></script>
 * Production: <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<YOUR_CLIENT_KEY>"></script>
 */

function payWithMidtrans(snapToken) {
    if (typeof snap === 'undefined') {
        console.error("Midtrans Snap.js script is not loaded!");
        alert("Sistem pembayaran belum siap. Silakan refresh halaman.");
        return;
    }

    // Panggil Midtrans Snap popup
    snap.pay(snapToken, {
        // Callback saat transaksi berhasil (berstatus 'paid' atau 'settlement')
        onSuccess: function(result) {
            console.log("Pembayaran Success:", result);
            // Contoh implementasi: Update status di front-end via AJAX / redirect
            Swal.fire({
                icon: 'success',
                title: 'Pembayaran Berhasil!',
                text: 'Terima kasih, pembayaran Anda telah kami terima.',
                confirmButtonText: 'Lanjutkan'
            }).then(() => {
                // Jangan panggil server POST/UPDATE di sini untuk status pembayaran.
                // Status final diproses di backend melalui Webhook (PembayaranService->handleCallback).
                // Di sini hanya cukup reload UI atau redirect sesuai workflow pendaftaran.
                window.location.reload(); 
            });
        },
        
        // Callback saat transaksi masih tertunda (seperti bank transfer yang belum dibayar)
        onPending: function(result) {
            console.log("Pembayaran Pending:", result);
            Swal.fire({
                icon: 'info',
                title: 'Menunggu Pembayaran',
                text: 'Silakan selesaikan pembayaran sesuai instruksi yang diberikan sebelum batas waktu habis.',
                confirmButtonText: 'Tutup'
            }).then(() => {
                window.location.reload();
            });
        },
        
        // Callback saat transaksi gagal (deny, cancel, dll)
        onError: function(result) {
            console.log("Pembayaran Error:", result);
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.',
                confirmButtonText: 'Tutup'
            });
        },
        
        // Callback saat user menutup popup tanpa menyelesaikan pembayaran
        onClose: function() {
            Swal.fire({
                icon: 'warning',
                title: 'Transaksi Dibatalkan',
                text: 'Anda menutup popup pembayaran. Pembayaran belum diselesaikan.',
                confirmButtonText: 'Mengerti'
            });
        }
    });
}
