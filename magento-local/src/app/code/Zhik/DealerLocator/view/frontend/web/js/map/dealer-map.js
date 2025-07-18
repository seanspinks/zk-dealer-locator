/**
 * Dealer Map Component
 */
define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($, _, ko, Component, urlBuilder, storage, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Zhik_DealerLocator/map/dealer-map',
            map: null,
            markers: [],
            markerCluster: null,
            infoWindow: null,
            autocomplete: null,
            searchResults: ko.observableArray([]),
            selectedTags: ko.observableArray([]),
            availableTags: ko.observableArray([]),
            isLoading: ko.observable(false),
            searchQuery: ko.observable(''),
            searchRadius: ko.observable(50), // Default 50km as per PRD
            mapId: 'dealer-map-canvas',
            selectedLocationIndex: ko.observable(null),
            showGlobalView: ko.observable(false),
            hasSearched: ko.observable(false),
            mapLoadError: ko.observable(false),
            mapErrorMessage: ko.observable(''),
            mapInitialized: false,
            defaultLat: 40.7128,
            defaultLng: -74.0060,
            defaultZoom: 10,
            searchUrl: '',
            detailsUrl: '',
            geocodeUrl: '',
            defaultRadius: 50,
            mapStyle: [],
            clusterEnabled: true,
            clusterGridSize: 60,
            clusterMinSize: 3,
            clusterOptions: {
                gridSize: 60,
                maxZoom: 15,
                minimumClusterSize: 3,
                zoomOnClick: true,
                averageCenter: true,
                styles: [{
                    url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m1.png',
                    height: 53,
                    width: 53,
                    textColor: '#ffffff',
                    textSize: 11
                }, {
                    url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m2.png',
                    height: 56,
                    width: 56,
                    textColor: '#ffffff',
                    textSize: 12
                }, {
                    url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m3.png',
                    height: 66,
                    width: 66,
                    textColor: '#ffffff',
                    textSize: 13
                }]
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // Apply configuration from PHP
            if (this.defaultLat && this.defaultLng) {
                this.defaultLat = parseFloat(this.defaultLat);
                this.defaultLng = parseFloat(this.defaultLng);
            }
            if (this.defaultZoom) {
                this.defaultZoom = parseInt(this.defaultZoom);
            }
            if (this.defaultRadius) {
                this.searchRadius(parseInt(this.defaultRadius));
            }
            
            // Ensure URLs are set - force HTTPS to avoid redirect issues
            var baseUrl = 'https://' + window.location.host;
            if (!this.searchUrl) {
                this.searchUrl = baseUrl + '/dealerlocator/location/search/';
            }
            if (!this.detailsUrl) {
                this.detailsUrl = baseUrl + '/dealerlocator/location/details/';
            }
            if (!this.geocodeUrl) {
                this.geocodeUrl = baseUrl + '/dealerlocator/map/geocode/';
            }
            
            console.log('[DealerLocator] Initialized with URLs:', {
                searchUrl: this.searchUrl,
                geocodeUrl: this.geocodeUrl,
                defaultLocation: { lat: this.defaultLat, lng: this.defaultLng }
            });
            
            // Apply cluster configuration from admin
            if (typeof this.clusterEnabled !== 'undefined') {
                this.clusterEnabled = !!this.clusterEnabled;
            }
            if (this.clusterGridSize) {
                this.clusterOptions.gridSize = parseInt(this.clusterGridSize);
            }
            if (this.clusterMinSize) {
                this.clusterOptions.minimumClusterSize = parseInt(this.clusterMinSize);
            }
            
            // Track if we're in initial load phase
            this._initialLoad = true;
            
            // Subscribe to tag changes early
            this.selectedTags.subscribe((newTags) => {
                console.log('[DealerLocator] Selected tags changed:', newTags);
                console.log('[DealerLocator] Map initialized:', this.mapInitialized);
                console.log('[DealerLocator] Map object exists:', !!this.map);
                console.log('[DealerLocator] Initial load:', this._initialLoad);
                
                // Skip during initial load to prevent duplicate searches
                if (this._initialLoad) {
                    console.log('[DealerLocator] Skipping search during initial load');
                    return;
                }
                
                // Always perform search when tags change after initial load
                if (this.map) {
                    var center = this.map.getCenter();
                    if (center) {
                        console.log('[DealerLocator] Triggering automatic search after tag change');
                        console.log('[DealerLocator] Center:', { lat: center.lat(), lng: center.lng() });
                        this.searchNearby(center.lat(), center.lng());
                    } else {
                        console.warn('[DealerLocator] Map center not available, using default location');
                        this.searchNearby(this.defaultLat, this.defaultLng);
                    }
                } else {
                    console.log('[DealerLocator] Map not ready yet - deferring search');
                }
            });
            
            // Subscribe to global view toggle
            this.showGlobalView.subscribe((showGlobal) => {
                if (showGlobal && this.map) {
                    this.loadAllLocations();
                } else if (this.hasSearched() && this.map) {
                    var center = this.map.getCenter();
                    this.searchNearby(center.lat(), center.lng());
                }
            });
            
            // First load tags immediately
            this.loadAvailableTags();
            
            // Automatically search on load to show initial locations
            var self = this;
            
            // Wait for Google Maps to load
            this.waitForGoogleMaps().then(() => {
                this.initializeMap();
                this.initializeAutocomplete();
                this.bindEvents();
                
                // Perform initial search to show all locations
                setTimeout(function() {
                    console.log('[DealerLocator] Performing initial search...');
                    self.searchNearby(self.defaultLat, self.defaultLng);
                    
                    // Mark that initial load is complete
                    self.mapInitialized = true;
                    self._initialLoad = false;
                    console.log('[DealerLocator] Initial load complete, tag filtering now active');
                }, 500);
            }).catch((error) => {
                console.error('[DealerLocator] Failed to load Google Maps:', error);
                this.mapLoadError(true);
                this.mapErrorMessage(error.message || 'Failed to load Google Maps. Please check your API key configuration.');
            });
            
            return this;
        },

        /**
         * Wait for Google Maps API to load
         */
        waitForGoogleMaps: function () {
            return new Promise((resolve, reject) => {
                let attempts = 0;
                const maxAttempts = 100; // 10 seconds timeout
                
                const checkLibraries = () => {
                    // Check if there's a Google Maps error
                    if (window.gm_authFailure) {
                        reject(new Error('Google Maps authentication failed. Please check your API key and ensure it has the required permissions.'));
                        return true;
                    }
                    
                    return window.google && 
                           window.google.maps && 
                           window.markerClusterer;
                };
                
                if (checkLibraries()) {
                    resolve();
                } else {
                    const checkInterval = setInterval(() => {
                        attempts++;
                        
                        if (checkLibraries()) {
                            clearInterval(checkInterval);
                            resolve();
                        } else if (attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                            reject(new Error('Google Maps failed to load. Please ensure you have a valid API key configured.'));
                        }
                    }, 100);
                }
            });
        },

        /**
         * Initialize Google Map
         */
        initializeMap: function () {
            var self = this;
            
            console.log('Initializing Google Map with config:', {
                apiKeyPresent: !!this.apiKey,
                center: { lat: this.defaultLat, lng: this.defaultLng },
                zoom: this.defaultZoom
            });
            
            try {
                var mapOptions = {
                    center: { lat: this.defaultLat, lng: this.defaultLng },
                    zoom: this.defaultZoom,
                    mapTypeControl: false,
                    fullscreenControl: false,
                    streetViewControl: false
                };
                
                // Apply custom map styles if configured
                console.log('[DealerLocator] Map style config:', this.mapStyle);
                if (this.mapStyle && Array.isArray(this.mapStyle) && this.mapStyle.length > 0) {
                    mapOptions.styles = this.mapStyle;
                    console.log('[DealerLocator] Applied custom map style with', this.mapStyle.length, 'rules');
                } else {
                    console.log('[DealerLocator] No custom map style configured');
                }
                
                this.map = new google.maps.Map(
                    document.getElementById(this.mapId),
                    mapOptions
                );
                
                // Listen for authentication errors
                window.gm_authFailure = function() {
                    self.mapLoadError(true);
                    self.mapErrorMessage('Google Maps authentication failed. Please check your API key configuration.');
                };

            this.infoWindow = new google.maps.InfoWindow();
            
            // Initialize marker clusterer only if enabled
            if (this.clusterEnabled) {
                this.markerCluster = new markerClusterer.MarkerClusterer({
                    map: this.map,
                    markers: [],
                    ...this.clusterOptions
                });
            }
            
            } catch (error) {
                console.error('Failed to initialize map:', error);
                this.mapLoadError(true);
                this.mapErrorMessage('Failed to initialize the map. ' + error.message);
                return;
            }
            
            // Try to get user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        var pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        self.map.setCenter(pos);
                        self.searchNearby(pos.lat, pos.lng);
                        // Mark initial load complete after geolocation search
                        self.mapInitialized = true;
                        self._initialLoad = false;
                        console.log('[DealerLocator] Initial load complete after geolocation');
                    },
                    function () {
                        // Location access denied or error - try IP geolocation
                        console.log('Browser geolocation failed, trying IP geolocation');
                        self.getIpLocation();
                    }
                );
            } else {
                // Browser doesn't support geolocation - try IP geolocation
                this.getIpLocation();
            }
            
            // Listen for map idle event to update results based on viewport
            google.maps.event.addListener(this.map, 'idle', function() {
                if (self.hasSearched() && !self.showGlobalView()) {
                    self.updateVisibleResults();
                }
            });
        },

        /**
         * Initialize Google Places Autocomplete
         */
        initializeAutocomplete: function () {
            var self = this;
            var input = document.getElementById('map-search-input');
            
            if (!input) {
                setTimeout(() => this.initializeAutocomplete(), 100);
                return;
            }
            
            this.autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });
            
            // Bind autocomplete to map bounds
            this.autocomplete.bindTo('bounds', this.map);
            
            // Listen for place selection
            this.autocomplete.addListener('place_changed', function () {
                var place = self.autocomplete.getPlace();
                
                if (!place.geometry) {
                    console.error('No location data for this place');
                    return;
                }
                
                // Update map center and search
                self.map.setCenter(place.geometry.location);
                self.map.setZoom(12);
                self.searchNearby(
                    place.geometry.location.lat(),
                    place.geometry.location.lng()
                );
            });
        },

        /**
         * Bind DOM events
         */
        bindEvents: function () {
            // Enter key in search already handled by knockout binding
        },

        /**
         * Load available tags
         */
        loadAvailableTags: function () {
            var self = this;
            
            $.ajax({
                url: 'https://' + window.location.host + '/dealerlocator/tag/lists/',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('[DealerLocator] Tags response:', response);
                    if (response && response.items) {
                        // Transform tag items to include display names
                        var tags = response.items.map(function(tag) {
                            return {
                                id: parseInt(tag.tag_id, 10),
                                name: tag.name,
                                code: tag.code
                            };
                        });
                        self.availableTags(tags);
                        console.log('[DealerLocator] Available tags loaded:', tags);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('[DealerLocator] Failed to load tags:', error);
                    console.error('[DealerLocator] Response:', xhr.responseText);
                }
            });
        },

        /**
         * Perform search
         */
        performSearch: function () {
            var query = this.searchQuery();
            
            if (!query) {
                return;
            }
            
            this.isLoading(true);
            
            // Use Google Geocoding to get coordinates
            var geocoder = new google.maps.Geocoder();
            var self = this;
            
            geocoder.geocode({ 'address': query }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    var location = results[0].geometry.location;
                    self.map.setCenter(location);
                    self.searchNearby(location.lat(), location.lng());
                } else {
                    self.isLoading(false);
                    alert($t('Location not found. Please try a different search.'));
                }
            });
        },

        /**
         * Search for dealers near coordinates
         */
        searchNearby: function (lat, lng) {
            var self = this,
                // Ensure tag IDs are numbers
                tagIds = this.selectedTags().map(function(id) {
                    return typeof id === 'string' ? parseInt(id, 10) : id;
                }),
                searchData = {
                    lat: lat,
                    lng: lng,
                    radius: this.showGlobalView() ? 0 : this.searchRadius(),
                    tags: tagIds
                };

            this.hasSearched(true);
            this.isLoading(true);
            
            console.log('[DealerLocator] Searching with data:', searchData);
            console.log('[DealerLocator] Search URL:', this.searchUrl);
            console.log('[DealerLocator] Original selected tag IDs:', this.selectedTags());
            console.log('[DealerLocator] Converted tag IDs:', tagIds);
            console.log('[DealerLocator] Available tags:', this.availableTags());
            
            storage.post(
                this.searchUrl,
                JSON.stringify(searchData)
            ).done(function (response) {
                console.log('[DealerLocator] Search response:', response);
                self.isLoading(false);
                self.processSearchResults(response, lat, lng);
            }).fail(function (xhr, status, error) {
                console.error('[DealerLocator] Search failed:', error);
                console.error('[DealerLocator] Response:', xhr.responseText);
                self.isLoading(false);
                alert($t('Search failed. Please try again.'));
            });
        },

        /**
         * Get location using IP geolocation
         */
        getIpLocation: function () {
            var self = this;
            
            $.ajax({
                url: this.geocodeUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.location) {
                        var pos = {
                            lat: response.location.latitude,
                            lng: response.location.longitude
                        };
                        self.map.setCenter(pos);
                        self.searchNearby(pos.lat, pos.lng);
                    } else {
                        console.log('IP geolocation failed:', response.message);
                        // Use default location or do nothing
                    }
                    // Mark initial load complete after IP geolocation
                    self.mapInitialized = true;
                    self._initialLoad = false;
                    console.log('[DealerLocator] Initial load complete after IP geolocation');
                },
                error: function () {
                    console.log('IP geolocation request failed');
                    // Mark initial load complete even on error
                    self.mapInitialized = true;
                    self._initialLoad = false;
                    console.log('[DealerLocator] Initial load complete after IP geolocation error');
                }
            });
        },

        /**
         * Process search results
         */
        processSearchResults: function (results, centerLat, centerLng) {
            var self = this;
            
            console.log('[DealerLocator] Processing search results:', results);
            console.log('[DealerLocator] Results count:', results ? results.length : 0);

            // Clear existing markers
            this.clearMarkers();

            // Calculate distances
            _.each(results, function (location) {
                if (centerLat && centerLng && location.latitude && location.longitude) {
                    location.distance = self.calculateDistance(
                        centerLat,
                        centerLng,
                        parseFloat(location.latitude),
                        parseFloat(location.longitude)
                    );
                }
            });

            // Sort by distance
            results.sort(function (a, b) {
                return (a.distance || 0) - (b.distance || 0);
            });

            // Update results in sidebar
            this.searchResults(results);

            // Add markers to map
            var newMarkers = [];
            _.each(results, function (location, index) {
                if (location.latitude && location.longitude) {
                    var marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(location.latitude),
                            lng: parseFloat(location.longitude)
                        },
                        title: location.name,
                        animation: google.maps.Animation.DROP
                    });
                    
                    // Store reference to location data
                    marker.locationData = location;
                    marker.index = index;
                    
                    // Add click listener
                    marker.addListener('click', function () {
                        self.showInfoWindow(marker, location);
                        self.selectedLocationIndex(index);
                    });
                    
                    self.markers.push(marker);
                    newMarkers.push(marker);
                }
            });
            
            // Add all markers to cluster at once for better performance
            if (this.clusterEnabled && this.markerCluster && newMarkers.length > 0) {
                this.markerCluster.addMarkers(newMarkers);
            } else if (!this.clusterEnabled) {
                // If clustering is disabled, add markers directly to map
                _.each(newMarkers, function(marker) {
                    marker.setMap(self.map);
                });
            }

            // Adjust map bounds to show all markers
            if (results.length > 0) {
                this.fitMapToBounds();
            } else {
                // Center on search location
                this.map.setCenter({ lat: centerLat, lng: centerLng });
                this.map.setZoom(12);
            }
        },

        /**
         * Load all locations (global view)
         */
        loadAllLocations: function () {
            this.searchRadius(0); // 0 means no radius limit
            if (this.map) {
                var center = this.map.getCenter();
                this.searchNearby(center.lat(), center.lng());
            }
        },

        /**
         * Toggle global view
         */
        toggleGlobalView: function () {
            this.showGlobalView(!this.showGlobalView());
        },


        /**
         * Show info window
         */
        showInfoWindow: function (marker, location) {
            var content = this.buildInfoWindowContent(location);
            this.infoWindow.setContent(content);
            this.infoWindow.open(this.map, marker);
        },

        /**
         * Build info window content
         */
        buildInfoWindowContent: function (location) {
            var content = '<div class="map-info-window">';
            content += '<h4>' + _.escape(location.name) + '</h4>';
            content += '<p>' + _.escape(location.address) + '<br>';
            content += _.escape(location.city + ', ' + location.state + ' ' + location.zip) + '</p>';
            
            if (location.phone) {
                content += '<p><a href="tel:' + _.escape(location.phone) + '" class="info-phone">' + _.escape(location.phone) + '</a></p>';
            }
            
            if (location.website) {
                content += '<p><a href="' + _.escape(location.website) + '" target="_blank" class="info-website">' + $t('Visit Website') + '</a></p>';
            }
            
            content += '<div class="info-actions">';
            content += '<a href="https://www.google.com/maps/dir/?api=1&destination=' + 
                       encodeURIComponent(location.address + ', ' + location.city + ', ' + location.state + ' ' + location.zip) + 
                       '" target="_blank" class="info-directions">';
            content += '<svg class="icon" width="16" height="16"><use xlink:href="#icon-directions"></use></svg> ';
            content += $t('Get Directions') + '</a>';
            content += '</div>';
            
            content += '</div>';
            
            return content;
        },

        /**
         * Clear all markers
         */
        clearMarkers: function () {
            // Remove markers from cluster
            if (this.clusterEnabled && this.markerCluster) {
                this.markerCluster.clearMarkers();
            } else {
                // Clear individual markers
                _.each(this.markers, function (marker) {
                    marker.setMap(null);
                });
            }
            
            this.markers = [];
        },

        /**
         * Fit map to show all markers
         */
        fitMapToBounds: function () {
            var bounds = new google.maps.LatLngBounds();
            
            _.each(this.markers, function (marker) {
                bounds.extend(marker.getPosition());
            });
            
            this.map.fitBounds(bounds);
            
            // Don't zoom in too far for single marker
            if (this.markers.length === 1) {
                this.map.setZoom(Math.min(this.map.getZoom(), 15));
            }
        },

        /**
         * Update visible results based on map viewport
         */
        updateVisibleResults: function () {
            var bounds = this.map.getBounds();
            if (!bounds) return;
            
            var visibleResults = [];
            
            _.each(this.searchResults(), function (location) {
                if (location.latitude && location.longitude) {
                    var position = new google.maps.LatLng(
                        parseFloat(location.latitude),
                        parseFloat(location.longitude)
                    );
                    
                    if (bounds.contains(position)) {
                        visibleResults.push(location);
                    }
                }
            });
            
            // Update the display to show only visible results
            // For now, we'll keep showing all results but this could be enhanced
        },

        /**
         * Select location from list
         */
        selectLocation: function (index) {
            var marker = this.markers[index];
            if (marker) {
                // Center on marker and zoom in enough to break cluster
                this.map.setCenter(marker.getPosition());
                this.map.setZoom(16); // Zoom past cluster max zoom
                
                // Wait for map to settle then trigger click
                var self = this;
                google.maps.event.addListenerOnce(this.map, 'idle', function() {
                    google.maps.event.trigger(marker, 'click');
                });
            }
        },

        /**
         * Use my location
         */
        useMyLocation: function () {
            var self = this;
            this.isLoading(true);
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        var pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        self.map.setCenter(pos);
                        self.searchNearby(pos.lat, pos.lng);
                    },
                    function () {
                        // Fall back to IP geolocation
                        self.getIpLocation();
                    }
                );
            } else {
                // Fall back to IP geolocation
                this.getIpLocation();
            }
        },

        /**
         * Get directions
         */
        getDirections: function (location) {
            var url = 'https://www.google.com/maps/dir/?api=1&destination=' + 
                      encodeURIComponent(location.address + ', ' + location.city + ', ' + location.state + ' ' + location.zip);
            window.open(url, '_blank');
        },

        /**
         * Handle search on enter key
         */
        searchOnEnter: function (data, event) {
            if (event.keyCode === 13) {
                this.performSearch();
                return false;
            }
            return true;
        },

        /**
         * Calculate distance between two coordinates
         */
        calculateDistance: function (lat1, lon1, lat2, lon2) {
            var R = 3959; // Radius of Earth in miles
            var dLat = this.toRad(lat2 - lat1);
            var dLon = this.toRad(lon2 - lon1);
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },

        /**
         * Convert degrees to radians
         */
        toRad: function (degrees) {
            return degrees * (Math.PI / 180);
        },

        /**
         * Get URL with base path
         */
        getUrl: function (path) {
            return urlBuilder.build(path);
        },
        
        /**
         * Reload the page
         */
        reloadPage: function () {
            window.location.reload();
        },
        
        /**
         * Update cluster options
         */
        updateClusterOptions: function (options) {
            if (this.markerCluster) {
                // Update cluster options dynamically
                _.extend(this.clusterOptions, options);
                
                // Recreate cluster with new options
                var markers = this.markerCluster.getMarkers();
                this.markerCluster.clearMarkers();
                this.markerCluster = new markerClusterer.MarkerClusterer({
                    map: this.map,
                    markers: markers,
                    ...this.clusterOptions
                });
            }
        }
    });
});