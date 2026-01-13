jQuery(document).ready(function ($) {
    console.log('Nang Tho Checkout Script Loaded');

    if (typeof nang_tho_data === 'undefined') {
        console.error('Nang Tho Data not found!');
        return;
    }

    const vietnamData = nang_tho_data;
    console.log('Vietnam Data:', vietnamData);

    // Helper to populate Select
    function populateSelect(selectElement, options, selectedValue) {
        selectElement.empty();
        selectElement.append('<option value="">' + selectElement.attr('data-placeholder') + '</option>');

        if (!options) return;

        // Handle Array or Object options
        if (Array.isArray(options)) {
            $.each(options, function (index, value) {
                selectElement.append('<option value="' + value + '">' + value + '</option>');
            });
        } else {
            $.each(options, function (key, value) {
                // Key is the province/district name
                selectElement.append('<option value="' + key + '">' + key + '</option>');
            });
        }

        if (selectedValue) {
            selectElement.val(selectedValue);
        }

        // Notify changes
        selectElement.trigger('change');
    }

    // Main update logic
    function updateDistrictsAndWards() {
        const stateSelect = $('#billing_state');
        const citySelect = $('#billing_city');
        const wardSelect = $('#billing_address_2');

        if (stateSelect.length === 0) {
            console.warn('Billing State field not found');
            return;
        }

        // Store current values to restore after repopulating if possible
        const currentCity = citySelect.val() || citySelect.attr('value');
        const currentWard = wardSelect.val() || wardSelect.attr('value');

        console.log('Updating Dropdowns. Current State:', stateSelect.val(), 'City:', currentCity, 'Ward:', currentWard);

        // Ensure dependent fields are Selects
        if (!citySelect.is('select')) {
            console.warn('Billing City is NOT a select element! It is:', citySelect.prop('tagName'));
            // Try to convert or replace? For now just warn.
        }

        // Event: State (Province) Change
        // WooCommerce uses Select2 for country/state, so we must listen for select2:select as well
        stateSelect.off('change.nangtho select2:select').on('change.nangtho select2:select', function () {
            const province = $(this).val();
            console.log('Province Changed (Event Triggered) to:', province);

            citySelect.empty().append('<option value="">Chọn Quận/Huyện</option>');
            wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');

            if (province && vietnamData[province]) {
                console.log('Found Districts for:', province);
                populateSelect(citySelect, vietnamData[province], null);
            } else {
                console.log('No Districts found for:', province);
            }

            // Trigger change on dependent fields to update formatted containers if needed
            citySelect.trigger('change');
            wardSelect.trigger('change');
        });

        // Event: City (District) Change
        citySelect.off('change.nangtho select2:select').on('change.nangtho select2:select', function () {
            const province = stateSelect.val();
            const district = $(this).val();
            console.log('District Changed (Event Triggered) to:', district);


            wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');

            if (province && district && vietnamData[province] && vietnamData[province][district]) {
                console.log('Found Wards for:', district);
                populateSelect(wardSelect, vietnamData[province][district], null);
            }
        });

        // Initial Population Logic
        const currentProvince = stateSelect.val();

        // 1. Recover Districts if needed
        // Check if we need to populate: if valid province exists + cities empty (or only default)
        if (currentProvince && vietnamData[currentProvince]) {
            // Only populate if it looks empty or has just placeholder
            if (citySelect.children('option').length <= 1) {
                console.log('Initial Population: Districts');
                populateSelect(citySelect, vietnamData[currentProvince], currentCity);
            }
        }

        // 2. Recover Wards if needed
        const activeDistrict = citySelect.val();
        if (currentProvince && activeDistrict && vietnamData[currentProvince][activeDistrict]) {
            if (wardSelect.children('option').length <= 1) {
                console.log('Initial Population: Wards');
                populateSelect(wardSelect, vietnamData[currentProvince][activeDistrict], currentWard);
            }
        }
    }

    // Run on load
    updateDistrictsAndWards();

    // Run on WooCommerce Checkout Update
    // 'updated_checkout' is triggered after the AJAX update is complete
    $(document.body).on('updated_checkout', function () {
        console.log('WooCommerce Updated Checkout - Re-binding events');
        updateDistrictsAndWards();

        // Ensure placeholders are correct
        $('#billing_city').attr('placeholder', 'Chọn Quận/Huyện').attr('data-placeholder', 'Chọn Quận/Huyện');
        $('#billing_address_2').attr('placeholder', 'Chọn Phường/Xã').attr('data-placeholder', 'Chọn Phường/Xã');
    });

    // Also listen for country change just in case
    $(document.body).on('country_to_state_changed', function () {
        console.log('Country changed - waiting for state update');
        setTimeout(updateDistrictsAndWards, 500); // Increased timeout to be safe
    });

});
