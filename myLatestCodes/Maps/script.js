let countries = [];
let currentIndex = 0;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    fetchCountries();
    setupSearch();
    setupCarousel();
});

// Get random starting index
function getRandomIndex() {
    return Math.floor(Math.random() * countries.length);
}

// Fetch countries from PHP backend
function fetchCountries() {
    fetch('api.php?action=getCountries')
        .then(response => response.json())
        .then(data => {
            countries = data;
            // Set random starting index
            currentIndex = getRandomIndex();
            updateCarousel();
            document.getElementById('totalCountries').textContent = countries.length;
        })
        .catch(error => console.error('Error fetching countries:', error));
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsList = document.getElementById('suggestionsList');
    const searchBtn = document.querySelector('.search-btn');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        if (query.length === 0) {
            suggestionsList.classList.remove('active');
            return;
        }

        // Filter countries
        const filtered = countries.filter(country =>
            country.name.toLowerCase().includes(query) ||
            country.capital.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            suggestionsList.classList.remove('active');
            return;
        }

        // Display suggestions
        suggestionsList.innerHTML = '';
        filtered.forEach(country => {
            const li = document.createElement('li');
            li.textContent = `${country.name} - ${country.capital}`;
            li.addEventListener('click', function() {
                selectCountry(country.id);
                searchInput.value = '';
                suggestionsList.classList.remove('active');
            });
            suggestionsList.appendChild(li);
        });

        suggestionsList.classList.add('active');
    });

    // Search button click handler
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim().toLowerCase();
        
        if (query.length === 0) {
            alert('Please enter a country name or capital to search. Please try again.');
            return;
        }

        // Find matching country
        const found = countries.find(country =>
            country.name.toLowerCase().includes(query) ||
            country.capital.toLowerCase().includes(query)
        );

        if (found) {
            selectCountry(found.id);
            searchInput.value = '';
            suggestionsList.classList.remove('active');
        } else {
            alert('No country found with that name or capital. Please try again.');
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && e.target !== suggestionsList) {
            suggestionsList.classList.remove('active');
        }
    });
}

// Select a country from suggestions
function selectCountry(countryId) {
    const index = countries.findIndex(c => c.id === countryId);
    if (index !== -1) {
        currentIndex = index;
        updateCarousel();
    }
}

// Setup carousel buttons
function setupCarousel() {
    document.getElementById('prevBtn').addEventListener('click', goToPrevious);
    document.getElementById('nextBtn').addEventListener('click', goToNext);

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') goToPrevious();
        if (e.key === 'ArrowRight') goToNext();
    });
}

// Navigate to previous country
function goToPrevious() {
    currentIndex = (currentIndex - 1 + countries.length) % countries.length;
    updateCarousel();
}

// Navigate to next country
function goToNext() {
    currentIndex = (currentIndex + 1) % countries.length;
    updateCarousel();
}

// Update carousel display
function updateCarousel() {
    if (countries.length === 0) return;

    const country = countries[currentIndex];

    document.getElementById('countryName').textContent = country.name;
    document.getElementById('countryCapital').textContent = `Capital: ${country.capital}`;
    document.getElementById('countryImage').src = `showImage.php?id=${country.id}`;
    document.getElementById('currentIndex').textContent = currentIndex + 1;

    // Update search input with current country
    document.getElementById('searchInput').value = '';
}
