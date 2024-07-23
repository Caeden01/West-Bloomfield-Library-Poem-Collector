<?php 
require "../include.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["ids"])) {
    // Get JSON data from POST body
    $json = $_POST["ids"];
    $ids = json_decode($json, true);

    // Check if $ids is an array
    if (is_array($ids)) {
        // Fetch rows from the database based on IDs
        // Create connection using MySQLi
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare an empty array to store results
        $rows = [];

        // Prepare SQL query with a UNION to search in multiple tables and indicate source
        $sql = "
            SELECT id, name, email, title, poem, timestamp, comments, printHistory, status
            FROM poems WHERE id = ?
        ";

        // Prepare statement
        $stmt = $conn->prepare($sql);

        // Check if prepare() succeeded
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        // Bind parameters for each ID and execute queries
        $stmt->bind_param("s", $id);
        foreach ($ids as $id) {
            // Execute statement with bound parameters
            $stmt->execute();

            // Get result
            $result = $stmt->get_result();

            // Fetch associative array if row exists
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $row["printHistory"] =  json_decode($row["printHistory"], true);
                $row["comments"] =  json_decode($row["comments"], true);
                $rows[] = $row; // Add row to results array
            } else {
                $rows[] = null; // Add null if no row found
            }

            // Free result
            $result->free();
        }

        // Respond with JSON-encoded data
        header('Content-Type: application/json');
        echo json_encode($rows);

        // Close statement
        $stmt->close();

        // Close connection
        $conn->close();
    } else {
        // Respond with error message if input data is not valid
        header("HTTP/1.1 400 Bad Request");
        die("Invalid request");
    }
}
?>