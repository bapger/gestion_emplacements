function openModal(buttonNumber) {
    $('#buttonNumberInput').val(buttonNumber);
    $('#buttonModal').modal('show');
}

sessionStorage.setItem('decodeText',"None")

function updateButtonState(buttonNumber,id) {
    sessionStorage.setItem("currentButton",buttonNumber)
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
}
    
function saveValues(){
    const date = Date.now();
    var value = sessionStorage.getItem('decodeText')
    var button_number=sessionStorage.getItem("currentButton")
    if (value=="None"){
        window.onerror = function(msg, url, linenumber) {
            alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber);
            return true;
        }
    }
    fetch('php/update_button.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'button_number': button_number,
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
    function onScanSuccess(decodeText, decodeResult) {
        alert("QR Code Scanné :" + decodeText);
        sessionStorage.setItem('decodeText', decodeText);
        document.getElementById("num").innerHTML = "Camion :"+decodeText;
    }

    let htmlscanner = new Html5QrcodeScanner(
        "my-qr-reader",
        { fps: 10, qrbos: 250 }
    );
    htmlscanner.render(onScanSuccess);
});

