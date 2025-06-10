// Geocode address
function openCageGeocode(address) {
  return new Promise((resolve) => {
    if (geocodeCache[address]) {
      resolve(geocodeCache[address]);
      return;
    }

    fetch(
      `https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(
        address
      )}&key=${apiKey}`
    )
      .then((response) => {
        if (!response.ok)
          throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        updateRateLimit(data);
        if (data.results && data.results[0]) {
          const coords = [
            data.results[0].geometry.lat,
            data.results[0].geometry.lng,
          ];
          geocodeCache[address] = coords;
          resolve(coords);
        } else {
          resolve(null);
        }
      })
      .catch((error) => {
        console.error("Geocoding error:", error);
        resolve(null);
      });
  });
}

// Function to get geolocation with exact matching
function DataBaseGeolocation(address) {
  // Clean and normalize the address
  let cleanedAddress = address.trim().toLowerCase();

  return fetch(`/static/geocode.php?address=${encodeURIComponent(cleanedAddress)}`)
    .then((response) => response.text())
    .then((text) => {
      try {
        const data = JSON.parse(text);

        // Check for exact match
        if (data.lat && data.lon) {
          return {
            lat: data.lat,
            lon: data.lon,
            source: data.source,
          };
        }

        // Check for fuzzy matches
        if (data.fuzzy_matches && data.fuzzy_matches.length > 0) {
          // Use the first fuzzy match
          const match = data.fuzzy_matches[0];
          return {
            lat: match.lat,
            lon: match.lon,
            source: data.source,
            address_full: match.address_full,
            match_type: data.match_type || "fuzzy"
          };
        }

        console.error("Geolocation failed:", data.error);
        return null;
      } catch (e) {
        console.error("JSON parsing error:", e);
        console.error("Response text:", text);
        return null;
      }
    })
    .catch((error) => {
      console.error("Error fetching data:", error);
      return null;
    });
}

// Reverse geocode coordinates
function reverseGeocode(lat, lng, callback) {
  const url = `https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lng}&key=${apiKey}`;

  fetch(url)
    .then((response) => {
      if (!response.ok)
        throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      if (data.results.length > 0) {
        callback(data.results[0].formatted);
      } else {
        callback(null);
      }
    })
    .catch((error) => {
      console.error("Reverse geocoding error:", error);
      callback(null);
    });
}

function updateRateLimit(data) {
  if (data.rate) {
    const rateElement = document.getElementById("rate-limit");
    const resetElement = document.getElementById("rate-reset");

    // Update remaining calls
    rateElement.textContent = `API Calls: ${data.rate.remaining}/${data.rate.limit}`;

    // Calculate and display reset time
    const resetDate = new Date(data.rate.reset * 1000);
    const resetTime = resetDate.toLocaleTimeString();
    resetElement.textContent = `Resets: ${resetTime}`;
  }
}