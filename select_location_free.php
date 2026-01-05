<?php
// FREE VERSION - No API Key Required!
// Uses OpenStreetMap and browser geolocation
$page_title = "Select Delivery Location";
include 'includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getCurrentUser($conn);

// Handle location save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_location'])) {
    $address = clean($_POST['address']);
    $pincode = clean($_POST['pincode']);
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    $latitude = isset($_POST['latitude']) ? clean($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? clean($_POST['longitude']) : null;
    
    $stmt = $conn->prepare("UPDATE users SET address = ?, pincode = ?, city = ?, state = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $address, $pincode, $city, $state, $user_id);
    
    if ($stmt->execute()) {
        if ($latitude && $longitude) {
            $lat_stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
            $lat_stmt->bind_param("ddi", $latitude, $longitude, $user_id);
            $lat_stmt->execute();
        }
        
        header('Location: customer/checkout.php?location_updated=1');
        exit();
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-map-marker-alt text-success"></i> Select Delivery Location
    </h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-map"></i> Select Your Location
                </h5>
                
                <!-- Get Current Location Button -->
                <div class="mb-3">
                    <button type="button" id="getCurrentLocation" class="btn btn-success mb-2">
                        <i class="fas fa-crosshairs"></i> Use My Current Location
                    </button>
                    <span id="locationStatus" class="ms-2 text-muted"></span>
                </div>
                
                <!-- Map Container -->
                <div id="map" style="height: 400px; border-radius: 10px; width: 100%;"></div>
                <small class="text-muted">Click on the map to select your location</small>
            </div>
            
            <!-- Location Form -->
            <div class="dashboard-card">
                <h5 class="mb-3">Confirm Delivery Address</h5>
                
                <form method="POST" id="locationForm">
                    <div class="mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" 
                                   value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" 
                                   required maxlength="6" pattern="[0-9]{6}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State *</label>
                            <select class="form-control" id="state" name="state" required>
                                <option value="">Select State</option>
                                <?php
                                $states = ['Andhra Pradesh', 'Bihar', 'Chhattisgarh', 'Delhi', 'Goa', 'Gujarat', 
                                          'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 
                                          'Madhya Pradesh', 'Maharashtra', 'Odisha', 'Punjab', 'Rajasthan', 
                                          'Tamil Nadu', 'Telangana', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'];
                                foreach ($states as $state) {
                                    $selected = ($user['state'] ?? '') === $state ? 'selected' : '';
                                    echo "<option value='$state' $selected>$state</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo $user['latitude'] ?? ''; ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo $user['longitude'] ?? ''; ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Products will be shown from farmers within 50 km of your location.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="save_location" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-check"></i> Confirm Location & Continue
                        </button>
                        <a href="customer/cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Sidebar -->
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="fas fa-truck"></i> Delivery Information</h5>
                
                <div class="mb-3">
                    <h6>Delivery Radius</h6>
                    <p class="text-muted small">We deliver within 50 km of your selected location</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Time</h6>
                    <p class="text-muted small">2-3 business days for standard delivery</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Charges</h6>
                    <p class="text-muted small">
                        Free delivery on orders above ₹500<br>
                        ₹50 for orders below ₹500
                    </p>
                </div>
                
                <hr>
                
                <h6>How It Works</h6>
                <ol class="small text-muted ps-3">
                    <li>Click "Use My Current Location"</li>
                    <li>Or click on the map to select location</li>
                    <li>Enter your address details</li>
                    <li>Confirm and continue</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Nominatim for reverse geocoding (free, no API key) -->
<script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />

<script>
let map;
let marker;
let geocoder;
let currentLocation = null;

// Initialize map using OpenStreetMap (FREE, no API key needed)
function initMap() {
    // Default location (Pune, India)
    const defaultLocation = [18.5204, 73.8567];
    
    // Try to get user's saved location
    const savedLat = parseFloat(document.getElementById('latitude').value);
    const savedLng = parseFloat(document.getElementById('longitude').value);
    const center = (savedLat && savedLng) ? [savedLat, savedLng] : defaultLocation;
    
    // Create map
    map = L.map('map').setView(center, 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add geocoder control for searching
    geocoder = L.Control.Geocoder.nominatim();
    
    // Create marker
    marker = L.marker(center, { draggable: true }).addTo(map);
    
    // When marker is dragged
    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        updateAddressFromLocation(pos.lat, pos.lng);
    });
    
    // When map is clicked
    map.on('click', function(event) {
        const pos = event.latlng;
        marker.setLatLng(pos);
        updateAddressFromLocation(pos.lat, pos.lng);
    });
    
    // Update address on initial load if coordinates exist
    if (savedLat && savedLng) {
        updateAddressFromLocation(savedLat, savedLng);
    }
}

// Get current location using browser geolocation
function getCurrentLocation() {
    const statusElement = document.getElementById('locationStatus');
    statusElement.textContent = 'Getting your location...';
    statusElement.className = 'ms-2 text-info';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                currentLocation = [lat, lng];
                map.setView(currentLocation, 17);
                marker.setLatLng(currentLocation);
                
                updateAddressFromLocation(lat, lng);
                
                statusElement.textContent = 'Location found!';
                statusElement.className = 'ms-2 text-success';
                setTimeout(() => {
                    statusElement.textContent = '';
                }, 3000);
            },
            function(error) {
                let errorMsg = 'Unable to get your location. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Please allow location access.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Location information unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Location request timed out.';
                        break;
                    default:
                        errorMsg += 'An unknown error occurred.';
                        break;
                }
                statusElement.textContent = errorMsg;
                statusElement.className = 'ms-2 text-danger';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        statusElement.textContent = 'Geolocation is not supported by your browser.';
        statusElement.className = 'ms-2 text-danger';
    }
}

// Update address fields from coordinates using Nominatim (free reverse geocoding)
function updateAddressFromLocation(lat, lng) {
    // Update hidden coordinates
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    // Use Nominatim for reverse geocoding (free, no API key)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.address) {
                const addr = data.address;
                let address = data.display_name || '';
                let pincode = addr.postcode || '';
                let city = addr.city || addr.town || addr.village || addr.county || '';
                let state = addr.state || '';
                
                // Update form fields
                document.getElementById('address').value = address;
                if (pincode) document.getElementById('pincode').value = pincode;
                if (city) document.getElementById('city').value = city;
                if (state) {
                    const stateSelect = document.getElementById('state');
                    const options = stateSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].text === state || options[i].value === state) {
                            stateSelect.value = options[i].value;
                            break;
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            // Still update coordinates even if address lookup fails
        });
}

// Event listener for current location button
document.getElementById('getCurrentLocation').addEventListener('click', getCurrentLocation);

// Initialize map when page loads
window.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>

<?php include 'includes/footer.php'; ?>

