<?php 
require "../include.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    // Create a function to get the database connection
    function getDbConnection() {
        global $servername, $username, $password, $dbname;
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    // Function to check if ID exists in any table
    function checkIfIdExists($conn, $id) {
        // Prepare SQL query to check if ID exists in any table using prepared statements
        $sql = "
            SELECT 1 FROM poems WHERE id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    // Get the sanitized ID
    $id = $_POST["id"];

    // Get database connection
    $conn = getDbConnection();

    // Check if the ID exists
    $exists = checkIfIdExists($conn, $id);

    echo $exists ? 'true' : 'false';

    // Close connection
    $conn->close();
}
?>
