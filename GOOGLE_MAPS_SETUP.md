# Google Maps API Setup Guide

This project now includes Google Maps integration for fetching user's current location and selecting delivery addresses.

## Features

1. **Current Location Detection**: Automatically fetch user's GPS location using browser geolocation API
2. **Interactive Map**: Users can search, click, or drag markers to select their location
3. **Address Autocomplete**: Google Places API for searching locations
4. **Auto-fill Address**: Automatically fills address fields from selected location

## Setup Instructions

### Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - **Maps JavaScript API**
   - **Places API**
   - **Geocoding API**
4. Go to "Credentials" → "Create Credentials" → "API Key"
5. Copy your API key
6. (Optional but recommended) Restrict the API key to your domain

### Step 2: Configure API Key

1. Open `config/google_maps.php`
2. Replace `YOUR_API_KEY` with your actual Google Maps API key:

```php
define('GOOGLE_MAPS_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE');
```

### Step 3: Update Database (Optional)

If you want to store latitude/longitude coordinates, run the SQL script:

```sql
-- Run add_location_columns.sql or execute:
ALTER TABLE users 
ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER address,
ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude;
```

Note: The application will work without these columns, but coordinates won't be saved.

## Usage

### For Customers

1. **Select Location Page** (`select_location.php`):
   - Click "Use My Current Location" to automatically detect location
   - Or search for a location in the search box
   - Or click/drag on the map to select location
   - Address fields will auto-fill
   - Click "Confirm Location & Continue"

2. **Checkout Page** (`customer/checkout.php`):
   - Click "Use My Current Location" to detect location
   - Or click "Select on Map" to go to the location selection page
   - Or search for location in the search box
   - Address will auto-fill in the delivery address field

## Browser Permissions

- Users need to allow location access when prompted
- The browser will ask for permission the first time
- If denied, users can manually search or select location on the map

## Troubleshooting

### Map not loading
- Check if API key is correctly set in `config/google_maps.php`
- Verify APIs are enabled in Google Cloud Console
- Check browser console for errors

### Location not detected
- Ensure browser supports geolocation (modern browsers do)
- Check if user granted location permission
- Verify HTTPS is enabled (required for geolocation on some browsers)

### Address not auto-filling
- Check if Geocoding API is enabled
- Verify API key has proper permissions
- Check browser console for errors

## API Costs

Google Maps API has a free tier:
- Maps JavaScript API: $200 free credit per month
- Geocoding API: $200 free credit per month
- Places API: $200 free credit per month

For most small to medium applications, the free tier is sufficient.

## Security Notes

1. **Restrict API Key**: In Google Cloud Console, restrict your API key to:
   - HTTP referrers (your domain)
   - Specific APIs (Maps JavaScript, Places, Geocoding)

2. **Don't commit API key**: Add `config/google_maps.php` to `.gitignore` if it contains your actual key

3. **Use environment variables**: For production, consider using environment variables instead of hardcoding the key

