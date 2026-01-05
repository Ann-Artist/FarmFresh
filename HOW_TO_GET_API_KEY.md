# How to Get Google Maps API Key - Step by Step

## Method 1: Quick Setup (5 minutes)

### Step 1: Go to Google Cloud Console
1. Visit: https://console.cloud.google.com/
2. Sign in with your Google account (Gmail account works)

### Step 2: Create or Select Project
1. Click on the project dropdown at the top
2. Click "New Project"
3. Enter project name: "FarmFresh Maps" (or any name)
4. Click "Create"
5. Wait a few seconds, then select your new project

### Step 3: Enable Required APIs
1. Go to "APIs & Services" → "Library" (in the left menu)
2. Search for "Maps JavaScript API" → Click it → Click "Enable"
3. Search for "Places API" → Click it → Click "Enable"
4. Search for "Geocoding API" → Click it → Click "Enable"

### Step 4: Create API Key
1. Go to "APIs & Services" → "Credentials" (in the left menu)
2. Click "Create Credentials" → "API Key"
3. Your API key will appear in a popup
4. **COPY THE KEY IMMEDIATELY** (you can't see it again easily)
5. Click "Close"

### Step 5: (Optional) Restrict API Key
1. Click on your newly created API key
2. Under "API restrictions", select "Restrict key"
3. Check: Maps JavaScript API, Places API, Geocoding API
4. Under "Application restrictions", select "HTTP referrers"
5. Add your website URL (e.g., `http://localhost/*` for local testing)
6. Click "Save"

### Step 6: Add Key to Your Project
1. Open `config/google_maps.php` in your project
2. Replace `YOUR_API_KEY` with the key you copied
3. Save the file

## Method 2: If You Don't Have a Google Account

### Option A: Create Free Google Account
1. Go to https://accounts.google.com/signup
2. Create a free Gmail account
3. Follow Method 1 above

### Option B: Use Free Alternative (No API Key Needed)
I've created an alternative solution using OpenStreetMap that works WITHOUT any API key!
- See the alternative implementation below
- Completely free
- No registration needed
- Works immediately

## Troubleshooting

### "Billing Required" Error
- Google requires a billing account BUT gives $200 free credit per month
- Most small projects never exceed the free tier
- You can add a credit card (won't be charged unless you exceed $200/month)
- Or use the free OpenStreetMap alternative

### "API Not Enabled" Error
- Make sure you enabled all 3 APIs: Maps JavaScript, Places, Geocoding
- Go back to "APIs & Services" → "Library" and enable them

### Key Not Working
- Make sure you copied the entire key (it's long)
- Check if APIs are enabled
- Wait 5 minutes after creating key (takes time to activate)
- Check browser console for specific error messages

## Free Alternative Solution

If you can't get the API key, I've created a version that uses OpenStreetMap (completely free, no API key needed). Check the alternative files I created.

