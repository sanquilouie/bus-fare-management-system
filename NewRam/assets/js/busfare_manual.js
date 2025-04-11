export function updateDistance_manual() {
    const fromRouteValue = document.getElementById('fromRoute').value;
    const toRouteValue = document.getElementById('toRoute').value;
    const kmLabel = document.getElementById('kmLabel');
    const fareLabel = document.getElementById('fareLabel');
    const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10); // Get passenger quantity
    console.log("From Route: ", fromRouteValue);
    console.log("To Route: ", toRouteValue);
    if (fromRouteValue && toRouteValue) {
        try {
            const fromRoute = JSON.parse(fromRouteValue);
            const toRoute = JSON.parse(toRouteValue);

            // Calculate the distance in kilometers
            const distance = Math.abs(fromRoute.post - toRoute.post);
            kmLabel.textContent = `${distance} km`;

            // Calculate the fare based on the distance
            let totalFare = baseFare; // Start with the base fare for the first 4 km
            if (distance > 4) {
                // Add additional fare for kilometers beyond the first 4 km
                totalFare += (distance - 4) * additionalFare;
            }

            // Apply discount if applicable
            const fareType = document.getElementById('fareType').value;
            if (fareType === 'discounted') {
                totalFare *= 0.8; // Apply 20% discount
            }

            // Calculate total fare with passenger quantity
            totalFare *= passengerQuantity; // Multiply by the number of passengers

            fareLabel.textContent = `₱${totalFare.toFixed(2)}`;
        } catch (error) {
            console.error('Error parsing route data:', error);
            kmLabel.textContent = "Invalid route data";
            fareLabel.textContent = "₱0.00";
        }
    } else {
        kmLabel.textContent = "0 km";
        fareLabel.textContent = "₱0.00";
    }
}

export function validateRoutes_manual() {
    const fromRoute = document.getElementById('fromRoute').value;
    const toRoute = document.getElementById('toRoute').value;

    if (!fromRoute || !toRoute) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Selection',
            text: 'Please select both a starting point and a destination.',
        });
        return false;
    }
    return true;
}

export function promptRFIDInput_manual() {
    const fromRouteValue = document.getElementById('fromRoute').value;
    const toRouteValue = document.getElementById('toRoute').value;
    const distance = Math.abs(fromRoute.post - toRoute.post);
    const transactionNumber = generateTransactionNumber();
    const paymentMethod = 'RFID';

    console.log("Generated Transaction Number:", transactionNumber); // Debugging line
    console.log("Distance:", distance); // Debugging line
    console.log("Payment Method:", paymentMethod);

    if (!validateRoutes()) {
        // Stop execution if routes are not selected
        return;
    }

    Swal.fire({
        title: 'Enter RFID',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: 'Cancel',
        inputPlaceholder: 'Scan your RFID here',
        didOpen: () => {
            const inputField = Swal.getInput();
            if (inputField) {
                activeInput = inputField;  // Track the Swal input
                inputField.focus();
            inputField.addEventListener('keydown', async (event) => {
                // Check if the Enter key is pressed
                if (event.key === 'Enter') {
                    const rfid = inputField.value.trim();
                    if (rfid) {
                        // If RFID is entered, automatically process the fare
                        const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
                        const toRoute = JSON.parse(document.getElementById('toRoute').value);
                        const fareType = document.getElementById('fareType').value;
                        const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);

                        if (!fromRoute || !toRoute) {
                            Swal.fire('Error', 'Please select both starting point and destination.', 'error');
                            return;
                        }

                        console.log("Transaction Number before calling getUser Balance:", transactionNumber); // Debugging line

                        // Call the export function to get user balance and process the fare
                        getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
                    }
                }
            });
        }
        }
    });
}

// export function to get user balance based on RFID (account_number)
export function processPayment_manual(paymentType) {
    if (!validateRoutes()) {
        return;
    }
    if (paymentType === 'cash') {
        Swal.fire({
            title: 'Confirm Cash Payment',
            text: 'Are you sure you want to pay in cash?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const rfid = '';
                const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
                const toRoute = JSON.parse(document.getElementById('toRoute').value);
                const fareType = document.getElementById('fareType').value;
                const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);
                const paymentMethod = 'Cash';

                // Generate transaction number
                const transactionNumber = generateTransactionNumber();
                const distance = Math.abs(fromRoute.post - toRoute.post);
                getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
            }
        });
    }
}