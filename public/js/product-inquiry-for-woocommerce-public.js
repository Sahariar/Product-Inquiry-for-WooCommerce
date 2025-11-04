/**
 * Product Inquiry Frontend
 *
 * Handles modal interactions and AJAX form submission.
 *
 * @package Product_Inquiry
 * @since   1.0.0
 */

(function($) {
    'use strict';

    const PI_Modal = {

        /**
         * Initialize modal functionality.
         */
        init: function() {
            this.cacheDom();
            this.bindEvents();
        },

        /**
         * Cache DOM elements.
         */
        cacheDom: function() {
            this.$overlay = $('#product-inquiry-for-woocommerce-modal-overlay');
            this.$modal = $('#product-inquiry-for-woocommerce-modal');
            this.$openButton = $('#product-inquiry-for-woocommerce-open-modal');
            this.$closeButton = $('.product-inquiry-for-woocommerce-modal-close');
            this.$cancelButton = $('.product-inquiry-for-woocommerce-cancel-button');
            this.$form = $('#product-inquiry-for-woocommerce-inquiry-form');
            this.$submitBtn = $('.product-inquiry-for-woocommerce-submit-button'); // FIXED: Added this line
            this.$messages = $('.product-inquiry-for-woocommerce-form-messages');
            this.$firstInput = $('#product-inquiry-for-woocommerce-name');
            
            // Store original button text
            this.submitBtnText = this.$submitBtn.text();
        },

        /**
         * Bind event listeners.
         */
        bindEvents: function() {
            const self = this;

            // Open modal
            this.$openButton.on('click', function(e) {
                e.preventDefault();
                self.openModal();
            });

            // Close modal on close button click
            this.$closeButton.on('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Close modal on cancel button click
            this.$cancelButton.on('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Close modal on overlay click
            this.$overlay.on('click', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.$overlay.attr('aria-hidden') === 'false') {
                    self.closeModal();
                }
            });

            // Handle form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.handleSubmit();
            });
        },

        /**
         * Open the modal.
         */
        openModal: function() {
            this.$overlay.attr('aria-hidden', 'false').fadeIn(200);
            this.$modal.attr('tabindex', '-1').focus();
            
            // Focus first input after animation completes
            setTimeout(() => {
                this.$firstInput.focus();
            }, 250);

            // Prevent body scroll
            $('body').css('overflow', 'hidden');
        },

        /**
         * Close the modal.
         */
        closeModal: function() {
            this.$overlay.attr('aria-hidden', 'true').fadeOut(200);
            this.$openButton.focus(); // Return focus to trigger button
            
            // Re-enable body scroll
            $('body').css('overflow', '');
            
            // Clear messages and reset form
            this.clearMessages();
            this.$form[0].reset();
        },

        /**
         * Handle form submission via AJAX.
         */
        handleSubmit: function() {
            const self = this;
            
            // Clear previous messages
            this.clearMessages();

            // Basic client-side validation
            const name = $('#product-inquiry-for-woocommerce-name').val().trim();
            const email = $('#product-inquiry-for-woocommerce-email').val().trim();
            const message = $('#product-inquiry-for-woocommerce-message').val().trim();

            if (!name || !email || !message) {
                this.showMessage('Please fill in all required fields.', 'error');
                return false;
            }

            if (!this.validateEmail(email)) {
                this.showMessage('Please enter a valid email address.', 'error');
                return false;
            }

            // Disable submit button
            this.$submitBtn.prop('disabled', true).text('Sending...');

            // Prepare form data
            const formData = this.$form.serialize() + '&action=product_inquiry_for_woocommerce_submit_inquiry';
              // DEBUG: Log what we're sending
        console.log('Form Data:', formData);
        console.log('AJAX URL:', productInquiryForWooCommerceData.ajax_url);
            // Send AJAX request
            $.ajax({
                url: productInquiryForWooCommerceData.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage(response.data.message, 'success');
                        self.$form[0].reset();
                        
                        // Close modal after 2 seconds
                        setTimeout(function() {
                            self.closeModal();
                        }, 2000);
                    } else {
                        self.showMessage(response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error , xhr);
                    self.showMessage('An unexpected error occurred. Please try again.', 'error');
                },
                complete: function() {
                    // Re-enable submit button with original text
                    self.$submitBtn.prop('disabled', false).text(self.submitBtnText);
                }
            });
        },

        /**
         * Validate email format.
         */
        validateEmail: function(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },

        /**
         * Display a message to the user.
         */
        showMessage: function(message, type) {
            const alertClass = type === 'error' ? 'product-inquiry-for-woocommerce-error' : type === 'success' ? 'product-inquiry-for-woocommerce-success' : 'product-inquiry-for-woocommerce-info';
            
            this.$messages
                .html('<p class="' + alertClass + '">' + message + '</p>')
                .slideDown();
        },

        /**
         * Clear messages.
         */
        clearMessages: function() {
            this.$messages.html('').hide();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PI_Modal.init();
    });

})(jQuery);