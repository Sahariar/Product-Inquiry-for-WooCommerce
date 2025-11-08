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
            this.$submitBtn = $('.product-inquiry-for-woocommerce-submit-button');
            this.$messages = $('.product-inquiry-for-woocommerce-form-messages');
            this.$firstInput = $('#product-inquiry-for-woocommerce-name');
            
            // Store original button text
            if (this.$submitBtn.length) {
                this.submitBtnText = this.$submitBtn.text();
            }
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

            // Handle modal form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.handleSubmit($(this));
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
            this.$openButton.focus();
            
            // Re-enable body scroll
            $('body').css('overflow', '');
            
            // Clear messages and reset form
            this.clearMessages();
            this.$form[0].reset();
        },

        /**
         * Handle form submission via AJAX.
         */
        handleSubmit: function($form) {
            const self = this;
            
            // Clear previous messages
            this.clearMessages();

            // Get form elements
            const $nameInput = $form.find('input[name="name"]');
            const $emailInput = $form.find('input[name="email"]');
            const $messageInput = $form.find('textarea[name="message"]');
            const $submitBtn = $form.find('button[type="submit"]');
            const $messagesDiv = $form.find('.product-inquiry-for-woocommerce-form-messages');

            // Basic client-side validation
            const name = $nameInput.val().trim();
            const email = $emailInput.val().trim();
            const message = $messageInput.val().trim();

            if (!name || !email || !message) {
                this.showMessage('Please fill in all required fields.', 'error', $messagesDiv);
                return false;
            }

            if (!this.validateEmail(email)) {
                this.showMessage('Please enter a valid email address.', 'error', $messagesDiv);
                return false;
            }

            // Store original button text
            const submitBtnText = $submitBtn.text();

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Sending...');

            // Prepare form data
            const formData = $form.serialize() + '&action=product_inquiry_for_woocommerce_submit_inquiry';

            // Send AJAX request
            $.ajax({
                url: productInquiryForWooCommerceData.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage(response.data.message, 'success', $messagesDiv);
                        $form[0].reset();
                        
                        // Close modal after 2 seconds (only if it's the modal form)
                        if ($form.attr('id') === 'product-inquiry-for-woocommerce-inquiry-form') {
                            setTimeout(function() {
                                self.closeModal();
                            }, 2000);
                        }
                    } else {
                        self.showMessage(response.data.message, 'error', $messagesDiv);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr);
                    self.showMessage('An unexpected error occurred. Please try again.', 'error', $messagesDiv);
                },
                complete: function() {
                    // Re-enable submit button with original text
                    $submitBtn.prop('disabled', false).text(submitBtnText);
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
        showMessage: function(message, type, $container) {
            const alertClass = type === 'error' ? 'product-inquiry-for-woocommerce-error' : type === 'success' ? 'product-inquiry-for-woocommerce-success' : 'product-inquiry-for-woocommerce-info';
            
            // Use provided container or fall back to default
            const $target = $container && $container.length ? $container : this.$messages;
            
            $target
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

    /**
     * Handle inline/shortcode forms.
     */
    const PI_InlineForms = {
        
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;
            
            // Handle all inline inquiry forms (shortcode/block)
            $(document).on('submit', '.pi-inquiry-form', function(e) {
                e.preventDefault();
                self.handleSubmit($(this));
            });
        },

        handleSubmit: function($form) {
            const self = this;
            
            // Get form elements
            const $responseDiv = $form.find('.pi-form-response');
            const $submitBtn = $form.find('.pi-submit-btn');
            const $spinner = $form.find('.pi-spinner');

            // Basic client-side validation
            const name = $form.find('input[name="name"]').val().trim();
            const email = $form.find('input[name="email"]').val().trim();
            const message = $form.find('textarea[name="message"]').val().trim();

            if (!name || !email || !message) {
                this.showMessage($responseDiv, 'Please fill in all required fields.', 'error');
                return false;
            }

            if (!this.validateEmail(email)) {
                this.showMessage($responseDiv, 'Please enter a valid email address.', 'error');
                return false;
            }

            // Show loading state
            $submitBtn.prop('disabled', true);
            $spinner.addClass('is-active');
            $responseDiv.html('').hide();

            // Prepare form data
            const formData = $form.serialize() + '&action=product_inquiry_for_woocommerce_submit_inquiry';

            // Send AJAX request
            $.ajax({
                url: productInquiry.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage($responseDiv, response.data.message, 'success');
                        $form[0].reset();
                    } else {
                        self.showMessage($responseDiv, response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr);
                    self.showMessage($responseDiv, 'An unexpected error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        },

        validateEmail: function(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },

        showMessage: function($container, message, type) {
            const className = type === 'error' ? 'pi-error' : 'pi-success';
            $container
                .html('<div class="pi-message ' + className + '">' + message + '</div>')
                .slideDown();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PI_Modal.init();
        PI_InlineForms.init();
    });

})(jQuery);