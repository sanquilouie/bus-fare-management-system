
document.addEventListener('DOMContentLoaded', function () {
    const nameFields = ['firstname', 'middlename', 'lastname', 'suffix'];

    nameFields.forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('input', function (e) {
            this.value = this.value.replace(/[^A-Za-z\s-]/g, ''); // Allow only letters, spaces, and hyphens
        });
    });
});

        $(document).ready(function () {
            let confirmationShown = false; // To track confirmation dialog
            let rfidScanned = false; // To track if RFID has been scanned

            $('#phone').on('input', function () {
                var contactValue = $(this).val();

                // Allow only digits and limit to 11 characters
                contactValue = contactValue.replace(/[^0-9]/g, ''); // Remove non-numeric characters
                if (contactValue.length > 11) {
                    contactValue = contactValue.substring(0, 11); // Limit to 11 digits
                }
                $(this).val(contactValue); // Update the input value

                // Send AJAX request if the input has exactly 11 characters
                if (contactValue.length === 11) {
                    $.ajax({
                        type: "POST",
                        url: "../../actions/check_contact.php",
                        data: { contactnumber: contactValue },
                        dataType: "json",
                        success: function (response) {
                            if (response.exists) {
                                $('#phone').addClass('is-invalid'); // Add invalid class to input
                                // Set error message directly in the existing div
                                $('#contactError').text("This contact number is already registered.").show();
                            } else {
                                $('#phone').removeClass('is-invalid'); // Remove invalid class
                                $('#contactError').hide(); // Hide error message if it exists
                            }
                        },
                        error: function () {
                            console.error("Error checking contact number.");
                        }
                    });
                } else {
                    $('#phone').removeClass('is-invalid');
                    $('#contactError').hide(); // Hide error message if the input is less than 11 characters
                }
            });


            $('#email').on('input', function () {
                var email = $(this).val();

                // Check if email is not empty
                if (email) {
                    $.ajax({
                        url: '/NewRam/actions/check_email.php', // Path to your PHP script
                        type: 'POST',
                        data: { email: email },
                        dataType: 'json',
                        success: function (response) {
                            if (response.exists) {
                                // Email already exists
                                $('#email').addClass('is-invalid');
                                $('#emailFeedback').remove();
                                $('#email').after('<div id="emailFeedback" class="invalid-feedback">This email is already registered.</div>');
                            } else {
                                // Email does not exist
                                $('#email').removeClass('is-invalid');
                                $('#emailFeedback').remove();
                            }
                        },
                        error: function () {
                            console.error('Error checking email.');
                        }
                    });
                } else {
                    // Reset feedback if email is empty
                    $('#email').removeClass('is-invalid');
                    $('#emailFeedback').remove();
                }
            });

            $('.register').click(function (event) {
                event.preventDefault(); // Prevent the default form submission

                if (!confirmationShown) {
                    confirmationShown = true;

                    Swal.fire({
                        title: 'Confirm Registration?',
                        text: "Are you sure you want to register?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#cc0000',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, register!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Waiting for RFID',
                                text: 'Please scan your RFID tag.',
                                icon: 'info',
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });

                            $('#account_number').removeAttr('readonly').focus();
                        } else {
                            confirmationShown = false; // Reset if cancelled
                        }
                    });
                }
            });

            // Birthday and age validation
            function calculateAge(birthday) {
                let today = new Date();
                let birthDate = new Date(birthday);
                let age = today.getFullYear() - birthDate.getFullYear();
                let monthDifference = today.getMonth() - birthDate.getMonth();
                if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            $('#birthday').change(function () {
                let birthday = $(this).val();
                if (birthday) {
                    let age = calculateAge(birthday);
                    $('#age').val(age);
                }
            });

            // Load provinces on page load
            $.ajax({
                url: 'https://psgc.gitlab.io/api/provinces', // API URL for provinces
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    // Populate the province dropdown
                    data.sort((a, b) => a.name.localeCompare(b.name));
                    $.each(data, function (index, province) {
                        $('#province').append($('<option>', {
                            value: province.code,
                            text: province.name
                        }));
                    });
                },
                error: function () {
                    console.error('Error fetching provinces');
                }
            });

            // When a province is selected, fetch municipalities
            $('#province').change(function () {
                const provinceCode = $(this).val();
                $('#municipality').empty().append('<option value="">-- Select Municipality --</option>');
                $('#barangay').empty().append('<option value="">-- Select Barangay --</option>');

                if (provinceCode) {
                    $.ajax({
                        url: 'https://psgc.gitlab.io/api/cities-municipalities', // API URL for municipalities
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Filter municipalities by province code
                            const municipalities = data.filter(municipality => municipality.provinceCode === provinceCode);
                            municipalities.sort((a, b) => a.name.localeCompare(b.name));
                            if (municipalities.length > 0) {
                                $.each(municipalities, function (index, municipality) {
                                    $('#municipality').append($('<option>', {
                                        value: municipality.code,
                                        text: municipality.name
                                    }));
                                });
                            } else {
                                console.warn('No municipalities found for this province.');
                            }
                        },
                        error: function () {
                            console.error('Error fetching municipalities');
                        }
                    });
                }
            });

            // When a municipality is selected, fetch barangays
            $('#municipality').change(function () {
                const municipalityCode = $(this).val();
                $('#barangay').empty().append('<option value="">-- Select Barangay --</option>');

                if (municipalityCode) {
                    // Adjusted barangay API call
                    $.ajax({
                        url: `https://psgc.gitlab.io/api/barangays`, // Ensure this endpoint is correct
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Filter barangays by municipality code
                            const barangays = data.filter(barangay => barangay.municipalityCode === municipalityCode);
                            barangays.sort((a, b) => a.name.localeCompare(b.name));
                            if (barangays.length > 0) {
                                $.each(barangays, function (index, barangay) {
                                    $('#barangay').append($('<option>', {
                                        value: barangay.code,
                                        text: barangay.name
                                    }));
                                });
                            } else {
                                console.warn('No barangays found for this municipality.');
                            }
                        },
                        error: function () {
                            console.error('Error fetching barangays');
                        }
                    });
                }
            });
        });

        // Calculate the date for 7 years ago
        const today = new Date();
        const sevenYearsAgo = new Date(today.setFullYear(today.getFullYear() - 7));

        // Format the date as YYYY-MM-DD
        const formattedDate = sevenYearsAgo.toISOString().split('T')[0];

        // Set the minimum date in the input field
        document.getElementById("birthday").setAttribute("min", formattedDate);
