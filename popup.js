<script>
document.addEventListener('DOMContentLoaded', function () {
    var openPopupButton = document.getElementById('openMpesaPhonePopup');
    
    openPopupButton.addEventListener('click', function () {
        var overlay = document.createElement('div');
        overlay.className = 'popup-overlay';
        
        var popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = '<h2>Enter Your Mpesa Phone Number:</h2>' +
            '<input type="text" id="mpesaPhoneNumber" placeholder="Mpesa Phone Number">' +
            '<button id="submitMpesaPhoneNumber">Submit</button>';
        
        overlay.appendChild(popup);
        document.body.appendChild(overlay);
        
        // Add event listener to handle form submission.
        var submitButton = document.getElementById('submitMpesaPhoneNumber');
        submitButton.addEventListener('click', function () {
            var mpesaPhoneNumber = document.getElementById('mpesaPhoneNumber').value;
            
            // Send the phone number to the server or update it as needed.
            // You can use AJAX to send the data to the server.
            
            // Close the popup when done.
            document.body.removeChild(overlay);
        });
    });
});
</script>