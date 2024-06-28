(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	
	jQuery(document).ready(function($) {

	    $('.user-data').select2({
	        ajax: {
	            url: sejoli_import.ajax_url,
	            dataType: 'json',
	            delay: 250,
	            data: function(params) {
	                return {
	                    q: params.term, // search term
	                    action: 'get_users_select2' // AJAX action
	                };
	            },
	            processResults: function(data) {
	                return {
	                    results: data
	                };
	            },
	            cache: true
	        },
	        minimumInputLength: 1,
	        placeholder: 'Select a user',
	        allowClear: true
	    });

	    $('.affiliate-data').select2({
	        ajax: {
	            url: sejoli_import.ajax_url,
	            dataType: 'json',
	            delay: 250,
	            data: function(params) {
	                return {
	                    q: params.term, // search term
	                    action: 'get_users_select2' // AJAX action
	                };
	            },
	            processResults: function(data) {
	                return {
	                    results: data
	                };
	            },
	            cache: true
	        },
	        minimumInputLength: 1,
	        placeholder: 'Select a affiliate',
	        allowClear: true
	    });

	    $('.product-data').select2({
	        ajax: {
	            url: sejoli_import.ajax_url,
	            dataType: 'json',
	            delay: 250,
	            data: function(params) {
	                return {
	                    q: params.term, // search term
	                    action: 'get_products_select2' // AJAX action
	                };
	            },
	            processResults: function(data) {
	                return {
	                    results: data
	                };
	            },
	            cache: true
	        },
	        minimumInputLength: 1,
	        placeholder: 'Select a product',
	        allowClear: true
	    });

	    $('.coupon-data').select2({
	        ajax: {
	            url: sejoli_import.ajax_url,
	            dataType: 'json',
	            delay: 250,
	            data: function(params) {
	                return {
	                    q: params.term, // search term
	                    action: 'get_coupon_select2' // AJAX action
	                };
	            },
	            processResults: function(data) {
	                return {
	                    results: data
	                };
	            },
	            cache: true
	        },
	        minimumInputLength: 1,
	        placeholder: 'Select a coupon',
	        allowClear: true
	    });

	    $('#input_order_data').on('submit', function(e) {
	        e.preventDefault();

	        var formData = $(this).serialize();
	        formData += '&action=sejoli_create_order_data';
	        formData += '&nonce=' + sejoli_import.ajax_nonce.submit_order;

        	var userConfirmed = confirm('Apakah Anda yakin ingin input data ini?');

		    if (userConfirmed) {

        		$('#form-message').text('Please wait...').css('color', 'blue').show();

		        $.ajax({
		            url : sejoli_import.ajax_url,
		            type: 'post',
		            data: formData,
		            success: function(response) {

		                if (response.valid) {

		                	var errorMsg = response && response.messages ? response.messages : 'Form submitted successfully! ';
		                    $('#form-message').text(errorMsg).css('color', 'green').show();
		                    
		                    setTimeout(function() {
		                        $('#form-message').hide();
		                    }, 5000);

		                } else {

		                    var errorMsg = response && response.messages ? response.messages : 'Form submission failed! ';
		                    $('#form-message').text(errorMsg).css('color', 'red').show();

		                }

		            },
		            error: function() {

		                $('#form-message').text('An error occurred. Please try again.').css('color', 'red').show();
		                
		            }
		        });

		    }
	    });

	    $('#import_order_data').on('submit', function(e) {
	        e.preventDefault();

	        var fileInput = $('#import_order_file')[0];
       	 	var file = fileInput.files[0];

	        var formData = new FormData();
	        formData.append('action', 'sejoli_import_order_data');
	        formData.append('nonce', sejoli_import.ajax_nonce.import_order);
	        formData.append('import_order_file', file);

        	var userConfirmed = confirm('Apakah Anda yakin ingin import data ini?');

		    if (userConfirmed) {
        		
        		$('#form-message-import').text('Please wait...').css('color', 'blue').show();

		        $.ajax({
		            url : sejoli_import.ajax_url,
		            type: 'post',
		            data: formData,
		            contentType: false,
            		processData: false,
		            success: function(response) {

		                if (response.valid) {

		                	var errorMsg = response && response.messages ? response.messages : 'Form submitted successfully! ';
		                    $('#form-message-import').text(errorMsg).css('color', 'green').show();
		                    
		                    setTimeout(function() {
		                        $('#form-message-import').hide();
		                    }, 5000);

		                } else {

		                    var errorMsg = response && response.messages ? response.messages : 'Form submission failed! ';
		                    $('#form-message-import').text(errorMsg).css('color', 'red').show();

		                }

		            },
		            error: function() {

		                $('#form-message-import').text('An error occurred. Please try again.').css('color', 'red').show();
		                
		            }
		        });

		    }
	    });

        function toggleOptionalFields() {
            if ($('#user-id').val() === '') {
                $('.optional-fields').show();
            } else {
                $('.optional-fields').hide();
            }
        }

        toggleOptionalFields();

        $('#user-id').change(function() {
            toggleOptionalFields();
        });

	});

	document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('tab-1-nav').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('tab-1-content').style.display = 'block';
            document.getElementById('tab-2-content').style.display = 'none';
            this.classList.add('nav-tab-active');
            document.getElementById('tab-2-nav').classList.remove('nav-tab-active');
        });
        document.getElementById('tab-2-nav').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('tab-1-content').style.display = 'none';
            document.getElementById('tab-2-content').style.display = 'block';
            this.classList.add('nav-tab-active');
            document.getElementById('tab-1-nav').classList.remove('nav-tab-active');
        });
    });

})( jQuery );
