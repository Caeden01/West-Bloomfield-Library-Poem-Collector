<?php
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

  # Max character length of submitted name, email, and poem.
  $MAX_NAME_LENGTH = 100;
  $MAX_EMAIL_LENGTH = 100;

  # Max poem length is additionally referenced in script.js. 
  # If the variable below is changed, a reference needs to be changed in script.js.
  $MAX_POEM_LENGTH = 5000;
  
  # If a poem request is sent to the server, run the code in the below if block.
  if( isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["content"])) {
      # Initiate a connection with the MySQL database.
      $conn = new mysqli($servername, $username, $password, $dbname);
      # If there is an error, output the error.
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

    # Set the time zone to eastern standard time.
    date_default_timezone_set("America/New_York");
    
    # Run the submitted info through a special MySQL function to avoid MySQL injections.
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $content = mysqli_real_escape_string($conn, $_POST["content"]);
    # Get the date and time of the submission.
    $date = mysqli_real_escape_string($conn, date("Y-m-d h:i:sa"));
    
    # Record IP and user agent information for security reasons.
    # If a user is submitting inappropriate content, the code below can help track down or ban the user from the site.  
    $display_ip = mysqli_real_escape_string($conn, $ip);
    $user_agent = mysqli_real_escape_string($conn, $_SERVER["HTTP_USER_AGENT"]);
    
    # Each poem is assigned an ID to make sure it can be easily referenced.
    $id = mysqli_real_escape_string($conn, uniqid());
   
    # Reject any poems which exceed the maximum character length.
    # Users should not be able to exceed the max character length without malicious actions. (I.E. inspect element) 
    if(strlen($_POST["name"]) > $MAX_NAME_LENGTH) {
      die("error");
    }
    if(strlen($_POST["email"]) > $MAX_EMAIL_LENGTH) {
      die("error");
    }
    if(strlen($_POST["content"]) > $MAX_POEM_LENGTH) {
      die("error");
    }
    
    # MySQL script.
    $sql = "INSERT INTO poems (name, email, content, timestamp, ip_address, user_agent, num_prints, approved, id, non_edited_entry, ip_of_approver, time_of_approval, user_agent_of_approver, edited)
    VALUES ('$name', '$email', '$content', '$date', '$display_ip', '$user_agent', 0, false, '$id', '', '', '', '', false)";

    # Run the SQL script. 
    # If the script ran sucessfully, output the poem id otherwise output error.
    if ($conn->query($sql) === TRUE) {
      echo $id;
    } else {
      echo "error";
    }
    $conn->close();
  } else {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Poem Submission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="./styling/submission_page.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
      <div class="global_container">
        <div class="container get_started_container animate__animated" style="padding: 50px; padding-left: 75px; padding-right: 75px; height: 350px; width: 350px; display: block;">
          <form action="">
            <div class="context_info">
              Welcome to the West Bloomfield Library poem submission page! Please fill out the boxes below to continue.
            </div>
            <div class="label">
              Name <span class="required">*</span>
            </div>
            <input type="text" spellcheck="false" required id="name" maxlength="<?php echo $MAX_NAME_LENGTH; ?>" />
            <div class="label">
              Email <span class="required">*</span>
            </div>
            <input type="email" id="email" required maxlength="<?php echo $MAX_EMAIL_LENGTH; ?>" />
            <!-- Remove the code segment if terms and conditions is not necessary --> 
            <br/>
            <label class="checkbox bounce">
              <input type="checkbox" required>
              <svg viewBox="0 0 21 21">
                <polyline points="5 10.75 8.5 14.25 16 6"></polyline>
              </svg>
            </label>
            <div class="agree_info">
              I agree to the terms and conditions.
            </div>
            <!-- End of code segment -->
            <input type="submit" class="get_started" value="Get Started" />
          </form>
        </div>
        <div class="container write_container animate__animated" style="display: none;">
          <textarea class="textarea_poem" placeholder="Type Poem Here..." maxlength="<?php echo $MAX_POEM_LENGTH; ?>"></textarea>
          <div class="text_limit"><?php echo $MAX_POEM_LENGTH; ?> characters remaining</div>
          <button class="submit_poem">&#10148;</button>
        </div>
        <div class="container success_container animate__animated" style="display: none; color: rgb(220, 220, 220); text-align: left; width: 500px; height: 140px; padding: 40px;">
          <div class="submission_text">
            Your poem was sucessfully submitted, <span class="submit_name"></span>, with the id — <span class="poem_id"></span>. Don't lose it or you won't get to see how many times your poem is printed! Have a great day!
          </div>
          <button class="get_started" style="bottom: 35px;  padding: 15px 20px 15px 20px;" onclick="location.reload();">Submit Another</button>
        </div>
        </div>
    </div>
    <div class="footer">
      Made by <a target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/in/caeden-kidd-0957a1246/" style="color: orange">Caeden Kidd</a>, Ryan Sparago, Eesh Garg, and Max Gorman from the Coding Club at West Bloomfield High School. 
    </div>
    <script src="./scripts/main_script.js"></script>
  </body>
</html>
<?php } ?>