<?php
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
    $latitude = (float)$_POST['latitude'];
    $longitude = (float)$_POST['longitude'];
    
    $stmt = $conn->prepare("UPDATE users SET address = ?, pincode = ?, city = ?, state = ?, latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ssssddi", $address, $pincode, $city, $state, $latitude, $longitude, $user_id);
    
    if ($stmt->execute()) {
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
                <h5 class="mb-3">Search or Select Location on Map</h5>
                
                <!-- Search Box -->
                <div class="mb-3">
                    <input type="text" id="pac-input" class="form-control" placeholder="Search for your location...">
                </div>
                
                <!-- Map Container -->
                <div id="map" style="height: 400px; border-radius: 10px;"></div>
            </div>
            
            <!-- Location Form -->
            <div class="dashboard-card">
                <h5 class="mb-3">Confirm Delivery Address</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required maxlength="6">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo $user['latitude'] ?? '18.5204'; ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo $user['longitude'] ?? '73.8567'; ?>">
                    
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
                    <li>Search or click on the map</li>
                    <li>Confirm your delivery address</li>
                    <li>See products from nearby farmers</li>
                    <li>Place your order</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initMap" async defer></script>

<script>
let map;
let marker;
let autocomplete;

function initMap() {
    // Default location (Pune, India)
    const defaultLocation = {
        lat: parseFloat(document.getElementById('latitude').value) || 18.5204,
        lng: parseFloat(document.getElementById('longitude').value) || 73.8567
    };
    
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLocation,
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false
    });
    
    // Add marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP
    });
    
    // Initialize autocomplete
    const input = document.getElementById('pac-input');
    autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);
    
    // When place is selected from autocomplete
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry) {
            alert('No details available for: ' + place.name);
            return;
        }
        
        // Update map and marker
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(15);
        }
        
        marker.setPosition(place.geometry.location);
        updateFormFields(place);
    });
    
    // When marker is dragged
    marker.addListener('dragend', function() {
        const position = marker.getPosition();
        geocodePosition(position);
    });
    
    // When map is clicked
    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        geocodePosition(event.latLng);
    });
}

function geocodePosition(pos) {
    const geocoder = new google.maps.Geocoder();
    
    geocoder.geocode({location: pos}, function(results, status) {
        if (status === 'OK' && results[0]) {
            updateFormFields(results[0]);
        }
    });
}

function updateFormFields(place) {
    // Update coordinates
    document.getElementById('latitude').value = place.geometry.location.lat();
    document.getElementById('longitude').value = place.geometry.location.lng();
    
    // Update address
    document.getElementById('address').value = place.formatted_address || '';
    
    // Extract address components
    const components = place.address_components || [];
    
    components.forEach(component => {
        const types = component.types;
        
        if (types.includes('postal_code')) {
            document.getElementById('pincode').value = component.long_name;
        }
        
        if (types.includes('locality') || types.includes('administrative_area_level_2')) {
            document.getElementById('city').value = component.long_name;
        }
        
        if (types.includes('administrative_area_level_1')) {
            document.getElementById('state').value = component.long_name;
        }
    });
}

// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            map.setCenter(pos);
            marker.setPosition(pos);
            geocodePosition(pos);
        });
    }
}

// Add button to get current location
window.addEventListener('load', function() {
    const locationBtn = document.createElement('button');
    locationBtn.textContent = 'Use My Current Location';
    locationBtn.className = 'btn btn-sm btn-outline-primary mb-2';
    locationBtn.type = 'button';
    locationBtn.onclick = getCurrentLocation;
    
    const searchBox = document.getElementById('pac-input');
    searchBox.parentNode.insertBefore(locationBtn, searchBox);
});
</script>

<style>
#pac-input {
    margin-top: 10px;
}

#map {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.php'; ?>