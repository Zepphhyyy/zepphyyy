<?php
// Database configuration
$host = 'localhost';
$dbname = 'country_explorer';
$username = 'root';
$password = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asian Countries Explorer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="search-container">
                <center>
                    <input 
                    type="text" 
                    id="searchInput" 
                    class="search-input" 
                    placeholder="Search for a country..."
                    autocomplete="off"
                >
                <button class="search-btn">🔍</button>
                <ul id="suggestionsList" class="suggestions-list"></ul>
                </center>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="carousel-container">
            <!-- Previous Button -->
            <button class="carousel-btn prev-btn" id="prevBtn">❮</button>

            <!-- Carousel Content -->
            <div class="carousel-content">
                <img id="countryImage" src="" alt="Country Flag" class="country-image">
                <div class="country-info">
                    <h2 id="countryName"></h2>
                    <p id="countryCapital"></p>
                </div>
            </div>

            <!-- Next Button -->
            <button class="carousel-btn next-btn" id="nextBtn">❯</button>
        </div>
    </main>
    <script src="script.js"></script>
</body>
</html>
