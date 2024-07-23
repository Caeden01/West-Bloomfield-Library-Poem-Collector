<?php
session_start();
require "../include.php";

if(!isset($_SESSION["logged_in"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: you cannot access this service because you are not logged in.");
}
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if(isUserBanned($mysqli, $_SESSION["username"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: You are banned.");
}
if($_SESSION["tier"] != "medium" && $_SESSION["tier"] != "high") {
    header("HTTP/1.1 400 Bad Request");
    die("Error: your permissions aren't high enough to access this service.");
}
// Function to view all poems in the queue
function viewQueue($mysqli) {
    $sql = "SELECT id, poem_id, queue_number, title, author, added_by FROM printer_queue ORDER BY queue_number ASC";
    $result = $mysqli->query($sql);

    $queue = [];
    while ($row = $result->fetch_assoc()) {
        $queue[] = $row;
    }

    return json_encode($queue);
}
// Function to add a poem to the queue
function addToQueue($mysqli, $added_by, $poemId) {
    // Step 1: Fetch title and author from the poems table
    $poemQuery = "SELECT title, name FROM poems WHERE id = ?";
    $poemStmt = $mysqli->prepare($poemQuery);
    $poemStmt->bind_param("s", $poemId);
    $poemStmt->execute();
    $poemResult = $poemStmt->get_result();

    if ($poemResult->num_rows == 0) {
        return json_encode(["status" => "error", "message" => "Poem not found."]);
    }

    $poemData = $poemResult->fetch_assoc();
    $title = $poemData['title'];
    $author = $poemData['name'];

    // Step 2: Insert the poem into the printer_queue table
    $sql = "INSERT INTO printer_queue (title, author, added_by, poem_id, queue_number)
            SELECT ?, ?, ?, ?, IFNULL(MAX(queue_number), 0) + 1 FROM printer_queue";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssss", $title, $author, $added_by, $poemId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $queueNumber = $mysqli->insert_id;
        $result = $mysqli->query("SELECT queue_number FROM printer_queue WHERE id = $queueNumber");
        $row = $result->fetch_assoc();
        return json_encode(["status" => "success", "message" => "Poem added to queue.", "queue_number" => $row['queue_number']]);
    } else {
        return json_encode(["status" => "error", "message" => "Failed to add poem to queue."]);
    }
}

function removeFromQueue($mysqli, $queueId) {
    // Begin transaction
    $mysqli->begin_transaction();

    // Get the queue number of the item to be removed
    $sql = "SELECT queue_number FROM printer_queue WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $stmt->bind_result($queueNumber);
    $stmt->fetch();
    $stmt->close();

    if (!$queueNumber) {
        $mysqli->rollback();
        return false;
    }

    // Delete the item from the queue
    $deleteSql = "DELETE FROM printer_queue WHERE id = ?";
    $stmt = $mysqli->prepare($deleteSql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $deleteSuccess = $stmt->affected_rows > 0;
    $stmt->close();

    if ($deleteSuccess) {
        // Update the queue numbers of the items above the removed queue ID
        $updateSql = "UPDATE printer_queue SET queue_number = queue_number - 1 WHERE queue_number > ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param("i", $queueNumber);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $mysqli->commit();
        return true;
    } else {
        // Rollback transaction if delete fails
        $mysqli->rollback();
        return false;
    }
}

// Function to move a poem up in the queue
function moveUpInQueue($mysqli, $queueId) {
    // Begin transaction
    $mysqli->begin_transaction();

    // Get the current queue number of the item
    $sql = "SELECT queue_number FROM printer_queue WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $stmt->bind_result($currentQueueNumber);
    $stmt->fetch();
    $stmt->close();

    if (!$currentQueueNumber || $currentQueueNumber == 1) {
        // Cannot move up if it's the first item or not found
        $mysqli->rollback();
        return json_encode(["status" => "error", "message" => "Cannot move up."]);
    }

    // Get the id of the item above the current item
    $sql = "SELECT id FROM printer_queue WHERE queue_number = ?";
    $stmt = $mysqli->prepare($sql);
    $previousQueueNumber = $currentQueueNumber - 1;
    $stmt->bind_param("i", $previousQueueNumber);
    $stmt->execute();
    $stmt->bind_result($previousQueueId);
    $stmt->fetch();
    $stmt->close();

    if (!$previousQueueId) {
        // No item found above the current one
        $mysqli->rollback();
        return json_encode(["status" => "error", "message" => "No item above to swap."]);
    }

    // Swap the queue numbers
    $sql = "UPDATE printer_queue SET queue_number = CASE 
                WHEN id = ? THEN ? 
                WHEN id = ? THEN ? 
            END 
            WHERE id IN (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iiiiii", $queueId, $previousQueueNumber, $previousQueueId, $currentQueueNumber, $queueId, $previousQueueId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $mysqli->commit();
    return json_encode(["status" => "success", "message" => "Poem moved up."]);
}

// Function to move a poem down in the queue
function moveDownInQueue($mysqli, $queueId) {
    // Begin transaction
    $mysqli->begin_transaction();

    // Get the current queue number of the item
    $sql = "SELECT queue_number FROM printer_queue WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $stmt->bind_result($currentQueueNumber);
    $stmt->fetch();
    $stmt->close();

    if (!$currentQueueNumber) {
        // Cannot move down if not found
        $mysqli->rollback();
        return json_encode(["status" => "error", "message" => "Cannot move down."]);
    }

    // Get the id of the item below the current item
    $sql = "SELECT id FROM printer_queue WHERE queue_number = ?";
    $stmt = $mysqli->prepare($sql);
    $nextQueueNumber = $currentQueueNumber + 1;
    $stmt->bind_param("i", $nextQueueNumber);
    $stmt->execute();
    $stmt->bind_result($nextQueueId);
    $stmt->fetch();
    $stmt->close();

    if (!$nextQueueId) {
        // No item found below the current one
        $mysqli->rollback();
        return json_encode(["status" => "error", "message" => "No item below to swap."]);
    }

    // Swap the queue numbers
    $sql = "UPDATE printer_queue SET queue_number = CASE 
                WHEN id = ? THEN ? 
                WHEN id = ? THEN ? 
            END 
            WHERE id IN (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iiiiii", $queueId, $nextQueueNumber, $nextQueueId, $currentQueueNumber, $queueId, $nextQueueId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $mysqli->commit();
    return json_encode(["status" => "success", "message" => "Poem moved down."]);
}

// Function to move a poem to the top of the queue
function moveToTopOfQueue($mysqli, $queueId) {
    // Begin transaction
    $mysqli->begin_transaction();

    // Get the current queue number of the item
    $sql = "SELECT queue_number FROM printer_queue WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $stmt->bind_result($currentQueueNumber);
    $stmt->fetch();
    $stmt->close();

    if (!$currentQueueNumber || $currentQueueNumber == 1) {
        // Cannot move to top if it's already at the top or not found
        $mysqli->rollback();
        return json_encode(["status" => "error", "message" => "Cannot move to top."]);
    }

    // Update all items with a queue number less than the current one to increment their queue number by 1
    $sql = "UPDATE printer_queue SET queue_number = queue_number + 1 WHERE queue_number < ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $currentQueueNumber);
    $stmt->execute();
    $stmt->close();

    // Set the queue number of the current item to 1
    $sql = "UPDATE printer_queue SET queue_number = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $queueId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $mysqli->commit();
    return json_encode(["status" => "success", "message" => "Poem moved to top."]);
}

// Handle requests
$action = $_POST['action'] ?? '';
$poemId = $_POST['poem_id'] ?? '';
$queueId = $_POST['queue_id'] ?? '';

switch ($action) {
    case 'view':
        echo viewQueue($mysqli);
        break;
    case 'add':
        if ($poemId) {
            echo addToQueue($mysqli, $_SESSION["username"], $poemId);
        } else {
            echo json_encode(["status" => "error", "message" => "Poem ID is required."]);
        }
        break;
    case 'remove':
        if ($queueId) {
            if (removeFromQueue($mysqli, $queueId)) {
                echo json_encode(["status" => "success", "message" => "Poem removed from queue."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to remove poem from queue."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Queue ID is required."]);
        }
        break;
    case 'move_up':
        if ($queueId) {
            echo moveUpInQueue($mysqli, $queueId);
        } else {
            echo json_encode(["status" => "error", "message" => "Queue ID is required."]);
        }
        break;
    case 'move_down':
        if ($queueId) {
            echo moveDownInQueue($mysqli, $queueId);
        } else {
            echo json_encode(["status" => "error", "message" => "Queue ID is required."]);
        }
        break;
    case 'move_top':
        if ($queueId) {
            echo moveToTopOfQueue($mysqli, $queueId);
        } else {
            echo json_encode(["status" => "error", "message" => "Queue ID is required."]);
        }
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$mysqli->close();

?>