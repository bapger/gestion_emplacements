document.addEventListener("DOMContentLoaded", function() {
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    scanner.addListener('scan', function (content) {
        document.getElementById('result').innerText = content;
        // Envoyer les données scannées à un script PHP
        fetch('store_qr.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ qrData: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('QR Code enregistré avec succès');
            } else {
                alert('Erreur lors de l\'enregistrement du QR Code');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });
});
