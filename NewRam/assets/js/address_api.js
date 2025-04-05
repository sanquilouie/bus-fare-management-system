
$(document).ready(function () {
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
