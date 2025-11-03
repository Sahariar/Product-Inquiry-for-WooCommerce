/**
 * Admin JavaScript for Product Inquiry
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin/js
 * @since      1.0.0
 */

(function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {

			/**
			 * Confirm before deleting inquiry
			 */
			$( '.submitdelete' ).on(
				'click',
				function (e) {
					if ( ! confirm( piAdmin.confirmDelete ) ) {
						e.preventDefault();
						return false;
					}
				}
			);

			/**
			 * Reply functionality
			 */
			if ( typeof piReply !== 'undefined' ) {

				// Send reply button click
				$( '#pi_send_reply_btn' ).on(
					'click',
					function (e) {
						e.preventDefault();

						var $btn              = $( this );
						var $spinner          = $btn.siblings( '.spinner' );
						var $messageContainer = $( '#pi_reply_message_container' );
						var $textarea         = $( '#pi_reply_message' );
						var replyMessage      = $textarea.val().trim();

						// Validate message
						if ( replyMessage.length < 10 ) {
							showMessage( 'error', piReply.messages.empty );
							return;
						}

						// Confirm before sending
						if ( ! confirm( piReply.messages.confirm_send ) ) {
							return;
						}

						// Disable button and show spinner
						$btn.prop( 'disabled', true );
						$spinner.addClass( 'is-active' );
						$messageContainer.hide();

						// Send AJAX request
						$.ajax(
							{
								url: piReply.ajax_url,
								type: 'POST',
								data: {
									action: 'pi_send_reply',
									nonce: piReply.nonce,
									inquiry_id: piReply.inquiry_id,
									reply_message: replyMessage
								},
								success: function (response) {
									if ( response.success ) {
										showMessage( 'success', response.data.message );

										// Clear textarea
										$textarea.val( '' );

										// Update status badge if on edit screen
										updateStatusBadge();

										// Reload page after 2 seconds to show reply in history
										setTimeout(
											function () {
												location.reload();
											},
											2000
										);

									} else {
										showMessage( 'error', response.data.message || piReply.messages.error );
									}
								},
								error: function () {
									showMessage( 'error', piReply.messages.error );
								},
								complete: function () {
									$btn.prop( 'disabled', false );
									$spinner.removeClass( 'is-active' );
								}
							}
						);
					}
				);

				// Show message helper
				function showMessage( type, message ) {
					var $container = $( '#pi_reply_message_container' );
					var className  = type === 'success' ? 'notice-success' : 'notice-error';

					$container
						.removeClass( 'notice-success notice-error' )
						.addClass( 'notice ' + className )
						.html( '<p>' + message + '</p>' )
						.show();
				}

				// Update status badge
				function updateStatusBadge() {
					$( '.pi-status' ).removeClass( 'pi-status-unread' ).addClass( 'pi-status-replied' ).text( 'Replied' );
				}
			}

			/**
			 * Auto-dismiss admin notices after 5 seconds
			 */
			setTimeout(
				function () {
					$( '.notice.is-dismissible' ).fadeOut(
						300,
						function () {
							$( this ).remove();
						}
					);
				},
				5000
			);

		}
	);

})( jQuery );