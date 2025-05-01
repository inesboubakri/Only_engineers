/**
 * Optimized OpenStreetMap location selector
 * This script provides an efficient way to search and select locations on a map
 */

// Function to initialize the map once Leaflet is available
function initializeMap() {
    console.log('Initializing map...');
    
    // Check if the map container exists
    const mapContainer = document.getElementById('location-map');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }
    
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet is not loaded');
        
        // Add a message to the container
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;">Map library not loaded. Please refresh the page.</div>';
        return;
    }
    
    console.log('Leaflet is loaded, creating map...');
    
    // Create loading indicator
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'map-loading';
    loadingIndicator.textContent = 'Loading map...';
    loadingIndicator.style.position = 'absolute';
    loadingIndicator.style.top = '50%';
    loadingIndicator.style.left = '50%';
    loadingIndicator.style.transform = 'translate(-50%, -50%)';
    loadingIndicator.style.background = 'rgba(255, 255, 255, 0.8)';
    loadingIndicator.style.padding = '10px 15px';
    loadingIndicator.style.borderRadius = '5px';
    loadingIndicator.style.zIndex = '1000';
    mapContainer.appendChild(loadingIndicator);
    
    try {
        // Initialize map with default view (centered on Europe)
        const map = L.map('location-map', {
            zoomControl: true,
            minZoom: 2,
            maxZoom: 18
        }).setView([48.8566, 2.3522], 4); // Default view on Paris
        
        console.log('Map created successfully');
        
        // Use a lightweight tile layer for better performance
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: ['a', 'b', 'c'],
            // Set these options for better performance
            tileSize: 256,
            updateWhenIdle: true,
            updateWhenZooming: false,
            keepBuffer: 2
        }).addTo(map);
        
        console.log('Tile layer added to map');
        
        // Remove loading indicator after tiles are loaded
        map.whenReady(function() {
            console.log('Map is ready');
            loadingIndicator.remove();
            
            // Force refresh the map container
            map.invalidateSize();
            
            // Try to get user's location for a better initial view
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const { latitude, longitude } = position.coords;
                    map.setView([latitude, longitude], 10);
                    console.log('Using user geolocation');
                }, function(error) {
                    console.log('Geolocation error:', error);
                }, {
                    timeout: 5000,
                    enableHighAccuracy: false
                });
            }
        });
        
        // Create marker for selected location
        let marker;
        
        // Add click event to select location
        map.on('click', function(e) {
            const { lat, lng } = e.latlng;
            console.log('Map clicked at:', lat, lng);
            
            // Update hidden form fields with coordinates
            const latField = document.getElementById('latitude');
            const lngField = document.getElementById('longitude');
            
            if (latField && lngField) {
                latField.value = lat.toFixed(6);
                lngField.value = lng.toFixed(6);
                console.log('Coordinates updated in form fields');
            }
            
            // Update or add marker
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
                console.log('Marker added to map');
            }
            
            // Get address for the selected location
            reverseGeocode(lat, lng);
        });
        
        // Add search functionality
        const locationInput = document.getElementById('location');
        if (locationInput) {
            console.log('Location input found');
            
            const searchButton = document.createElement('button');
            searchButton.type = 'button';
            searchButton.textContent = 'Search Location';
            searchButton.className = 'location-search-button';
            searchButton.style.marginTop = '5px';
            searchButton.style.padding = '8px 12px';
            searchButton.style.borderRadius = '4px';
            searchButton.style.border = '1px solid #4c6ef5';
            searchButton.style.backgroundColor = '#4c6ef5';
            searchButton.style.color = 'white';
            searchButton.style.cursor = 'pointer';
            
            // Insert the search button after the location input
            locationInput.parentNode.insertBefore(searchButton, locationInput.nextSibling);
            
            // Add click event to search button
            searchButton.addEventListener('click', function() {
                const searchTerm = locationInput.value.trim();
                if (searchTerm) {
                    console.log('Searching for location:', searchTerm);
                    geocode(searchTerm);
                }
            });
            
            // Also search when pressing Enter in the location input
            locationInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        console.log('Searching for location (via Enter):', searchTerm);
                        geocode(searchTerm);
                    }
                }
            });
        }
        
        // Function to geocode an address
        function geocode(address) {
            // Show loading indicator
            loadingIndicator.textContent = 'Searching...';
            loadingIndicator.style.display = 'block';
            mapContainer.appendChild(loadingIndicator);
            
            console.log('Geocoding address:', address);
            
            // Use Nominatim API for geocoding
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    // Hide loading indicator
                    loadingIndicator.remove();
                    
                    if (data && data.length > 0) {
                        const { lat, lon, display_name } = data[0];
                        console.log('Found location:', display_name, lat, lon);
                        
                        // Update map view
                        map.setView([lat, lon], 14);
                        
                        // Update hidden form fields
                        const latField = document.getElementById('latitude');
                        const lngField = document.getElementById('longitude');
                        
                        if (latField && lngField) {
                            latField.value = lat;
                            lngField.value = lon;
                        }
                        
                        // Update or add marker
                        if (marker) {
                            marker.setLatLng([lat, lon]);
                        } else {
                            marker = L.marker([lat, lon]).addTo(map);
                        }
                        
                        // Set location input value if it's not already set
                        if (locationInput && locationInput.value.trim() !== display_name) {
                            locationInput.value = display_name;
                        }
                    } else {
                        // Show no results found message
                        console.log('No locations found for:', address);
                        alert('No locations found for: ' + address);
                    }
                })
                .catch(error => {
                    loadingIndicator.remove();
                    console.error('Geocoding error:', error);
                    alert('Error searching for location. Please try again.');
                });
        }
        
        // Function to reverse geocode coordinates
        function reverseGeocode(lat, lng) {
            console.log('Reverse geocoding:', lat, lng);
            
            // Use Nominatim API for reverse geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        console.log('Found address:', data.display_name);
                        
                        // Set location input value
                        if (locationInput) {
                            locationInput.value = data.display_name;
                        }
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                });
        }
    } catch (error) {
        console.error('Error initializing map:', error);
        loadingIndicator.textContent = 'Error loading map';
        loadingIndicator.style.color = 'red';
    }
}

/**
 * Function to display a map with the given coordinates
 * @param {string|number} latitude - The latitude coordinate
 * @param {string|number} longitude - The longitude coordinate
 * @param {string} containerId - The ID of the container to display the map in
 */
function displayLocationMap(latitude, longitude, containerId = 'map-container') {
    console.log('Display location map:', latitude, longitude, containerId);
    
    // Get the container element
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Map container not found:', containerId);
        return;
    }
    
    // Check if we have valid coordinates
    if (!latitude || !longitude || isNaN(parseFloat(latitude)) || isNaN(parseFloat(longitude))) {
        console.warn('No valid coordinates provided');
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No location coordinates available for this hackathon.</div>';
        return;
    }
    
    // Parse coordinates to floats
    const lat = parseFloat(latitude);
    const lng = parseFloat(longitude);
    
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet library is not loaded');
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">Map library not loaded.</div>';
        return;
    }
    
    // Clear container and create a div for the map
    container.innerHTML = '<div id="location-details-map" style="height: 300px; width: 100%; border-radius: 8px;"></div>';
    
    // Initialize map
    const map = L.map('location-details-map').setView([lat, lng], 13);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker at the coordinates
    L.marker([lat, lng]).addTo(map);
    
    // Force a refresh of the map after rendering
    setTimeout(() => {
        map.invalidateSize();
    }, 100);
    
    console.log('Map displayed successfully');
}

// Check if document is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, checking for map containers');
        
        // Check for details map container
        const mapContainer = document.getElementById('map-container');
        if (mapContainer) {
            // Look for lat/long data attributes on the container
            const lat = mapContainer.getAttribute('data-latitude');
            const lng = mapContainer.getAttribute('data-longitude');
            
            if (lat && lng) {
                displayLocationMap(lat, lng);
            }
        }
    });
} else {
    // Document already loaded, initialize directly
    console.log('DOM already loaded, checking for map containers');
    
    // Check for details map container
    const mapContainer = document.getElementById('map-container');
    if (mapContainer) {
        // Look for lat/long data attributes on the container
        const lat = mapContainer.getAttribute('data-latitude');
        const lng = mapContainer.getAttribute('data-longitude');
        
        if (lat && lng) {
            displayLocationMap(lat, lng);
        }
    }
}