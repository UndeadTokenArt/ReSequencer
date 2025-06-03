let tabCount = 1;
var selectedMarkers = [];
const geocodeCache = {};

var greenIcon = L.icon({
  iconUrl: "../static/greendot.svg",
  iconSize: [60, 60],
  iconAnchor: [30, 30],
  popupAnchor: [1, -34],
});

// Prompt for API key on page load and store it
let apiKey = sessionStorage.getItem("apiKey");
if (!apiKey) {
  apiKey = prompt("Please enter your OpenCage API key:");
  if (apiKey && apiKey.match(/^[a-zA-Z0-9]{32}$/)) {
    sessionStorage.setItem("apiKey", apiKey);
  } else {
    alert("Invalid or missing API key. Geolocation features will not work.");
  }
}

const sortable = document.getElementById("sortable-list");

// Map initialization
let map;

function initMap() {
  map = L.map("map").setView([45.5231, -122.5], 12);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  // Event listener to add a marker by clicking on the map
  map.on("click", function (e) {
    var coords = [e.latlng.lat, e.latlng.lng];
    var marker = L.marker(coords, { icon: greenIcon }).addTo(map);
    reverseGeocode(e.latlng.lat, e.latlng.lng, (address) => {
      marker.address = address
        ? address
        : `Lat: ${coords[0]}, Lng: ${coords[1]}`;
      marker.on("click", function () {
        toggleMarkerSelection(marker);
      });
      selectedMarkers.push(marker);
      displaySelectedAddresses();
    });
  });
}

// Event listener to select multiple items in the list with CTRL or Shift
document.getElementById("sortable-list").addEventListener("click", (e) => {
  if (e.target.tagName === "LI") {
    if (e.ctrlKey || e.metaKey) {
      // Ctrl (or Cmd on Mac) + Click allows multiple selection
      e.target.classList.toggle("selected");
    } else if (e.shiftKey) {
      // Shift + Click selects a range of items
      const items = [...document.querySelectorAll("#sortable-list li")];
      const firstIndex = items.findIndex((item) =>
        item.classList.contains("selected")
      );
      const lastIndex = items.indexOf(e.target);

      if (firstIndex !== -1) {
        const range = items.slice(
          Math.min(firstIndex, lastIndex),
          Math.max(firstIndex, lastIndex) + 1
        );
        range.forEach((item) => item.classList.add("selected"));
      }
    } else {
      // Normal click (clears previous selection)
      document
        .querySelectorAll("#sortable-list li.selected")
        .forEach((item) => item.classList.remove("selected"));
      e.target.classList.add("selected");
    }
  }
});

let draggedItems = [];

document.getElementById("sortable-list").addEventListener("dragstart", (e) => {
  if (e.target.classList.contains("selected")) {
    draggedItems = [...document.querySelectorAll("#sortable-list li.selected")];
  } else {
    draggedItems = [e.target];
    document
      .querySelectorAll("#sortable-list li.selected")
      .forEach((item) => item.classList.remove("selected"));
    e.target.classList.add("selected");
  }
  e.dataTransfer.setData("text/plain", ""); // Required for Firefox compatibility
});

document.getElementById("sortable-list").addEventListener("dragover", (e) => {
  e.preventDefault();
  const afterElement = getInsertionPoint(e.clientY);
  if (afterElement) {
    sortable.insertBefore(draggedItems[0], afterElement);
  } else {
    sortable.appendChild(draggedItems[0]);
  }
});

document.getElementById("sortable-list").addEventListener("drop", (e) => {
  e.preventDefault();
  const afterElement = getInsertionPoint(e.clientY);

  if (afterElement) {
    draggedItems.forEach((item) => sortable.insertBefore(item, afterElement));
  } else {
    draggedItems.forEach((item) => sortable.appendChild(item));
  }

  draggedItems = [];
});

// Initialize the map as soon as the page loads
document.addEventListener("DOMContentLoaded", () => {
  initMap();
});

function updateSelectedMarkersFromList() {
  const sortableList = document.getElementById("sortable-list");
  const newOrder = Array.from(sortableList.children).map(
    (item) => item.textContent
  );

  selectedMarkers.sort((a, b) => {
    return newOrder.indexOf(a.address) - newOrder.indexOf(b.address);
  });
}

function getInsertionPoint(y) {
  const draggableElements = [
    ...document.querySelectorAll("#sortable-list li:not(.dragging)"),
  ];

  return draggableElements.reduce(
    (closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;

      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    },
    { offset: Number.NEGATIVE_INFINITY }
  ).element;
}

// centers the map on the selected markers from the list
function centerMapOnSelectedMarkers() {
  if (selectedMarkers.length === 0) {
    console.warn("No markers selected.");
    return;
  }

  let totalLat = 0;
  let totalLng = 0;

  selectedMarkers.forEach((marker) => {
    totalLat += marker.getLatLng().lat;
    totalLng += marker.getLatLng().lng;
  });

  const avgLat = totalLat / selectedMarkers.length;
  const avgLng = totalLng / selectedMarkers.length;

  map.setView([avgLat, avgLng], 12); // 12 is a reasonable zoom level
}

// Add this event listener to your map.js file
document.getElementById("center-map-btn").addEventListener("click", () => {
  centerMapOnSelectedMarkers();
});

// Drag-and-drop functionality for the sortable list
let draggedItem = null;

sortable.addEventListener("dragstart", (e) => {
  draggedItem = e.target;
  e.dataTransfer.setData("text/plain", "");
  draggedItem.classList.add("dragging");
});

sortable.addEventListener("dragover", (e) => {
  e.preventDefault(); // Necessary to allow the drop

  const afterElement = getInsertionPoint(e.clientY); // Find where to insert the item
  if (afterElement == null) {
    // If no element is found (e.g., dragged below the last item), append to the end
    sortable.appendChild(draggedItem);
  } else {
    // Otherwise, insert before the found element
    sortable.insertBefore(draggedItem, afterElement);
  }
});

sortable.addEventListener("drop", (e) => {
  e.preventDefault();

  if (
    draggedItem &&
    e.target !== draggedItem &&
    !e.target.contains(draggedItem)
  ) {
    // Do nothing here; the user will click the update button manually.
  }

  draggedItem.classList.remove("dragging"); // Optional: Remove dragging class
  draggedItem = null;
});

sortable.addEventListener("click", (e) => {
  if (e.target.classList.contains("delete-btn")) {
    const markerIndex = e.target.parentNode.dataset.markerIndex;
    if (markerIndex !== undefined) {
      const index = parseInt(markerIndex, 10);
      const marker = selectedMarkers[index];

      // Remove marker from map
      map.removeLayer(marker);

      // Remove marker from selectedMarkers array
      selectedMarkers.splice(index, 1);

      // Update the list display
      displaySelectedAddresses();
    }
  }
});

// Get the target element after which the dragged item should be inserted
function getInsertionPoint(y) {
  const draggableElements = [
    ...document.querySelectorAll("#sortable-list li:not(.dragging)"),
  ];

  return draggableElements.reduce(
    (closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;

      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    },
    { offset: Number.NEGATIVE_INFINITY }
  ).element;
}

// Drag over
sortable.addEventListener("dragover", (e) => {
  e.preventDefault();

  // Get the element after which the dragged item should be placed
  const afterElement = getInsertionPoint(e.clientY);
  if (afterElement == null) {
    // If no element is found (e.g., dragged below the last item), append to the end
    sortable.appendChild(draggedItem);
  } else {
    // Otherwise, insert before the found element
    sortable.insertBefore(draggedItem, afterElement);
  }
});

function createNumberedIcon(number) {
  return L.divIcon({
    html: `
            <div class="marker-container">
                <img src="../static/greendot.svg" class="marker-img" />
                <div class="marker-label">${number}</div>
            </div>
        `,
    className: "", // No extra Leaflet styling
    iconSize: [60, 60],
    iconAnchor: [30, 30],
  });
}

function updateSelectedMarkersFromList() {
  const sortableList = document.getElementById("sortable-list");
  const newOrder = Array.from(sortableList.children).map((item) => {
    return item.textContent.split(".")[1].trim(); // Extract the address without the number
  });

  // Sort selectedMarkers based on the new order
  selectedMarkers.sort((a, b) => {
    return newOrder.indexOf(a.address) - newOrder.indexOf(b.address);
  });

  // Update the sequence number for both markers and the list
  selectedMarkers.forEach((marker, index) => {
    marker.number = index + 1; // Update marker's sequence number
    marker.setIcon(createNumberedIcon(marker.number)); // Update the icon to reflect new number
  });

  displaySelectedAddresses(); // Refresh list with updated numbering
}

document.getElementById("update-markers-btn").addEventListener("click", () => {
  updateSelectedMarkersFromList();
});

function addMarkers() {
  const addressInput = document.getElementById("address-input").value;
  const addresses = addressInput
    .split("\n")
    .map((addr) => addr.trim())
    .filter((addr) => addr);

  // Assign sequence numbers first
  const addressWithNumbers = addresses.map((address, index) => ({
    address,
    number: index + 1,
  }));

  // Geocode all addresses asynchronously
  const geocodePromises = addressWithNumbers.map(async (item) => {
    const coords = await getGeolocation(item.address); // Fetch coordinates for each address
    if (coords && coords.lat !== undefined && coords.lon !== undefined) {
      return { coords: [coords.lat, coords.lon], ...item }; // Convert to Leaflet format
    }
    // If database lookup fails, try OpenCage geocoding
    const openCageCoords = await geocode(item.address);
    if (openCageCoords) {
      return {
        coords: openCageCoords,
        ...item,
        source: "opencage",
      };
    }

    // Both geocoding attempts failed
    console.warn(`Failed to geocode address: ${item.address}`);
    return null;
  });

  // After all geocoding requests are done, add the markers
  Promise.all(geocodePromises).then((results) => {
    results.forEach((result) => {
      if (result && result.coords) {
        // Ensure valid results
        const marker = L.marker(result.coords, { icon: greenIcon }).addTo(map);
        marker.address = result.address;
        marker.number = result.number; // Assign the sequence number
        marker.on("click", () => toggleMarkerSelection(marker));
        selectedMarkers.push(marker);
      }
    });

    displaySelectedAddresses(); // Update the list after all markers are added
  });
}

function displaySelectedAddresses() {
  const sortableList = document.getElementById("sortable-list");
  sortableList.innerHTML = ""; // Clear the list

  selectedMarkers.forEach((marker, index) => {
    // Create a custom icon with the sequence number inside the green dot
    const numberedIcon = L.divIcon({
      html: `
                <div class="marker-container">
                    <img src="../static/greendot.svg" class="marker-img" />
                    <div class="marker-label">${index + 1}</div>
                </div>
            `,
      className: "",
      iconSize: [60, 60], // Ensures a fixed size
      iconAnchor: [30, 30], // Keeps the marker centered
    });

    marker.setIcon(numberedIcon); // Update existing marker icon

    // Update the list display with sequence numbers
    const addressElement = document.createElement("li");
    addressElement.setAttribute("draggable", "true");
    addressElement.innerHTML = `${index + 1}. ${
      marker.address
    } <button class="delete-btn">x</button>`; // Add delete button
    addressElement.dataset.markerIndex = index; // Store marker index
    sortableList.appendChild(addressElement);
  });
}

function copySelectedMarkersToClipboard() {
  const csvHeader = "Address";
  const csvContent = selectedMarkers
    .map((marker) => {
      return `${marker.address}`;
    })
    .join("\n");

  const fullCsvContent = `${csvHeader}\n${csvContent}`;

  // Use Clipboard API to copy the CSV content to clipboard
  navigator.clipboard
    .writeText(fullCsvContent)
    .then(() => {
      alert("Selected marker addresses copied to clipboard as CSV!");
    })
    .catch((error) => {
      console.error("Failed to copy: ", error);
      alert("Failed to copy to clipboard.");
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
    const rateElement = document.getElementById('rate-limit');
    const resetElement = document.getElementById('rate-reset');
    
    // Update remaining calls
    rateElement.textContent = `API Calls: ${data.rate.remaining}/${data.rate.limit}`;
    
    // Calculate and display reset time
    const resetDate = new Date(data.rate.reset * 1000);
    const resetTime = resetDate.toLocaleTimeString();
    resetElement.textContent = `Resets: ${resetTime}`;
  }
}

// Geocode address
function geocode(address) {
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
function getGeolocation(address) {
  return fetch(`/static/geocode.php?address=${encodeURIComponent(address)}`)
    .then((response) => response.text())
    .then((text) => {
      console.log("Raw response:", text); // Keep for debugging
      try {
        const data = JSON.parse(text);

        // Simple exact match check
        if (data.lat && data.lon) {
          return {
            lat: data.lat,
            lon: data.lon,
            source: data.source,
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

// select a marker and highlight the corresponding list item
function toggleMarkerSelection(marker) {
  // Clear previous selections
  selectedMarkers.forEach((m) => {
    m.getElement().classList.remove("marker", "selected");
    m.getElement()
      .querySelector(".marker-img")
      .classList.remove("marker-selected");
  });

  document.querySelectorAll("#sortable-list li").forEach((item) => {
    item.classList.remove("markerSelect");
  });

  // Find and highlight the corresponding list item
  const listItems = document.querySelectorAll("#sortable-list li");
  listItems.forEach((item, index) => {
    if (item.textContent.includes(marker.address)) {
      // Highlight the list item
      item.classList.add("markerSelect");

      // Center the map on the selected marker
      map.setView(marker.getLatLng(), map.getZoom());

      // Scroll the list item into view
      item.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
        inline: "start",
      });

      // Update marker appearance
      marker.getElement().classList.add("marker", "selected");
      marker
        .getElement()
        .querySelector(".marker-img")
        .classList.add("marker-selected");
    }
  });
}

map.on("click", function (e) {
  var coords = [e.latlng.lat, e.latlng.lng];
  var marker = L.marker(coords, { icon: greenIcon }).addTo(map);
  reverseGeocode(e.latlng.lat, e.latlng.lng, (address) => {
    marker.address = address ? address : `Lat: ${coords[0]}, Lng: ${coords[1]}`;
    marker.on("click", function () {
      toggleMarkerSelection(marker);
    });
    selectedMarkers.push(marker);
    displaySelectedAddresses();
  });
});
