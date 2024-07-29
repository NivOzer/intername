<?php
include "submit_lead.php";
$firstName = null;
$lastName = null;
$email = null;
$phone = null;
$note = null;
$ip = null;
$url = null;
$sub1 = null;
$registrationError = null;
$registrationSuccess = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["submit_lead"])) {
        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
        $note = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_SPECIAL_CHARS);
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $_SERVER['HTTP_REFERER'];
        $sub1 = filter_input(INPUT_GET, 'sub_1', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

        // Check if the email is already registered
        $checkEmailQuery = "SELECT * FROM leads WHERE email = '$email'";
        $result = mysqli_query($conn, $checkEmailQuery);

        if (mysqli_num_rows($result) > 0) {
            $registrationError = "Sorry, this email is already registered";
        } else {
            $sqlinsert = "INSERT INTO leads (first_name, last_name, email, phone_number, ip, url, note, sub_1) VALUES ('$firstName', '$lastName', '$email', '$phone', '$ip', '$url', '$note', '$sub1')";
            if (mysqli_query($conn, $sqlinsert)) {
                $registrationSuccess = "Thank you $firstName $lastName, we’ll contact you soon";
            } else {
                $registrationError = "Error: " . mysqli_error($conn);
            }
        }
    }
}

if (!function_exists('popUpModal')) {
    function popUpModal($message){
        echo "<script type='text/javascript'>
                window.onload = function() {
                    var modal = document.getElementById('messageModal');
                    var messageText = document.getElementById('messageText');
                    messageText.textContent = '$message';
                    modal.classList.add('active');
                }
              </script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <form id="leadForm" action="backoffice.php" method="post">
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone" placeholder="Phone Number" required><br>
        <textarea name="note" placeholder="Note"></textarea><br>
        <button type="submit" name="submit_lead">Submit</button><br>
        <?php if ($registrationError): ?>
            <p class="error"><?php echo $registrationError; ?></p>
        <?php endif; ?>
    </form>
    <div id="messageModal">
        <p id="messageText"></p>
        <button onclick="closeModal()">Close</button>
    </div>
    <input type="hidden" id="messageTextContent" value="<?php echo $registrationSuccess; ?>">
    <script src="scripts.js"></script>
</body>
</html>
