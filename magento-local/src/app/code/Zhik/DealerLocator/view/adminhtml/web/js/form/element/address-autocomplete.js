define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'mage/translate'
], function (Abstract, $, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementTmpl: 'Zhik_DealerLocator/form/element/address-autocomplete',
            googleApiLoaded: false,
            autocomplete: null
        },

        /**
         * Initializes component
         */
        initialize: function () {
            this._super();
            
            // Wait for the element to be rendered before loading API
            setTimeout(function() {
                this.loadGoogleMapsApi();
            }.bind(this), 100);
            
            return this;
        },

        /**
         * Load Google Maps API
         */
        loadGoogleMapsApi: function () {
            var self = this;
            
            // Check if API is already loaded
            if (window.google && window.google.maps && window.google.maps.places) {
                self.googleApiLoaded = true;
                self.initAutocomplete();
                return;
            }

            // Check if API key is available in window object first
            if (window.dealerLocatorConfig && window.dealerLocatorConfig.apiKey) {
                console.log('[DealerLocator] Using API key from window object');
                self.loadGoogleMapsScript(window.dealerLocatorConfig.apiKey);
                return;
            }

            // Get API key from config
            require(['mage/url'], function (urlBuilder) {
                var apiUrl = urlBuilder.build('dealerlocator/config/apikey');
                console.log('[DealerLocator] Fetching API key from:', apiUrl);
                
                $.ajax({
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        console.log('[DealerLocator] API key response:', response);
                        
                        if (response.api_key) {
                            self.loadGoogleMapsScript(response.api_key);
                        } else {
                            console.warn('[DealerLocator] No API key configured');
                            console.warn('[DealerLocator] Please configure the API key in Stores > Configuration > Zhik > Dealer Locator > Google Maps Configuration');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('[DealerLocator] Failed to fetch API key:', error);
                        console.error('[DealerLocator] Status:', status);
                        console.error('[DealerLocator] Response:', xhr.responseText);
                    }
                });
            });
        },

        /**
         * Load Google Maps script with API key
         */
        loadGoogleMapsScript: function(apiKey) {
            var self = this;
            
            console.log('[DealerLocator] Loading Google Maps API...');
            
            // Create unique callback name to avoid conflicts
            var callbackName = 'initGooglePlaces_' + Math.random().toString(36).substr(2, 9);
            
            window[callbackName] = function () {
                console.log('[DealerLocator] Google Maps API loaded successfully');
                self.googleApiLoaded = true;
                self.initAutocomplete();
                
                // Clean up global callback
                delete window[callbackName];
            };
            
            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&callback=' + callbackName;
            script.async = true;
            script.defer = true;
            script.onerror = function() {
                console.error('[DealerLocator] Failed to load Google Maps API');
                console.error('[DealerLocator] Script URL was:', script.src);
            };
            document.head.appendChild(script);
        },

        /**
         * Initialize autocomplete
         */
        initAutocomplete: function () {
            var self = this;
            
            if (!this.googleApiLoaded) {
                console.warn('[DealerLocator] Google API not loaded yet, waiting...');
                return;
            }

            // Try multiple times to find the input element
            var attempts = 0;
            var maxAttempts = 10;
            
            var tryInit = function() {
                attempts++;
                var input = document.getElementById(self.uid);
                
                if (!input && attempts < maxAttempts) {
                    console.log('[DealerLocator] Input not found yet, attempt ' + attempts + ' of ' + maxAttempts);
                    setTimeout(tryInit, 500);
                    return;
                }
                
                if (input && window.google && window.google.maps && window.google.maps.places) {
                    console.log('[DealerLocator] Initializing autocomplete for field:', self.uid);
                    console.log('[DealerLocator] Input element found:', input);
                    
                    // Disable browser autocomplete
                    input.setAttribute('autocomplete', 'new-password');
                    
                    try {
                        self.autocomplete = new google.maps.places.Autocomplete(input, {
                            types: ['address'],
                            fields: ['address_components', 'geometry', 'formatted_address']
                        });

                        self.autocomplete.addListener('place_changed', function () {
                            console.log('[DealerLocator] Place selected');
                            self.fillInAddress();
                        });
                        
                        console.log('[DealerLocator] Autocomplete initialized successfully');
                        
                        // Add visual indicator that autocomplete is active
                        input.style.backgroundColor = '#f0f8ff';
                        setTimeout(function() {
                            input.style.backgroundColor = '';
                        }, 2000);
                        
                    } catch (e) {
                        console.error('[DealerLocator] Failed to initialize autocomplete:', e);
                    }
                } else {
                    console.warn('[DealerLocator] Autocomplete not initialized - missing dependencies');
                    if (!input) console.warn('[DealerLocator] Input field not found after ' + attempts + ' attempts');
                    if (!window.google) console.warn('[DealerLocator] Google object not found');
                    if (window.google && !window.google.maps) console.warn('[DealerLocator] Google Maps not loaded');
                    if (window.google && window.google.maps && !window.google.maps.places) console.warn('[DealerLocator] Google Places library not loaded');
                }
            };
            
            // Start trying to initialize
            setTimeout(tryInit, 100);
        },

        /**
         * Fill in address fields from Google Places
         */
        fillInAddress: function () {
            var place = this.autocomplete.getPlace();
            
            if (!place.address_components) {
                return;
            }

            var addressComponents = {
                street_number: '',
                route: '',
                locality: '',
                administrative_area_level_1: '',
                postal_code: '',
                country: ''
            };

            // Extract address components
            place.address_components.forEach(function (component) {
                var type = component.types[0];
                if (addressComponents.hasOwnProperty(type)) {
                    addressComponents[type] = component.long_name;
                    if (type === 'country') {
                        addressComponents[type] = component.short_name;
                    }
                }
            });

            // Update form fields
            var streetAddress = addressComponents.street_number + ' ' + addressComponents.route;
            this.value(streetAddress.trim());

            // Update other fields if they exist in the form
            this.updateRelatedField('city', addressComponents.locality);
            this.updateRelatedField('state', addressComponents.administrative_area_level_1);
            this.updateRelatedField('postal_code', addressComponents.postal_code);
            this.updateRelatedField('country', addressComponents.country);

            // Update coordinates if available
            if (place.geometry && place.geometry.location) {
                this.updateRelatedField('latitude', place.geometry.location.lat());
                this.updateRelatedField('longitude', place.geometry.location.lng());
            }
        },

        /**
         * Update related field value
         */
        updateRelatedField: function (fieldName, value) {
            console.log('[DealerLocator] Updating field:', fieldName, 'with value:', value);
            
            // Try to find the field in various ways
            var field = null;
            
            // Method 1: Direct child of address fieldset
            if (this.source && this.source.get) {
                field = this.source.get('address.' + fieldName);
            }
            
            // Method 2: Search through parent form
            if (!field && this.containers && this.containers[0]) {
                var parent = this.containers[0];
                while (parent && !field) {
                    if (parent.getChild) {
                        field = parent.getChild('address.' + fieldName);
                        if (!field) {
                            field = parent.getChild(fieldName);
                        }
                    }
                    parent = parent.containers ? parent.containers[0] : null;
                }
            }
            
            // Method 3: Use registry to find the field
            if (!field) {
                require(['uiRegistry'], function(registry) {
                    var possibleNames = [
                        'dealerlocator_location_form.dealerlocator_location_form.address.' + fieldName,
                        'dealerlocator_location_form.address.' + fieldName,
                        'address.' + fieldName,
                        fieldName
                    ];
                    
                    for (var i = 0; i < possibleNames.length; i++) {
                        field = registry.get(possibleNames[i]);
                        if (field) {
                            console.log('[DealerLocator] Found field via registry:', possibleNames[i]);
                            break;
                        }
                    }
                    
                    if (field && field.value) {
                        field.value(value);
                        console.log('[DealerLocator] Updated field:', fieldName);
                    } else {
                        console.warn('[DealerLocator] Could not find field:', fieldName);
                    }
                });
            } else if (field && field.value) {
                field.value(value);
                console.log('[DealerLocator] Updated field:', fieldName);
            }
        },

        /**
         * On focus handler to prevent browser autocomplete
         */
        onFocus: function () {
            this._super();
            $('#' + this.uid).attr('autocomplete', 'new-password');
        }
    });
});