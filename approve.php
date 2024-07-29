<?php
  # Preset login credentials for the login page.
  # KEEP THIS INFORMATION SECURE.
  $login_username = "John Xina";
  $login_password = "bingchilling";

  # Do not change the variable below
  $servername = "localhost";

  # Name of the poem database.
  $dbname = "library_poem_project";

  # MySQL credentials for the server.
  # KEEP THIS INFORMATION SECURE
  # The database is password protected.
  # The database cannot be read or written to without the correct MySQL username and password.
  # The MySQL credentials should be the same as the phpMyAdmin portal.
  $username = "root";
  $password = "62VNmTy*y9E6";


  # Get IP address of the request.
  # For security reasons.
  if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  # Check if an admin submits a username and password through the login form.
  if(isset($_POST["username"]) && isset($_POST["password"])) {
    # Check to make sure the login information is correct.
    if($_POST["username"] == $login_username && $_POST["password"] == $login_password) {

      # Initiate a connection with the MySQL database.
      $conn = new mysqli($servername, $username, $password, $dbname);
      # If there is an error, output the error.
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      # If an admin sends an approve request, the code below will update the database to approve the requested poem.
      if(isset($_POST["approve"])) {
        # The function, mysqli_real_escape_string, serves to avoid potential MySQL injections.

        # The variable below stores the updated poem information.
        $content = mysqli_real_escape_string($conn, $_POST["content"]);
        # The variable below stores the approval ip information.
        $display_ip = mysqli_real_escape_string($conn, $ip);
        # The variable below stores the user agent of the poem approver.
        $user_agent = mysqli_real_escape_string($conn, $_SERVER["HTTP_USER_AGENT"]);
        # The following variable stores the date and time the poem was approved.
        $date = mysqli_real_escape_string($conn, date("Y-m-d h:i:sa"));
        # If a poem is edited, this variable stores the original, non-edited poem.
        # Only the edited copy of poems will be shown to library users.
        $org = mysqli_real_escape_string($conn, $_POST["org"]);

        # SQL update code.
        $sql = "UPDATE poems SET approved=1, content='" . $content . "', ip_of_approver='".$display_ip."', time_of_approval='".$date."', user_agent_of_approver='".$user_agent."', edited=".$_POST['edited'].", non_edited_entry='".$org."' WHERE id='" . $_POST["approve"] . "'";

        # Run the SQL script.
        # If the script ran sucessfully, output success otherwise output error.
        if (mysqli_query($conn, $sql)) {
          echo "success";
        } else {
          echo "error";
        }

      # If an admin chooses to remove a selected poem, the code below will hide the poem.
      } else if(isset($_POST["remove"])) {
        # The script functions by setting the approval id to negative one.
        # The code does not delete the poem but will hide it from both admin and user view.
        $sql = "UPDATE poems SET approved=-1 WHERE id='".mysqli_real_escape_string($conn, $_POST["remove"])."'";

        # Run the SQL script.
        # If the script ran sucessfully, output success otherwise output error.
        if (mysqli_query($conn, $sql)) {
          echo "success";
        } else {
          echo "error";
        }
      # The poems that await to be approved are fetched using the code below.
      } else {
        # The SQL code below fetches the name, email, timestampt, content, and id from poems that have not yet been approved.
        # It will not fetch rejected poems.
        $sql = "SELECT name, email, timestamp, content, id FROM poems WHERE approved=0";
        $result = $conn->query($sql);

        # The following code creates a table containing each awaiting poem.
        echo "<ion-content><table>";
        echo "<tr>";
        echo "<td>Name</td>";
        echo "<td>Email</td>";
        echo "<td>Timestamp</td>";
        echo "<td>Poem</td>";
        echo "<td>Approve?</td>";
        echo "</tr></ion-content>";
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . $row['timestamp'] . "</td>";
                echo "<td class='content_td' style='width:350px!important'><textarea id='".$row["id"]."org' style='display: none;'>".$row['content'] ."</textarea><textarea class='edit_poem_box' id='".$row["id"]."' oninput='auto_grow(this)' spellcheck='false'>" . $row['content'] . "</textarea></td>";
                echo "<td style='width: 200px;'><button class='approve_btn' id='".$row["id"]."' onclick='yeah'><ion-icon name='checkmark-outline' style='color: #70de1f'></ion-icon></button> <button class='delete_btn' id='".$row["id"]."'><ion-icon name='trash-outline' style='color: #de4f1f;'></ion-icon></button></td>";
                echo "</tr>";
          }
        }
        echo "</table>";

        # The MySQL connection is closed.
        $conn->close();
      }
    # If the username and password sent is incorrect, "wrong" will be printed to signify incorrect credentials.
    } else {
      echo "wrong";
    }
  # If no username or password is sent, the login screen will be displayed.
  } else {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="./styling/approve_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  </head>
  <body>
    <div class="global_container">
      <div class="login_container animate__animated">
        <div class="label">
          Username <span class="required">*</span>
        </div>
        <input type="text" spellcheck="false" required id="name" />
        <div class="label">
          Password <span class="required">*</span>
        </div>
        <input type="password" spellcheck="false" required id="password" />
        <input type="submit" class="login" value="Login" />
      </div>
      <div class="approve_container animate__animated" style="display:none;">
      </div>
    </div>
    <script src="./scripts/approve.js"></script>
    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons.js"></script>
  </body>
</html>
<?php } ?>
