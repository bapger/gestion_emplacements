function updateButtonState(buttonNumber) {
    const newState = prompt("Enter new state (disponible, Plein, Sigma, TDR):");
    const value = prompt("Enter value (optional):")
    fetch('php/update_button.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'button_number': buttonNumber,
            'new_state': newState,
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
