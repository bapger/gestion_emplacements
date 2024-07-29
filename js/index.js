function openModal(buttonNumber) {
    $('#buttonNumberInput').val(buttonNumber);
    $('#buttonModal').modal('show');
}

function updateButtonState(buttonNumber,id) {
    switch(id) {
        case "TDR":
            $('#buttonModal').modal('show');
            break;
        case "Sigma":
            $('#buttonModal').modal('show');
            break;
        case "Plein":
            $('#buttonModal').modal('show');
            break;
        default:
            $('#buttonModal').modal('show');
      } 

    const date = Date.now();
    fetch('php/update_button.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'button_number': buttonNumber,
            'date': date,
            'value': value
        })
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function domReady(fn) {
    if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
    ) {
        setTimeout(fn, 1000);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

domReady(function () {

    // If found you qr code
    function onScanSuccess(decodeText, decodeResult) {
        alert("QR Code Scanné :" + decodeText, decodeResult);
    }

    let htmlscanner = new Html5QrcodeScanner(
        "my-qr-reader",
        { fps: 10, qrbos: 250 }
    );
    htmlscanner.render(onScanSuccess);
});
