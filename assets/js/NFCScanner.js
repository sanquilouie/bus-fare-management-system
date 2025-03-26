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
        let inputField = activeInput || Swal.getInput();
    
        if (inputField) {
            inputField.value = nfcId;
            inputField.focus();  // Ensure focus stays on the input field
        } else {
            alert("No input field selected!");
        }
    }
    
     