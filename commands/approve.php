<?php
require "../include.php";

session_start();

if(!isset($_SESSION["logged_in"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: you cannot access this service because you are not logged in.");
}
$conn = new mysqli($servername, $username, $password, $dbname);

if(isUserBanned($conn, $_SESSION["username"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: You are banned.");
}

// Function to fetch a random row from the last N rows from the pending_poems table
function fetchRandomRecentPendingPoem() {
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL query to fetch a random row from the pending_poems table
    $sql = "
        SELECT id, name, email, title, poem, timestamp, comments, printHistory, status
        FROM poems 
        WHERE status = 'Pending'
        ORDER BY RAND()
        LIMIT 1
    ";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Execute statement
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Fetch the row into an array
    $row = $result->fetch_assoc();

    if ($result->num_rows > 0) {
        $row["comments"] = json_decode($row["comments"], true);
        $row["printHistory"] = json_decode($row["printHistory"], true);
        $row["encodedId"] = scrambleLetters($row["id"]);
    }

    // Free result and close statement
    $result->free();
    $stmt->close();

    // Check if there is a row
    if (empty($row)) {
        return null; // No row found
    }

    // Return the selected row
    return $row;
}

// Function to update the status of a poem
function updatePoemStatus($id, $newStatus, $approverAccountName) {
    global $conn, $ip;
  
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
  
    // Start a transaction
    $conn->begin_transaction();
  
    try {
        $date = date("F j, Y \a\\t g:i:s A");
  
        // Prepare SQL query to update the status and history
        $updateSql = "
            UPDATE poems 
            SET status = ?, history = CONCAT(history, ?) 
            WHERE id = ?
        ";
  
        // Prepare statement to update the row
        $updateStmt = $conn->prepare($updateSql);
  
        // Prepare the history addition string
        $history_addon = "Status changed to " . $newStatus . " by " . $approverAccountName . " through Poem Swiper on " . $date . " with the IP: " . $ip . "\n";
  
        $updateStmt->bind_param("sss", $newStatus, $history_addon, $id);
  
        // Execute update statement
        $updateStmt->execute();
  
        // Check if any rows were affected
        if ($updateStmt->affected_rows === 0) {
            // No rows were updated
            $conn->rollback();
            $updateStmt->close();
            $conn->close();
            header("HTTP/1.1 404 Not Found");
            return false; // No rows found to update
        }
  
        // Commit the transaction
        $conn->commit();
  
        // Close statements
        $updateStmt->close();
  
        return true; // Successfully updated the row
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        // Rollback the transaction in case of error
        $conn->rollback();
        $conn->close();
        throw $e;
    }
}

// Check if an admin submits a username and status change through the form
if (isset($_POST["approve"])) {
    updatePoemStatus($_POST["approve"], "Approved", $_SESSION["username"]);
} else if (isset($_POST["reject"])) {
    updatePoemStatus($_POST["reject"], "Rejected", $_SESSION["username"]);
} else {
    $mostRecentPendingPoem = fetchRandomRecentPendingPoem();
    // Respond with JSON-encoded data
    header('Content-Type: application/json');
    echo json_encode($mostRecentPendingPoem);
}
$conn->close();
?>
