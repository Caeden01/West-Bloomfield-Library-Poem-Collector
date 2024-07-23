<?php
require "../include.php";

# If a poem request is sent to the server, run the code in the below if block.
if($_SERVER["REQUEST_METHOD"] == "POST"  && isset($_POST["name"]) && isset($_POST["title"]) && isset($_POST["poem"]) && isset($_POST["token"])) {
  if(!isValidCaptcha($_POST["token"])) {
    header("HTTP/1.1 400 Bad Request");
    die("Captcha is invalid.");
  }

  // Initiate a connection with the MySQL database.
  $conn = new mysqli($servername, $username, $password, $dbname);

  // If there is an error, output the error.
  if ($conn->connect_error) {
      header("HTTP/1.1 500 Internal Server Error");
      die("Server Error: There was an error entering your poem into our site's database. We have been notified of this error and will begin fixing it shortly.");
  }

  $name = $_POST["name"];
  $email = $_POST["email"];
  $title = $_POST["title"];
  $poem = $_POST["poem"];

  // Get the date and time of the submission.
  $date = date("F j, Y \a\\t g:i A");

  // Record IP and user agent information for security reasons.
  $user_agent = $_SERVER["HTTP_USER_AGENT"];
  $securityInformationJSON = $_POST["securityInformation"];
  $securityInformationArray = json_decode($securityInformationJSON, true);
  $securityInformationArray["user_agent"] = $user_agent;
  $securityInformationArray["ip"] = $_SERVER['REMOTE_ADDR'];
  $securityInformationArray["exact_time"] = date("Y-m-d h:i:sa");
  $securityInformationJSON = json_encode($securityInformationArray);

  // Each poem is assigned an ID to make sure it can be easily referenced.
  $id = uniqid();

  // Reject any poems which exceed the maximum character length.
  if(strlen($name) > $MAX_NAME_LENGTH) {
        header("HTTP/1.1 400 Bad Request");
        die("Server Error: Name is too long. Max Length is $MAX_NAME_LENGTH characters.");
  }
  if(strlen($email) > $MAX_EMAIL_LENGTH) {
    header("HTTP/1.1 400 Bad Request");
    die("Server Error: Email is too long. Max Length is $MAX_EMAIL_LENGTH characters.");
  }
  if(strlen($poem) > $MAX_POEM_LENGTH) {
    header("HTTP/1.1 400 Bad Request");
    die("Server Error: Poem has too many characters. Max length is $MAX_POEM_LENGTH characters. This length is imposed for custom poems that don't follow the traditional plain text implementation that's presented and instead import images or code in a custom HTML template. Please submit your poem with fewer images or characters to match the character limit.");
  }

  // MySQL script using prepared statements.
  $stmt = $conn->prepare("INSERT INTO poems (name, email, title, poem, timestamp, securityInformation, id, history, comments, commentSecurityInformation, printHistory) VALUES (?, ?, ?, ?, ?, ?, ?, '', '[]', '[]', '[]')");

  $stmt->bind_param("sssssss", $name, $email, $title, $poem, $date, $securityInformationJSON, $id);

  // Run the SQL script.
  if ($stmt->execute()) {
    header("HTTP/1.1 201 Created");
    echo "Congrats: $id";
  } else {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Server Error: There was an error entering your poem into our site's database. We have been notified of this error and will begin fixing it shortly.";
  }
}
?>