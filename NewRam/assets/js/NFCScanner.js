let activeInput = null;

      document.addEventListener("DOMContentLoaded", function () {
         // Track the currently focused input field
         document.addEventListener("focusin", function (event) {
            if (event.target.tagName === "INPUT" || event.target.tagName === "TEXTAREA") {
                  activeInput = event.target;
            }
         });

         document.addEventListener("focusout", function () {
            activeInput = null;
         });
      });

      function updateNfcText(nfcId) {
         if (Swal.isVisible()) {
             const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
             const toRoute = JSON.parse(document.getElementById('toRoute').value);
             const fareType = document.getElementById('fareType').value;
             const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);
     
             if (!fromRoute || !toRoute) {
                 Swal.fire('Error', 'Please select both starting point and destination.', 'error');
                 return;
             }
     
             Swal.close();  // Close the modal before proceeding
             getUserBalance(nfcId, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
         } else {
             alert("Please tap your card while the prompt is visible.");
         }
     }
     
    
     