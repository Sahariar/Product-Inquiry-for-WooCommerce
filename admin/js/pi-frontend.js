(function($){
'use strict'
const PI_Modal = {

    /**
     * Initialize mode functionality.
     */
    init:function(){
        this.cacheDom();
        this.bindEvent();
    },
        /**
         * Cache DOM elements.
         */
        cacheDom: function() {
            this.$overlay = $('#pi-modal-overlay');
            this.$modal = $('#pi-modal');
            this.$openButton = $('#pi-open-modal');
            this.$closeButton = $('.pi-modal-close');
            this.$cancelButton = $('.pi-cancel-button');
            this.$form = $('#pi-inquiry-form');
            this.$messages = $('.pi-form-messages');
            this.$firstInput = $('#pi-name');
        },

        /**
         * Bind event listeners.
         */
        bindEvents: function() {
            const self = this;

            // Open modal.
            this.$openButton.on('click', function(e) {
                e.preventDefault();
                self.openModal();
            });

            // Close modal on close button click.
            this.$closeButton.on('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Close modal on cancel button click.
            this.$cancelButton.on('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Close modal on overlay click.
            this.$overlay.on('click', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Close modal on Escape key.
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.$overlay.attr('aria-hidden') === 'false') {
                    self.closeModal();
                }
            });

            // Handle form submission (basic validation only for now).
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
            
            // Focus first input after animation completes.
            setTimeout(() => {
                this.$firstInput.focus();
            }, 250);

            // Prevent body scroll.
            $('body').css('overflow', 'hidden');
        },

        /**
         * Close the modal.
         */
        closeModal: function() {
            this.$overlay.attr('aria-hidden', 'true').fadeOut(200);
            this.$openButton.focus(); // Return focus to trigger button.
            
            // Re-enable body scroll.
            $('body').css('overflow', '');
            
            // Clear messages and reset form.
            this.clearMessages();
            this.$form[0].reset();
        },

        /**
         * Handle form submission (validation only for Feature 1).
         */
        handleSubmit: function() {
            this.clearMessages();

            // Basic client-side validation.
            const name = $('#pi-name').val().trim();
            const email = $('#pi-email').val().trim();
            const message = $('#pi-message').val().trim();

            if (!name || !email || !message) {
                this.showMessage('Please fill in all required fields.', 'error');
                return false;
            }

            if (!this.validateEmail(email)) {
                this.showMessage('Please enter a valid email address.', 'error');
                return false;
            }

            // Feature 2 will add AJAX submission here.
            // For now, just show a placeholder message.
            this.showMessage('Form validation passed. AJAX submission will be implemented in Feature 2.', 'info');
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
            const alertClass = type === 'error' ? 'pi-error' : type === 'success' ? 'pi-success' : 'pi-info';
            
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
    // Initialize on document ready.
    $(document).ready(function() {
        PI_Modal.init();
    });

})(jQuery);