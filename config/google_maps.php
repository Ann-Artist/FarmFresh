<?php
// Google Maps API Configuration
// Replace 'YOUR_API_KEY' with your actual Google Maps API key
// Get your API key from: https://console.cloud.google.com/google/maps-apis
// 
// IMPORTANT: If you don't have an API key, you can:
// 1. Use the free alternative: select_location_free.php (uses OpenStreetMap, no API key needed)
// 2. Or follow instructions in HOW_TO_GET_API_KEY.md
//
define('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY');

// Check if API key is set
define('HAS_GOOGLE_MAPS_KEY', GOOGLE_MAPS_API_KEY !== 'YOUR_API_KEY' && !empty(GOOGLE_MAPS_API_KEY));

// Note: Make sure to enable these APIs in Google Cloud Console:
// - Maps JavaScript API
// - Places API
// - Geocoding API
?>

