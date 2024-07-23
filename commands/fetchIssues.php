<?php
session_start();

require '../include.php'; // Adjust the path to your db.php

if(!isset($_SESSION["logged_in"]) && !$_SESSION["logged_in"]) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: you cannot access this service because you are not logged in.");
}

$conn = new mysqli($servername, $username, $password, $dbname);

// If there is an error, output the error.
if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Server Error: Unable to connect to the database."]);
    exit;
}

if(isUserBanned($conn, $_SESSION["username"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: You are banned.");
}
if($_SESSION["tier"] != "medium" && $_SESSION["tier"] != "high") {
    header("HTTP/1.1 400 Bad Request");
    die("Error: your permissions aren't high enough to access this service.");
}


header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && $_POST["action"] === "close") {
    // Get the issue ID from the POST request.
    $id = $_POST["id"];

    // Update the status to 'closed' and schedule for deletion in a month.
    $stmt = $conn->prepare("UPDATE issue_tickets SET status = 'closed' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Create an event to delete the issue after 1 month.
        $event_name = "delete_issue_$id";
        $event_query = $conn->prepare("
            CREATE EVENT IF NOT EXISTS $event_name
            ON SCHEDULE AT DATE_ADD(NOW(), INTERVAL 1 MONTH)
            DO DELETE FROM issue_tickets WHERE id = $id
        ");
        $event_query->execute();
        
        header("HTTP/1.1 200 OK");
        echo json_encode(["message" => "Ticket marked as closed and scheduled for deletion."]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(["error" => "Server Error: Unable to update ticket status."]);
    }

    $stmt->close();
    $conn->close();
} else if(isset($_POST["status"])) {
       // Initiate a connection with the MySQL database.
       $conn = new mysqli($servername, $username, $password, $dbname);

       // If there is an error, output the error.
       if ($conn->connect_error) {
           header("HTTP/1.1 500 Internal Server Error");
           echo json_encode(["error" => "Server Error: Unable to connect to the database."]);
           exit;
       }

        // Retrieve issues from the database.
        $query = "SELECT id, name, email, subject, issue FROM issue_tickets WHERE status = ?";
   
       $stmt = $conn->prepare($query);
       $stmt->bind_param("s", $_POST["status"]);
       $stmt->execute();
       $result = $stmt->get_result();
   
       if ($result) {
           $issues = [];
           while ($row = $result->fetch_assoc()) {
               $issues[] = $row;
           }
           echo json_encode($issues);
       } else {
           header("HTTP/1.1 500 Internal Server Error");
           echo json_encode(["error" => "Server Error: Unable to retrieve issues."]);
       }
   
       $result->free();
       $conn->close();
}
?>
