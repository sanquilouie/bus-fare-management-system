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
         if (typeof window.updateNfcText === "function") {
             window.updateNfcText(nfcId);
         } else {
             console.warn("updateNfcText handler is not defined yet.");
         }
     }
     
     
    
     