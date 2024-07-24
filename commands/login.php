<?php
ob_start(); // Start output buffering

require "../include.php";

session_start();
// Establish database connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

function isSecurePassword($password) {
    // Enforce password length
    $minLength = 8;

    // Check password length
    if (strlen($password) < $minLength) {
        return "Password must be at least $minLength characters long.";
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    
    // Check for at least one special character
    if (!preg_match('/[\W]/', $password)) {
        return "Password must contain at least one special character.";
    }

    return true;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($_POST['action'] === 'login') {
            if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
                echo json_encode(['success' => false, 'message' => 'Please sign out before signing back in.']);
                header("HTTP/1.1 401 Unauthorized");
                exit;
            }
            // Check if the user is banned
            $banned = isUserBanned($mysqli, $username);
            if ($banned) {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(['success' => false, 'message' => 'Your account has been banned.']);
                exit;
            }
            $query = $mysqli->prepare("SELECT username, id, password, tier FROM users WHERE username = ?");
            $query->bind_param('s', $username);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (empty($user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['tier'] = $user['tier'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['set_password'] = true;
                    echo json_encode(['success' => true, 'message' => 'Password is not set. Please set a password.', 'status' => 'Password not set']);
                    exit;
                } elseif (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['tier'] = $user['tier'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['logged_in'] = true;

                    echo json_encode(['success' => true, 'message' => 'Login successful.', 'status' => $user['tier'], 'user_id' => $user['id'], 'username' => $user['username']]);
                    exit;
                } else {
                    header("HTTP/1.1 401 Unauthorized");
                    echo json_encode(['success' => false, 'message' => 'Invalid password.']);
                    exit;
                }
            } else {
                header("HTTP/1.1 401 Unauthorized");
                echo json_encode(['success' => false, 'message' => 'Invalid username.']);
                exit;
            }
        } elseif ($_POST['action'] === 'set_password') {
            if (!isset($_SESSION['set_password']) && !$_SESSION["set_password"]) {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(['success' => false, 'message' => 'Cannot change password']);
                exit;
            }
            if (!isset($_POST['password'])) {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(['success' => false, 'message' => 'Password is required.']);
                exit;
            }
            $result = isSecurePassword($_POST['password']);

            if ($result !== true) {
                header("HTTP/1.1 400 Bad Request");
                echo $result;
                exit;  
            }

            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $query = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
            $query->bind_param('si', $password, $_SESSION['user_id']);
            if ($query->execute()) {
                // Cancel the scheduled deletion event
                if(!is_numeric($_SESSION['user_id'])) {
                    header("HTTP/1.1 400 Bad Request");
                    die("Cannot execute request");
                }
                $event_name = "delete_user_" . $_SESSION['user_id'];
                $drop_event_query_string = "DROP EVENT IF EXISTS $event_name";
                $mysqli->query($drop_event_query_string);
                unset($_SESSION['set_password']);
                $_SESSION["logged_in"] = true;
                echo json_encode(['success' => true, 'message' => 'Password set successfully.']);
                exit;
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(['success' => false, 'message' => 'Failed to set password.']);
                exit;
            }
        }
    } elseif (isset($_POST["action"]) && $_POST['action'] === 'check_login') {
        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            echo json_encode(['logged_in' => true, 'status' => $_SESSION['tier'], 'user_id' => $_SESSION['user_id'], 'username' => $_SESSION['username']]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
    } elseif (isset($_POST["action"]) && $_POST['action'] === 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        exit;
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
        exit;
    }
}

?>
