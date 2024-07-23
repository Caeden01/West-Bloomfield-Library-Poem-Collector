<?php
session_start();
require "../include.php";

if(!isset($_SESSION["logged_in"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: you cannot access this service because you are not logged in.");
}
$conn = new mysqli($servername, $username, $password, $dbname);
if(isUserBanned($conn, $_SESSION["username"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: You are banned.");
}
if($_SESSION["tier"] != "medium" && $_SESSION["tier"] != "high") {
    header("HTTP/1.1 400 Bad Request");
    die("Error: your permissions aren't high enough to access this service.");
}
// Function to generate pagination links
function numberOfPages($conn, $status, $searchQuery = '') {
    $limit = 15; // Number of entries per page

    // Prepare the base SQL query
    $sql = "SELECT COUNT(*) AS total FROM poems";
    $params = array();
    $types = '';

    // Add conditions based on search query and status
    if ($searchQuery) {
        $sql .= " WHERE (id LIKE ? OR title LIKE ? OR name LIKE ?)";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
        $types .= 'sss';
    } else if ($status) {
        $sql .= $searchQuery ? " AND status = ?" : " WHERE status = ?";
        $params[] = $status;
        $types .= 's';
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    // Calculate total pages
    $totalPages = ceil($total / $limit);

    return $totalPages;
}

function displayPoems($conn, $status, $page, $searchQuery = '') {
    $limit = 15; // Number of entries per page

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Prepare the base SQL query
    $sql = "SELECT * FROM poems";
    $params = array();
    $types = '';

    // Add conditions based on search query and status
    if ($searchQuery) {
        $sql .= " WHERE (id LIKE ? OR title LIKE ? OR name LIKE ?)";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
        $types .= 'sss';
    } else if ($status) {
        $sql .= $searchQuery ? " AND status = ?" : " WHERE status = ?";
        $params[] = $status;
        $types .= 's';
    }

    // Add ordering and limits
    $sql .= " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $pages = numberOfPages($conn, $status, $searchQuery);

    $data = array("currentPage" => $page, "totalPages" => $pages, "poems" => array());
    while ($row = $result->fetch_assoc()) {
        $row["securityInformation"] =  json_decode($row["securityInformation"], true);
        $row["comments"] =  json_decode($row["comments"], true);
        $row["commentSecurityInformation"] =  json_decode($row["commentSecurityInformation"], true);
        $row["printHistory"] =  json_decode($row["printHistory"], true);
        $row["encodedId"] = scrambleLetters($row["id"]);

        $data["poems"][] = $row;
    }
    // Convert the associative array into JSON format
    $json_data = json_encode($data);

    return $json_data;
}


function deleteComment($conn, $poemId, $commentIndex) {
    // SQL query to retrieve the poem with the given ID
    $sql = "SELECT comments, commentSecurityInformation FROM poems WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $poemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return json_encode(['success' => false, 'message' => 'Poem not found']);
    }

    $row = $result->fetch_assoc();
    $comments = json_decode($row['comments'], true);
    $commentSecurityInformation = json_decode($row['commentSecurityInformation'], true);

    if ($commentIndex < 0 || $commentIndex >= count($comments)) {
        return json_encode(['success' => false, 'message' => 'Invalid comment index']);
    }

    // Remove the comment at the specified index
    array_splice($comments, $commentIndex, 1);
    array_splice($commentSecurityInformation, $commentIndex, 1);
    
    $updatedComments = json_encode($comments);
    $updatedCommentSecurityInformation = json_encode($commentSecurityInformation);

    // Update the database with the new comments and commentSecurityInformation arrays
    $updateSql = "UPDATE poems SET comments = ?, commentSecurityInformation = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sss", $updatedComments, $updatedCommentSecurityInformation, $poemId);
    if ($updateStmt->execute()) {
        return json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
    } else {
        return json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    }
}
function updatePoemStatus($conn, $id, $newStatus, $approverAccountName) {
    global $ip;
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
        $history_addon = "Status changed to " . $newStatus . " by " . $approverAccountName . " through Admin Database on " . $date . " with the IP: " . $ip . "\n";
  
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
function deletePoem($conn, $id) {
    $deleteSql = "DELETE FROM poems WHERE id=?";

    // Prepare statement to update the row
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("s", $id);
  
    // Execute update statement
    $deleteStmt->execute();

    // Check if any rows were affected
    if ($deleteStmt->affected_rows === 0) {
        // No rows were updated
        $conn->rollback();
        $updateStmt->close();
        $conn->close();
        header("HTTP/1.1 404 Not Found");
        return false; // No rows found to update
    }

    echo "Poem deleted successfully.";
}

header('Content-Type: application/json');

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$status = isset($_POST['table']) ? $_POST['table'] : 'pending';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$poemId = isset($_POST['poemId']) ? $_POST['poemId'] : '';
$search = isset($_POST['search']) ? $_POST['search'] : '';

if($action === 'move' && isset($_POST["target_table"])) {
    updatePoemStatus($conn, $poemId, $_POST["target_table"], $_SESSION["username"]);
} else if($action === 'delete_poem') {
    if($_SESSION["tier"] != "high") {
        header("HTTP/1.1 400 Bad Request");
        die("Error: You're not allowed to permanently delete poems as a medium or low tier moderator. Please either contact a higher tier moderator or wait the 30 days for the poem to be automatically deleted.");
    } else {
        deletePoem($conn, $poemId);
    }
} else if ($action === 'deleteComment') {
    $commentIndex = isset($_POST['commentIndex']) ? intval($_POST['commentIndex']) : -1;
    echo deleteComment($conn, $poemId, $commentIndex);
} else {
    echo displayPoems($conn, $status, $page, $search);
}

$conn->close();

?>
