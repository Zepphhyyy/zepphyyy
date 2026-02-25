<?php
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'country_explorer';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the action from request
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'getCountries':
            getCountries($pdo);
            break;

        case 'searchCountries':
            searchCountries($pdo);
            break;

        case 'populateFlags':
            populateFlags($pdo);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}

// Fetch all countries
function getCountries($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, capital FROM countries ORDER BY name ASC");
        $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($countries);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error fetching countries: ' . $e->getMessage()]);
    }
}

// Search countries
function searchCountries($pdo) {
    try {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (strlen($query) < 1) {
            echo json_encode([]);
            return;
        }

        $search = "%$query%";
        $stmt = $pdo->prepare("
            SELECT id, name, capital FROM countries
            WHERE name LIKE :search OR capital LIKE :search
            ORDER BY name ASC
            LIMIT 10
        ");
        $stmt->execute([':search' => $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error searching countries: ' . $e->getMessage()]);
    }
}

// Populate flags from external URLs into LONGBLOB
function populateFlags($pdo) {
    try {
        $flagUrls = [
            'Afghanistan' => 'https://flagcdn.com/w320/af.png',
            'Azerbaijan' => 'https://flagcdn.com/w320/az.png',
            'Bahrain' => 'https://flagcdn.com/w320/bh.png',
            'Bangladesh' => 'https://flagcdn.com/w320/bd.png',
            'Bhutan' => 'https://flagcdn.com/w320/bt.png',
            'Brunei' => 'https://flagcdn.com/w320/bn.png',
            'Cambodia' => 'https://flagcdn.com/w320/kh.png',
            'China' => 'https://flagcdn.com/w320/cn.png',
            'Georgia' => 'https://flagcdn.com/w320/ge.png',
            'Hong Kong' => 'https://flagcdn.com/w320/hk.png',
            'India' => 'https://flagcdn.com/w320/in.png',
            'Indonesia' => 'https://flagcdn.com/w320/id.png',
            'Iran' => 'https://flagcdn.com/w320/ir.png',
            'Iraq' => 'https://flagcdn.com/w320/iq.png',
            'Israel' => 'https://flagcdn.com/w320/il.png',
            'Japan' => 'https://flagcdn.com/w320/jp.png',
            'Jordan' => 'https://flagcdn.com/w320/jo.png',
            'Kazakhstan' => 'https://flagcdn.com/w320/kz.png',
            'Kuwait' => 'https://flagcdn.com/w320/kw.png',
            'Kyrgyzstan' => 'https://flagcdn.com/w320/kg.png',
            'Laos' => 'https://flagcdn.com/w320/la.png',
            'Lebanon' => 'https://flagcdn.com/w320/lb.png',
            'Macao' => 'https://flagcdn.com/w320/mo.png',
            'Malaysia' => 'https://flagcdn.com/w320/my.png',
            'Maldives' => 'https://flagcdn.com/w320/mv.png',
            'Mongolia' => 'https://flagcdn.com/w320/mn.png',
            'Myanmar' => 'https://flagcdn.com/w320/mm.png',
            'Nepal' => 'https://flagcdn.com/w320/np.png',
            'North Korea' => 'https://flagcdn.com/w320/kp.png',
            'Oman' => 'https://flagcdn.com/w320/om.png',
            'Pakistan' => 'https://flagcdn.com/w320/pk.png',
            'Palestine' => 'https://flagcdn.com/w320/ps.png',
            'Philippines' => 'https://flagcdn.com/w320/ph.png',
            'Qatar' => 'https://flagcdn.com/w320/qa.png',
            'Saudi Arabia' => 'https://flagcdn.com/w320/sa.png',
            'Singapore' => 'https://flagcdn.com/w320/sg.png',
            'South Korea' => 'https://flagcdn.com/w320/kr.png',
            'Sri Lanka' => 'https://flagcdn.com/w320/lk.png',
            'Syria' => 'https://flagcdn.com/w320/sy.png',
            'Taiwan' => 'https://flagcdn.com/w320/tw.png',
            'Tajikistan' => 'https://flagcdn.com/w320/tj.png',
            'Thailand' => 'https://flagcdn.com/w320/th.png',
            'Timor-Leste' => 'https://flagcdn.com/w320/tl.png',
            'Turkey' => 'https://flagcdn.com/w320/tr.png',
            'Turkmenistan' => 'https://flagcdn.com/w320/tm.png',
            'United Arab Emirates' => 'https://flagcdn.com/w320/ae.png',
            'Uzbekistan' => 'https://flagcdn.com/w320/uz.png',
            'Vietnam' => 'https://flagcdn.com/w320/vn.png',
            'Yemen' => 'https://flagcdn.com/w320/ye.png'
        ];

        $inserted = 0;
        $failed = [];

        foreach ($flagUrls as $country => $url) {
            $imageData = @file_get_contents($url);
            if ($imageData === false) {
                $failed[] = $country;
                continue;
            }

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO countries (name, capital, flag_image) VALUES (:name, :capital, :flag)
                    ON DUPLICATE KEY UPDATE flag_image = VALUES(flag_image)
                ");
                
                // Get capital (for now we'll set a placeholder - you should update this)
                $capital = getCapital($country);
                
                $stmt->execute([
                    ':name' => $country,
                    ':capital' => $capital,
                    ':flag' => $imageData
                ]);
                $inserted++;
            } catch (PDOException $e) {
                $failed[] = $country;
            }
        }

        echo json_encode([
            'success' => true,
            'inserted' => $inserted,
            'failed' => $failed,
            'message' => "Populated $inserted countries with flags"
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error populating flags: ' . $e->getMessage()]);
    }
}


