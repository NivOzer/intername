<?php
session_start();
include 'submit_lead.php'; // Reuse the DB connection

// Handle authentication
function handleAuthentication($conn) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT password FROM users WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];
            if (password_verify($password, $hashedPassword)) {
                $_SESSION['loggedin'] = true;
            } else {
                $stmt->close();
                header("Location: login.php?error=Invalid credentials");
                exit;
            }
        } else {
            $stmt->close();
            header("Location: login.php?error=Invalid credentials");
            exit;
        }

        $stmt->close();
    }
}

// Redirect to login if not authenticated
function redirectIfNotAuthenticated() {
    if (!isset($_SESSION['loggedin'])) {
        header("Location: login.php");
        exit;
    }
}

// Handle actions
function handleActions($conn) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case 'show':
                showLead($conn, $_POST['id']);
                break;
            case 'edit':
                editLead($conn, $_POST['id']);
                break;
            case 'filter':
                filterLeads($conn, $_POST['filter']);
                break;
            case 'populate':
                populateDatabaseWithLeads($conn);
                break;
        }
    }
}

// Function to show lead details by ID
function showLead($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lead = $result->fetch_assoc();
    $stmt->close();

    echo json_encode($lead);
    exit;
}

// Function to mark a lead as called by ID
function editLead($conn, $id) {
    $stmt = $conn->prepare("UPDATE leads SET called = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success']);
    exit;
}

// Function to filter leads
function filterLeads($conn, $filter) {
    $query = "SELECT * FROM leads WHERE 1=1";
    
    if ($filter == 'called') {
        $query .= " AND called = 1";
    } elseif ($filter == 'today') {
        $query .= " AND DATE(created_at) = CURDATE()";
    } elseif ($filter == 'country') {
        $country = isset($_POST['country']) ? $_POST['country'] : '';
        if ($country) {
            $query .= " AND country = '$country'";
        }
    }

    $result = $conn->query($query);
    $leads = [];
    while ($row = $result->fetch_assoc()) {
        $leads[] = $row;
    }

    echo json_encode($leads);
    exit;
}

// Function to fetch JSON data
function fetchJSONData() {
    $url = "https://jsonplaceholder.typicode.com/users";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to generate random string
function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Function to generate random email
function generateRandomEmail() {
    $domains = ['example.com', 'test.com', 'sample.org'];
    return generateRandomString(7) . '@' . $domains[array_rand($domains)];
}

// Function to generate random phone number
function generateRandomPhoneNumber() {
    return '123-456-' . rand(1000, 9999);
}

// Function to generate random IP address
function generateRandomIPAddress() {
    return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
}

// Function to populate database with leads
function populateDatabaseWithLeads($conn) {
    $data = fetchJSONData();

    foreach ($data as $user) {
        $nameParts = explode(' ', $user['name']);
        $first_name = isset($nameParts[0]) ? $nameParts[0] : generateRandomString(5);
        $last_name = isset($nameParts[1]) ? $nameParts[1] : generateRandomString(5);
        $email = $user['email'];
        $phone = generateRandomPhoneNumber();
        $ip = generateRandomIPAddress();
        $url = 'http://' . generateRandomString(7) . '.com';
        $note = "Random note";
        $sub_1 = "Random advertisement";
        $country = generateRandomString(7);

        $stmt = $conn->prepare("INSERT INTO leads (first_name, last_name, email, phone_number, ip, url, note, sub_1, country, called, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $ip, $url, $note, $sub_1, $country);
        $stmt->execute();
        $stmt->close();
    }
}

// function createUser($conn, $username, $password) {
//     $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
//     $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ss", $username, $hashedPassword);
//     $stmt->execute();
//     $stmt->close();
// }
// createUser($conn, 'admin', 'password');
//main user is username: admin password: password(hashed)


// Main execution
handleAuthentication($conn);
redirectIfNotAuthenticated();
handleActions($conn);

$leads = $conn->query("SELECT * FROM leads");
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Leads Management</h1>

    <!-- Include the Add Lead Form from index.php -->
    <?php include 'index.php'; ?>

    <!-- Filter Leads Form -->
    <h2>Filter Leads</h2>
    <form id="filterForm" action="backoffice.php" method="POST">
        <input type="hidden" name="action" value="filter">
        <select name="filter" id="filter" onchange="updateFormVisibility()">
            <option value="">Select Filter</option>
            <option value="called">Marked as Called</option>
            <option value="today">Created Today</option>
            <option value="country">By Country</option>
        </select>
        <input type="text" name="country" id="country" placeholder="Country" style="display:none;">
        <button type="submit">Filter</button>
    </form>

    <!-- Leads Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>IP</th>
                <th>Country</th>
                <th>URL</th>
                <th>Note</th>
                <th>Sub_1</th>
                <th>Called</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $leads->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['first_name'] ?></td>
                    <td><?= $row['last_name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['phone_number'] ?></td>
                    <td><?= $row['ip'] ?></td>
                    <td><?= $row['country'] ?></td>
                    <td><?= $row['url'] ?></td>
                    <td><?= $row['note'] ?></td>
                    <td><?= $row['sub_1'] ?></td>
                    <td><?= $row['called'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <form action="backoffice.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="show">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">View</button>
                        </form>
                        <form action="backoffice.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">Mark as Called</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <form action="backoffice.php" method="POST">
        <input type="hidden" name="action" value="populate">
        <button type="submit">Populate Database with Leads</button>
    </form>

    <script>
        function updateFormVisibility() {
            var filter = document.getElementById('filter').value;
            var countryField = document.getElementById('country');
            if (filter === 'country') {
                countryField.style.display = 'inline';
            } else {
                countryField.style.display = 'none';
            }
        }

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('backoffice.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';
                data.forEach(lead => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${lead.id}</td>
                        <td>${lead.first_name}</td>
                        <td>${lead.last_name}</td>
                        <td>${lead.email}</td>
                        <td>${lead.phone_number}</td>
                        <td>${lead.ip}</td>
                        <td>${lead.country}</td>
                        <td>${lead.url}</td>
                        <td>${lead.note}</td>
                        <td>${lead.sub_1}</td>
                        <td>${lead.called}</td>
                        <td>${lead.created_at}</td>
                        <td>
                            <form action="backoffice.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="show">
                                <input type="hidden" name="id" value="${lead.id}">
                                <button type="submit">View</button>
                            </form>
                            <form action="backoffice.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="${lead.id}">
                                <button type="submit">Mark as Called</button>
                            </form>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            });
        });
    </script>
</body>
</html>
