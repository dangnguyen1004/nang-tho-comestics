/**
 * Shop Filters and Search Functionality
 *
 * @package Nang_Tho_Cosmetics
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Category Toggle
        $('.category-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $submenu = $(this).closest('li').find('.category-submenu');
            const $icon = $(this);
            
            $submenu.slideToggle(200);
            $icon.toggleClass('rotate-180');
        });

        // Brand Search Filter
        $('#brand-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.brand-item').each(function() {
                const brandName = $(this).data('brand-name') || '';
                if (brandName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Brand Checkbox Auto Submit
        $('.brand-checkbox').on('change', function() {
            // Show apply button if any checkbox is checked
            const hasChecked = $('.brand-checkbox:checked').length > 0;
            $('#apply-brand-filter').toggleClass('hidden', !hasChecked);
        });

        // Auto-submit brand filter when checkbox changes (optional - can be manual with button)
        // Uncomment if you want auto-submit:
        // $('.brand-checkbox').on('change', function() {
        //     $('.brand-filter-form').submit();
        // });

        // Stock Filter Auto Submit
        $('.stock-toggle').on('change', function() {
            $(this).closest('form').submit();
        });

        // Sort Dropdown Auto Submit
        $('.sort-select').on('change', function() {
            $(this).closest('form').submit();
        });

        // Price Slider (Visual only - actual filtering via form inputs)
        initPriceSlider();

        // In-category search with debounce
        let searchTimeout;
        $('form input[name="s"]').on('input', function() {
            clearTimeout(searchTimeout);
            const $form = $(this).closest('form');
            const searchValue = $(this).val();
            
            // Only auto-submit if there's a value or if cleared (to reset)
            searchTimeout = setTimeout(function() {
                if (searchValue.length >= 2 || searchValue.length === 0) {
                    $form.submit();
                }
            }, 500); // Wait 500ms after user stops typing
        });

        // Header search form - ensure it works with WooCommerce
        // The form action already points to shop page, so no need to override
    });

    /**
     * Initialize Interactive Price Slider
     */
    function initPriceSlider() {
        const $track = $('#price-slider-track');
        const $range = $('#price-slider-range');
        const $minHandle = $('#price-slider-min');
        const $maxHandle = $('#price-slider-max');
        const $minInput = $('input[name="min_price"]');
        const $maxInput = $('input[name="max_price"]');
        const $form = $('.price-filter-form');

        if (!$track.length) return;

        // Get current values or defaults
        let minPrice = parseFloat($minInput.val()) || 0;
        let maxPrice = parseFloat($maxInput.val()) || 1000000;
        const minLimit = 0;
        const maxLimit = 2000000;

        let isDragging = false;
        let activeHandle = null;

        // Update slider position based on price values
        function updateSlider() {
            const minPercent = Math.max(0, Math.min(100, ((minPrice - minLimit) / (maxLimit - minLimit)) * 100));
            const maxPercent = Math.max(0, Math.min(100, ((maxPrice - minLimit) / (maxLimit - minLimit)) * 100));
            
            // Ensure min is always less than max
            if (minPercent >= maxPercent) {
                if (activeHandle === 'min') {
                    minPercent = Math.max(0, maxPercent - 1);
                    minPrice = (minPercent / 100) * (maxLimit - minLimit) + minLimit;
                } else {
                    maxPercent = Math.min(100, minPercent + 1);
                    maxPrice = (maxPercent / 100) * (maxLimit - minLimit) + minLimit;
                }
            }
            
            $minHandle.css('left', minPercent + '%');
            $maxHandle.css('left', maxPercent + '%');
            $range.css({
                'left': minPercent + '%',
                'right': (100 - maxPercent) + '%'
            });

            // Update input fields
            $minInput.val(Math.round(minPrice));
            $maxInput.val(Math.round(maxPrice));
        }

        // Convert mouse position to price value
        function getPriceFromPosition(clientX) {
            const trackRect = $track[0].getBoundingClientRect();
            const percent = Math.max(0, Math.min(100, ((clientX - trackRect.left) / trackRect.width) * 100));
            return (percent / 100) * (maxLimit - minLimit) + minLimit;
        }

        // Handle mousedown on slider handles
        function handleMouseDown(e, handle) {
            e.preventDefault();
            isDragging = true;
            activeHandle = handle;
            $(document).on('mousemove.priceSlider', handleMouseMove);
            $(document).on('mouseup.priceSlider', handleMouseUp);
        }

        function handleMouseMove(e) {
            if (!isDragging) return;
            
            const newPrice = getPriceFromPosition(e.clientX);
            
            if (activeHandle === 'min') {
                minPrice = Math.max(minLimit, Math.min(maxPrice - 10000, newPrice));
            } else {
                maxPrice = Math.min(maxLimit, Math.max(minPrice + 10000, newPrice));
            }
            
            updateSlider();
        }

        function handleMouseUp() {
            if (isDragging) {
                isDragging = false;
                activeHandle = null;
                $(document).off('mousemove.priceSlider mouseup.priceSlider');
            }
        }

        // Initialize slider position
        updateSlider();

        // Attach drag handlers
        $minHandle.on('mousedown', function(e) {
            handleMouseDown(e, 'min');
        });

        $maxHandle.on('mousedown', function(e) {
            handleMouseDown(e, 'max');
        });

        // Handle click on track
        $track.on('click', function(e) {
            if (isDragging) return;
            
            const clickPrice = getPriceFromPosition(e.clientX);
            const trackRect = $track[0].getBoundingClientRect();
            const clickPercent = ((e.clientX - trackRect.left) / trackRect.width) * 100;
            const minPercent = (minPrice / (maxLimit - minLimit)) * 100;
            const maxPercent = (maxPrice / (maxLimit - minLimit)) * 100;
            
            // Determine which handle to move
            const distToMin = Math.abs(clickPercent - minPercent);
            const distToMax = Math.abs(clickPercent - maxPercent);
            
            if (distToMin < distToMax) {
                minPrice = Math.max(minLimit, Math.min(maxPrice - 10000, clickPrice));
            } else {
                maxPrice = Math.min(maxLimit, Math.max(minPrice + 10000, clickPrice));
            }
            
            updateSlider();
        });

        // Update slider when inputs change
        $minInput.on('input', function() {
            const val = parseFloat($(this).val()) || 0;
            minPrice = Math.max(minLimit, Math.min(maxPrice - 10000, val));
            updateSlider();
        });

        $maxInput.on('input', function() {
            const val = parseFloat($(this).val()) || maxLimit;
            maxPrice = Math.min(maxLimit, Math.max(minPrice + 10000, val));
            updateSlider();
        });

        // Prevent form auto-submit, require manual apply button click
        $form.on('submit', function(e) {
            // Form will submit normally, but slider updates are already handled
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            $(document).off('mousemove.priceSlider mouseup.priceSlider');
        });
    }

})(jQuery);