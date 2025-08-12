/**
 * Address Autocomplete for frontend forms
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (config, element) {
        var $addressInput = $(element);
        var autocomplete;
        var componentForm = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            administrative_area_level_1: 'short_name',
            country: 'short_name',
            postal_code: 'short_name'
        };

        /**
         * Wait for Google Maps to load
         */
        function waitForGoogleMaps() {
            return new Promise((resolve, reject) => {
                let attempts = 0;
                const maxAttempts = 100; // 10 seconds timeout
                
                const checkInterval = setInterval(() => {
                    attempts++;
                    
                    if (window.google && window.google.maps && window.google.maps.places) {
                        clearInterval(checkInterval);
                        resolve();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                        reject(new Error('Google Maps failed to load'));
                    }
                }, 100);
            });
        }

        /**
         * Initialize autocomplete
         */
        function initAutocomplete() {
            // Disable browser autocomplete
            $addressInput.attr('autocomplete', 'new-password');
            
            autocomplete = new google.maps.places.Autocomplete($addressInput[0], {
                types: ['address'],
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            autocomplete.addListener('place_changed', fillInAddress);
            
            console.log('[DealerLocator] Address autocomplete initialized for', $addressInput.attr('id'));
        }

        /**
         * Fill in address fields
         */
        function fillInAddress() {
            var place = autocomplete.getPlace();
            
            if (!place.address_components) {
                return;
            }

            var addressData = {
                street: '',
                city: '',
                state: '',
                postal_code: '',
                country: '',
                latitude: '',
                longitude: ''
            };

            // Extract address components
            var streetNumber = '';
            var route = '';
            
            place.address_components.forEach(function (component) {
                var addressType = component.types[0];
                
                switch (addressType) {
                    case 'street_number':
                        streetNumber = component.short_name;
                        break;
                    case 'route':
                        route = component.long_name;
                        break;
                    case 'locality':
                        addressData.city = component.long_name;
                        break;
                    case 'administrative_area_level_1':
                        addressData.state = component.short_name;
                        break;
                    case 'country':
                        addressData.country = component.short_name;
                        break;
                    case 'postal_code':
                        addressData.postal_code = component.short_name;
                        break;
                }
            });

            // Combine street number and route
            addressData.street = (streetNumber + ' ' + route).trim();
            
            // Get coordinates
            if (place.geometry && place.geometry.location) {
                addressData.latitude = place.geometry.location.lat();
                addressData.longitude = place.geometry.location.lng();
            }

            // Update form fields
            $addressInput.val(addressData.street);
            
            // Update related fields
            updateField('city', addressData.city);
            updateField('state', addressData.state);
            updateField('postal_code', addressData.postal_code);
            updateField('country', addressData.country);
            updateField('latitude', addressData.latitude);
            updateField('longitude', addressData.longitude);
            
            // Trigger change events
            $addressInput.trigger('change');
        }

        /**
         * Update related field
         */
        function updateField(fieldName, value) {
            var $field = $('#' + fieldName);
            if ($field.length) {
                $field.val(value).trigger('change');
                console.log('[DealerLocator] Updated field:', fieldName, 'with value:', value);
            }
        }

        // Initialize when Google Maps is ready
        waitForGoogleMaps().then(() => {
            initAutocomplete();
        }).catch((error) => {
            console.error('[DealerLocator] Failed to initialize autocomplete:', error);
        });
    };
});