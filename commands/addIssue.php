<?php
require '../include.php'; // Adjust the path to your db.php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["subject"]) && isset($_POST["issue"]) && isset($_POST["token"])) {
    if (!isValidCaptcha($_POST["token"])) {
        header("HTTP/1.1 400 Bad Request");
        die("Captcha is invalid.");
    }

    // Set length limits
    $maxLengthName = 100;
    $maxLengthEmail = 100;
    $maxLengthSubject = 200;
    $maxLengthIssue = 1000;

    // Validate lengths
    $name = $_POST["name"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $issue = $_POST["issue"];

    if (strlen($name) > $maxLengthName || strlen($email) > $maxLengthEmail || strlen($subject) > $maxLengthSubject || strlen($issue) > $maxLengthIssue) {
        header("HTTP/1.1 400 Bad Request");
        die("Input exceeds the allowed length.");
    }

    // Initiate a connection with the MySQL database.
    $conn = new mysqli($servername, $username, $password, $dbname);

    // If there is an error, output the error.
    if ($conn->connect_error) {
        header("HTTP/1.1 500 Internal Server Error");
        die("Server Error: Unable to connect to the database.");
    }

    // Get the current date and time.
    $date = date("Y-m-d H:i:s");

    // Record IP and user agent information for security reasons.
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    $securityInformationJSON = $_POST["securityInformation"];
    $securityInformationArray = json_decode($securityInformationJSON, true);
    $securityInformationArray["user_agent"] = $user_agent;
    $securityInformationArray["ip"] = $_SERVER['REMOTE_ADDR'];
    $securityInformationArray["exact_time"] = date("Y-m-d h:i:sa");
    $securityInformationJSON = json_encode($securityInformationArray);

    // MySQL script using prepared statements.
    $stmt = $conn->prepare("INSERT INTO issue_tickets (name, email, subject, issue, securityInformation, status) VALUES (?, ?, ?, ?, ?, 'open')");

    $stmt->bind_param("sssss", $name, $email, $subject, $issue, $securityInformationJSON);

    // Run the SQL script.
    if ($stmt->execute()) {
        $insertedId = $stmt->insert_id; // Get the auto-incremented ID
        header("HTTP/1.1 201 Created");
        echo json_encode(["message" => "Ticket created successfully", "id" => $insertedId]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(["error" => "Server Error: Unable to create ticket."]);
    }

    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["error" => "Invalid request. Required fields are missing."]);
}
?>
