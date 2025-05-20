let activeInput = null;
let currentNfcCallback = null;

document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("focusin", function (event) {
        if (event.target.tagName === "INPUT" || event.target.tagName === "TEXTAREA") {
            activeInput = event.target;
        }
    });

    document.addEventListener("focusout", function () {
        activeInput = null;
    });
});

// Universal NFC text updater
function updateNfcText(nfcId) {
    let inputField = activeInput || Swal.getInput();
    
        if (inputField) {
            inputField.value = nfcId;
            inputField.focus();  // Ensure focus stays on the input field
        } else {
            alert("No input field selected!");
        }
}
