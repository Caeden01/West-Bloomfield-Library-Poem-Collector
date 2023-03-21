<?php
  # The key below serves to ensure users cannot artificially increase the number of prints counter by visiting this site. 
  # Keep the key secure.
  $PI_SECURITY_KEY = "lib_printer";

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

  if(isset($_GET["key"]) && $_GET["key"] == $PI_SECURITY_KEY) {
      # Initiate a connection with the MySQL database.
      $conn = new mysqli($servername, $username, $password, $dbname);
      # If there is an error, output the error.
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      # SQL request code.
      # The code below fetches an approved poem at random.
      $sql = mysqli_query($conn, "SELECT * FROM poems WHERE approved=1 order by RAND() limit 1");

      # The code below prints 
      while ($rows = mysqli_fetch_array($sql))
      {
        echo $rows["content"];
        ?>

        Written By <?php echo $rows["name"]."."; 
        
        $poem_id = $rows["id"];
        
        # Increment the number of prints variable in the database. 
        $sql_2 = "UPDATE poems SET num_prints = num_prints + 1  WHERE id='".$poem_id."'";
        mysqli_query($conn, $sql_2);
      }
  }
?>
