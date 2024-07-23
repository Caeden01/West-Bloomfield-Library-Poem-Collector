<?php
require "./include.php";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
function getName($id) {
    global $conn;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Prepare SQL query to fetch the row from pending_poems
        $fetchSql = "
                SELECT name FROM poems WHERE id = ?
        ";

        // Prepare statement to fetch the row
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("s", $id);
        $fetchStmt->execute();
        $fetchResult = $fetchStmt->get_result();

        if ($fetchResult->num_rows > 0) {
            // Fetch the row data
            $row = $fetchResult->fetch_assoc();
            return $row["name"];
        } else {
          header("HTTP/1.1 400 Bad Request");
          die("Poem not found");
          
        }
          // Free result and close statement
        $fetchResult->free();
        $fetchStmt->close();
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        // Rollback the transaction in case of error
        $conn->rollback();
        $conn->close();
        throw $e;
    }
}
function addCommentsToApprovedPoem($id, $name, $comment, $commentSecurityInfo) {
  global $servername, $username, $password, $dbname;

  // Create connection using MySQLi
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $user_agent = $_SERVER["HTTP_USER_AGENT"];
  $securityInformationArray = json_decode($commentSecurityInfo, true);
  $securityInformationArray["user_agent"] = $user_agent;
  $securityInformationArray["ip"] = $_SERVER['REMOTE_ADDR'];
  $securityInformationArray["exact_time"] = date("Y-m-d h:i:sa");
  $securityInformationJSON = json_encode($securityInformationArray);
  try {
      // Prepare SQL query to update comments and commentSecurityInformation in approved_poems
      $sql = "
      UPDATE poems 
      SET 
          comments = JSON_ARRAY_APPEND(comments, '$', JSON_OBJECT(
              'name', ?,
              'time', ?,
              'comment', ?
          )),
          commentSecurityInformation = JSON_ARRAY_APPEND(commentSecurityInformation, '$', JSON_EXTRACT (?, '$') )
      WHERE id = ?
      ";
      $time = date("F j, Y \a\\t g:i A");
      // Prepare statement
      $stmt = $conn->prepare($sql);

      // Bind parameters
      $stmt->bind_param("sssss", $name, $time, $comment, $securityInformationJSON, $id);
      
      // Execute statement
      $stmt->execute();

      // Check if the update was successful
      if ($stmt->affected_rows > 0) {
          // Update successful
          $stmt->close();
          return true;
      } else {
          // No rows updated (probably no row with the given ID)
          header("HTTP/1.1 400 Bad Request");
          $stmt->close();
          return false;
      }
  } catch (Exception $e) {
      header("HTTP/1.1 500 Internal Server Error");
      // Handle exception
      $conn->close();
      throw $e;
  }
}
if(isset($_POST["name"]) && isset($_POST["comment"]) && isset($_GET["id"])) {
    $unscrambled_id = unscrambleLetters($_GET["id"]);
    if(addCommentsToApprovedPoem($unscrambled_id, $_POST["name"], $_POST["comment"], $_POST["securityInfo"])) { ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <link rel="stylesheet" href="./styling/comment_page.css" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
        <div class="global_container">
            <div class="comment_container container" style="max-width: 350px">
                <div class="flexbox">
              <img src="./images/checkmark.png" style="width: 125px;  margin-right: 10px;" />
              <div>
                  Congratulations, <?php echo $_POST["name"] ?>! Your comment was sucessfully posted.
              </div>
            </div>
            </div>
        </div>
        </body>
        </html>
    <?php 
    }
} else if(isset($_GET["id"])) {
  $unscrambled_id = unscrambleLetters($_GET["id"]);
  $name = getName($unscrambled_id);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="./styling/comment_page.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
  <div class="global_container">
    <div class="comment_container container">
      <h3>Respond to <?php 
        echo htmlspecialchars($name);
      ?>'s poem</h3>
      <form action="" method="post">
        <div class="label">
          Name <span class="required">*</span>
        </div>
        <input type="text" spellcheck="false" required id="name" name="name" />
        <div class="label">
          Comment <span class="required">*</span>
        </div>
        <input type="text" name="securityInfo" class="hidden" id="securityInfo" />
        <textarea placeholder="Type comment here..." maxlength="500" required name="comment" onkeydown="document.getElementById('char').innerHTML = this.value.length"></textarea>
        <p><span id="char">0</span> / 500 characters</p>
        <button type="submit" class="login_button">Submit</button>
      </form>
    </div>
  </div>
  <script>
// Function to get a cookie by name
var getCookie = (name) => {
  return localStorage.getItem(name);
};
var getCookieArray = (name) => {
  return JSON.parse(getCookie(name));
}
var getSecurityInformation = () => {
  const browserInfo = {
    appVersion: navigator.appVersion,
    platform: navigator.platform,
    language: navigator.language,
    languages: navigator.languages,
    cookiesEnabled: navigator.cookieEnabled,
    javaEnabled: navigator.javaEnabled(),
    online: navigator.onLine,
    screen: {
        width: screen.width,
        height: screen.height,
        availWidth: screen.availWidth,
        availHeight: screen.availHeight,
        colorDepth: screen.colorDepth,
        pixelDepth: screen.pixelDepth
    },
    saved_poems: getCookieArray("poems") || "N/A",
    memory: navigator.deviceMemory || "N/A",
    timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
  };

  return JSON.stringify(browserInfo, null, 2);
}
document.getElementById("securityInfo").value = getSecurityInformation();
  </script>
  </body>
</html>
<?php }
// Close connection
$conn->close();
?>