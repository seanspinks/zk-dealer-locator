/**
 * Google Maps API Key Validator
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';

    return {
        /**
         * Validate API Key
         */
        validate: function (apiKey, button) {
            var self = this,
                $button = $(button),
                $input = $('#' + $button.data('input-id')),
                $message = $('#' + $button.data('message-id'));

            if (!apiKey) {
                apiKey = $input.val();
            }

            if (!apiKey) {
                this.showMessage($message, 'error', $t('Please enter an API key to validate.'));
                return;
            }

            $button.prop('disabled', true).text($t('Validating...'));
            $message.hide();

            // Test the API key by loading a simple map
            var testUrl = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=gm_test_callback';
            
            window.gm_test_callback = function () {
                // API key is valid
                self.showMessage($message, 'success', $t('API key is valid! Make sure to enable Maps JavaScript API, Places API, and Geocoding API.'));
                $button.prop('disabled', false).text($t('Validate API Key'));
                delete window.gm_test_callback;
            };

            window.gm_authFailure = function () {
                // API key is invalid or has restrictions
                self.showMessage($message, 'error', $t('API key validation failed. Please check that the key is correct and has the required APIs enabled.'));
                $button.prop('disabled', false).text($t('Validate API Key'));
                delete window.gm_authFailure;
            };

            // Load test script
            var script = document.createElement('script');
            script.src = testUrl;
            script.onerror = function () {
                self.showMessage($message, 'error', $t('Failed to connect to Google Maps API. Please check your API key.'));
                $button.prop('disabled', false).text($t('Validate API Key'));
                delete window.gm_test_callback;
                delete window.gm_authFailure;
            };

            // Set timeout
            setTimeout(function () {
                if (window.gm_test_callback) {
                    self.showMessage($message, 'error', $t('API validation timed out. Please try again.'));
                    $button.prop('disabled', false).text($t('Validate API Key'));
                    delete window.gm_test_callback;
                    delete window.gm_authFailure;
                }
            }, 10000);

            document.head.appendChild(script);
        },

        /**
         * Show validation message
         */
        showMessage: function ($element, type, message) {
            $element
                .removeClass('message-success message-error message-warning')
                .addClass('message-' + type)
                .text(message)
                .show();
        }
    };
});