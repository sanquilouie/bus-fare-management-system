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
    if (typeof currentNfcCallback === "function") {
        currentNfcCallback(nfcId);
    } else {
        console.warn("No NFC callback registered.");
    }
}
