let tabCount = 1;
var selectedMarkers = [];
const geocodeCache = {};

var greenIcon = L.icon({
  iconUrl: "../static/greendot.svg",
  iconSize: [60, 60],
  iconAnchor: [30, 30],
  popupAnchor: [1, -34],
});

// Replace the automatic API key prompt with this code
let apiKey = sessionStorage.getItem("apiKey");

function promptForApiKey() {
  const newApiKey = prompt("Please enter your OpenCage API key:");
  if (newApiKey && newApiKey.match(/^[a-zA-Z0-9]{32}$/)) {
    apiKey = newApiKey;
    sessionStorage.setItem("apiKey", apiKey);
    alert("API key set successfully!");
  } else {
    alert(
      "Invalid API key format. Key should be 32 characters long and contain only letters and numbers."
    );
  }
}

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

// Initialize the map as soon as the page loads
document.addEventListener("DOMContentLoaded", () => {
  initMap();

  // Add event listener for the API key button
  document.getElementById("set-api-key").addEventListener("click", promptForApiKey);
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
