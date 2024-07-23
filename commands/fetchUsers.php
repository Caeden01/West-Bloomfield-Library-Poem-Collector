<?php
session_start();
require "../include.php";

if(!isset($_SESSION["logged_in"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: you cannot access this service because you are not logged in.");
}
// Establish database connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if(isUserBanned($mysqli, $_SESSION["username"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: You are banned.");
}
if($_SESSION["tier"] != "high") {
    header("HTTP/1.1 400 Bad Request");
    die("Error: your permissions aren't high enough to access this service.");
}
function isValidUsername($username) {
    // Define the regular expression pattern to match alphanumeric characters and underscores
    $pattern = '/^[a-zA-Z0-9_]+$/';

    // Check if the username matches the pattern
    if (preg_match($pattern, $username)) {
        return true;
    } else {
        return false;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST["username"]) && isset($_POST["tier"]) && $_POST['action'] === 'create_account') {
        $username = $_POST['username'];
        if(!isValidUsername($username)) {
            header("HTTP/1.1 400 Bad Request");
            die("Error: Invalid Username. You cannot have spaces or special characters");
        }
        if(strlen($username) > 25) {
            header("HTTP/1.1 400 Bad Request");
            die("Error: Invalid Username. Username too long.");
        }
        $tier = $_POST['tier'];
        $parent_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Check if username already exists
        $check_query = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $check_query->bind_param('s', $username);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            header("HTTP/1.1 400 Bad Request");
            die("Username '$username' already exists.");
        } else {
            // Proceed with account creation
            $query = $mysqli->prepare("INSERT INTO users (username, tier, parent_id) VALUES (?, ?, ?)");
            $query->bind_param('ssi', $username, $tier, $parent_id);
            if ($query->execute()) {
                $success = "Account created successfully.";
                $user_id = $query->insert_id;
                
                // Create a unique event name using the user ID
                $event_name = "delete_user_" . $user_id;

                // Prepare the query string
                $event_query_string = "
                    CREATE EVENT $event_name
                    ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 15 MINUTE
                    DO DELETE FROM users WHERE id = $user_id
                ";

                // Execute the query directly
                if (!$mysqli->query($event_query_string)) {
                    header("HTTP/1.1 400 Bad Request");
                    die("Failed to schedule account deletion.");
                }
            } else {
                header("HTTP/1.1 400 Bad Request");
                die("Failed to create account.");
            }

        }
    } elseif (isset($_POST['action']) && isset($_POST["user_id"]) && ($_POST['action'] === 'ban' || $_POST['action'] === 'unban')) {
        $user_id = $_POST['user_id'];
        
        // Function to check if $user_id is a descendant of $_SESSION['user_id']
        function isDescendantOf($child_id, $parent_id) {
            global $mysqli;
            
            // Base case: If $child_id matches $parent_id
            if ($child_id == $parent_id) {
                return true;
            }
            
            // Recursive case: Check if $child_id's parent_id is $parent_id or its parent_id is a descendant of $parent_id
            $query = $mysqli->prepare("SELECT parent_id FROM users WHERE id = ?");
            $query->bind_param('i', $child_id);
            $query->execute();
            $query->bind_result($next_parent_id);
            $query->fetch();
            $query->close();
            
            // If $next_parent_id is null, $child_id has no parent (invalid scenario)
            if ($next_parent_id === null) {
                return false;
            }
            
            return isDescendantOf($next_parent_id, $parent_id);
        }    
        // Check if $user_id is a descendant of $_SESSION['user_id']
        if (isDescendantOf($user_id, $_SESSION['user_id'])) {
            if ($_POST['action'] === 'ban') {
                $query = $mysqli->prepare("UPDATE users SET banned = 1 WHERE id = ?");
                $success_message = "Account banned successfully.";
            } elseif ($_POST['action'] === 'unban') {
                $query = $mysqli->prepare("UPDATE users SET banned = 0 WHERE id = ?");
                $success_message = "Account unbanned successfully.";
            }
            $query->bind_param('i', $user_id);
            if ($query->execute()) {
                $success = $success_message;
            } else {
                die("Failed to perform action.");
            }
        } else {
            die("You do not have permission to perform this action on this account.");
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'get_all_users') {
        echo json_encode(getAllUsers());
    }
}
function getAllUsers() {
    global $mysqli;
    
    $query = "SELECT id, username, parent_id, banned, tier FROM users";
    $result = $mysqli->query($query);
    
    if ($result === false) {
        return [];
    }
    
    $users = array("user_id" => $_SESSION['user_id'], "users" => []);
    while ($row = $result->fetch_assoc()) {
        $users["users"][] = $row;
    }
    
    return $users;
}

ob_end_flush(); // End output buffering and flush output
?>
