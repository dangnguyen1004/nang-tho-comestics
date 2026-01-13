/**
 * Product Detail Page Functionality
 *
 * @package Nang_Tho_Cosmetics
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Product Image Gallery - Thumbnail Switching
        $('.product-thumbnail').on('click', function(e) {
            e.preventDefault();
            
            const $thumbnail = $(this);
            const fullUrl = $thumbnail.data('full-url');
            const imageId = $thumbnail.data('image-id');
            
            if (!fullUrl) return;
            
            // Update main image
            const $mainImage = $('.product-main-image');
            if ($mainImage.is('img')) {
                $mainImage.attr('src', fullUrl);
            } else {
                $mainImage.css('background-image', 'url("' + fullUrl + '")');
            }
            
            // Update active thumbnail
            $('.product-thumbnail').removeClass('border-primary border-2').addClass('border-transparent');
            $thumbnail.removeClass('border-transparent').addClass('border-primary border-2');
        });

        // Quantity Stepper
        $('.quantity-increase').on('click', function(e) {
            e.preventDefault();
            const $input = $(this).siblings('input.qty');
            const max = parseFloat($input.attr('max')) || 9999;
            const current = parseFloat($input.val()) || 1;
            const step = parseFloat($input.attr('step')) || 1;
            const newVal = Math.min(max, current + step);
            $input.val(newVal).trigger('change');
        });

        $('.quantity-decrease').on('click', function(e) {
            e.preventDefault();
            const $input = $(this).siblings('input.qty');
            const min = parseFloat($input.attr('min')) || 1;
            const current = parseFloat($input.val()) || 1;
            const step = parseFloat($input.attr('step')) || 1;
            const newVal = Math.max(min, current - step);
            $input.val(newVal).trigger('change');
        });

        // Product Tabs Switching
        $('.product-tab-btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const tabKey = $btn.data('tab');
            
            // Update active tab button
            $('.product-tab-btn').removeClass('border-primary text-primary font-bold')
                .addClass('border-transparent text-gray-500 font-medium');
            $btn.removeClass('border-transparent text-gray-500 font-medium')
                .addClass('border-primary text-primary font-bold');
            
            // Show corresponding tab panel
            $('.product-tab-panel').addClass('hidden');
            $('#tab-' + tabKey).removeClass('hidden');
        });

        // Product Variation Selection (handled by WooCommerce for variable products)
        // Custom variation buttons for simple products with attributes
        $('.product-variation-btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const attribute = $btn.data('attribute');
            const value = $btn.data('value');
            
            // Update active variation button
            $('.product-variation-btn').removeClass('border-2 border-primary bg-primary/5 text-primary font-bold')
                .addClass('border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300');
            $btn.removeClass('border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300')
                .addClass('border-2 border-primary bg-primary/5 text-primary font-bold');
            
            // Trigger WooCommerce variation selection if it's a variable product
            if ($product.is('variable') && $('#pa_' + attribute).length) {
                $('#pa_' + attribute).val(value).trigger('change');
            }
        });

        // Add to Cart from Related Products
        $('.add-to-cart-related').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const productId = $button.data('product-id');
            if (!productId) return;
            
            // Use WooCommerce's built-in add to cart functionality
            const $form = $('<form>', {
                method: 'POST',
                action: wc_add_to_cart_params?.wc_ajax_url?.toString().replace('%%endpoint%%', 'add_to_cart') || '/?wc-ajax=add_to_cart'
            });
            
            $form.append($('<input>', { type: 'hidden', name: 'product_id', value: productId }));
            $form.append($('<input>', { type: 'hidden', name: 'quantity', value: 1 }));
            
            // Submit form
            $('body').append($form);
            $form.submit();
        });

        // Zoom Image Button (placeholder for future lightbox implementation)
        $('.product-zoom-btn').on('click', function(e) {
            e.preventDefault();
            // Future: Open image in lightbox/modal
            // For now, just scroll to main image
            $('html, body').animate({
                scrollTop: $('.product-main-image').offset().top - 100
            }, 300);
        });

        // Write Review Button
        $('.write-review-btn').on('click', function(e) {
            e.preventDefault();
            // Scroll to comment form if exists, otherwise show it
            const $commentForm = $('#review_form_wrapper, #respond');
            if ($commentForm.length) {
                $('html, body').animate({
                    scrollTop: $commentForm.offset().top - 100
                }, 300);
                $commentForm.find('textarea').focus();
            }
        });

        // Buy Now Button - Add to cart then redirect to checkout
        $('.buy-now-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const checkoutUrl = $btn.data('checkout-url') || (typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.checkout_url : null);
            const $form = $('form.cart');
            
            if (!$form.length) {
                alert('Không tìm thấy form sản phẩm. Vui lòng thử lại.');
                return false;
            }

            // Get product ID - first try from button data attribute, then from form button value
            let productId = $btn.data('product-id') ||
                           $form.find('button[name="add-to-cart"]').attr('value') || 
                           $form.find('button[name="add-to-cart"]').val() ||
                           $form.find('input[name="add-to-cart"]').val() ||
                           $form.find('input[name="product_id"]').val();
            
            // If still not found, extract from product container ID (product-{ID})
            if (!productId) {
                const $productContainer = $form.closest('[id^="product-"]');
                if ($productContainer.length) {
                    const idAttr = $productContainer.attr('id');
                    const match = idAttr ? idAttr.match(/product-(\d+)/) : null;
                    if (match && match[1]) {
                        productId = match[1];
                    }
                }
            }
            
            const quantity = $form.find('input.qty').val() || $form.find('input[name="quantity"]').val() || 1;

            if (!productId) {
                console.error('Cannot find product ID. Button data:', $btn.data());
                console.error('Form HTML:', $form.html().substring(0, 500));
                alert('Không tìm thấy sản phẩm. Vui lòng thử lại.');
                return false;
            }

            // Disable button during submission
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin" style="animation: spin 1s linear infinite;">sync</span> Đang xử lý...');

            // Try AJAX first if available
            const ajaxUrl = (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.ajax_url) 
                ? wc_add_to_cart_params.ajax_url 
                : null;

            if (ajaxUrl && ajaxUrl !== '/?wc-ajax=add_to_cart') {
                // Use AJAX to add to cart
                $.ajax({
                    type: 'POST',
                    url: ajaxUrl,
                    data: {
                        product_id: productId,
                        quantity: quantity,
                        buy_now: '1'
                    },
                    success: function(response) {
                        if (response && response.redirect) {
                            window.location.href = response.redirect;
                        } else if (response && response.error && response.product_url) {
                            window.location.href = response.product_url;
                        } else if (checkoutUrl) {
                            window.location.href = checkoutUrl;
                        } else {
                            window.location.href = '/checkout/';
                        }
                    },
                    error: function() {
                        // Fallback to form submission
                        submitBuyNowForm($form, checkoutUrl);
                    }
                });
            } else {
                // Use form submission
                submitBuyNowForm($form, checkoutUrl);
            }
            
            return false;
        });

        // Helper function to submit form with buy_now parameter
        function submitBuyNowForm($form, checkoutUrl) {
            // Add buy_now hidden input if not exists
            if (!$form.find('input[name="buy_now"]').length) {
                $form.append('<input type="hidden" name="buy_now" value="1">');
            }

            // Submit form - PHP hook will handle redirect
            $form.off('submit.buynow').on('submit.buynow', function(e) {
                // Form will submit normally, PHP hook handles redirect
                return true;
            });

            // Trigger form submission
            const $submitBtn = $form.find('button[name="add-to-cart"], button[type="submit"]').first();
            if ($submitBtn.length) {
                $submitBtn.click();
            } else {
                $form.submit();
            }

            // Backup redirect after delay
            if (checkoutUrl) {
                setTimeout(function() {
                    window.location.href = checkoutUrl;
                }, 1500);
            }
        }
    });

})(jQuery);