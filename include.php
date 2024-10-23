<?php
  # Name of the poem database.
  $dbname = "wb_library_poem_project";

  # MySQL credentials for the server.
  # KEEP THIS INFORMATION SECURE
  # The database is password protected.
  # The database cannot be read or written to without the correct MySQL username and password.
  # The MySQL credentials should be the same as the phpMyAdmin portal.
  $username = "YOUR MYSQL USERNAME";
  $password = "YOUR MYSQL PASSWORD";

  # This auth token is for picture_generator.php
  # Make sure that this key is set in your printer driver code as well
  $auth_token = "YOUR AUTH TOKEN";

  # Below references the library name, logo, website, and QR Code.
  # If you want to customize this for a different library you can do so quite easily below.
  # This will change all references.

  # Name of Your Library
  define("library_name", "West Bloomfield Public Library");
  # Logo for Your Library
  define("library_logo", "./images/wblib_logo.svg");
  # Black And White Version Of Logo for Your Website
  define("library_logo_bw", "./images/bw_wblib_logo.png");
  # Your Library Website URL
  define("library_website", "https://wblib.org/");
  # Path of QR Code that redirects to your hosted version of this poem collector site.
  define("website_qr_code", "./images/qr_code.png");
  # Path of hosted comment.php file. Please leave "?id="!
  define("url_comment_path", "https://REPLACE_WITH_YOUR_PROJECT_URL_PATH/comment.php?id=");
  # Quality of QR code generation - default set to low.
  define("qr_code_quality", "l");

  # Below are variables to use Google reCAPTCHA.
  # This was put in place to prevent against spam.
  # If you wish to disable it, set "use_captcha" to false.
  # Otherwise please fill in your captcha public and private keys here.
  define("use_captcha", false);
  define("captcha_public_key", "YOUR CAPTCHA PUBLIC KEY");
  define("captcha_private_key", "YOUR CAPTCHA PRIVATE KEY");

  # Change the timezone if your library does match with this one.
  date_default_timezone_set("America/Detroit");

  # Do not change the variable below
  $servername = "localhost";

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
  $MAX_POEM_LENGTH = 10000;

  function scrambleLetters($input) {
    // Define a mapping of characters to scrambled characters
    $charMap = array(
        'a' => '7', 'b' => '2', 'c' => '9', 'd' => '4', 'e' => '0',
        'f' => '8', 'g' => '5', 'h' => '6', 'i' => '3', 'j' => '1',
        'k' => 'w', 'l' => 'x', 'm' => 'y', 'n' => 'z', 'o' => 'v',
        'p' => 'u', 'q' => 't', 'r' => 's', 's' => 'r', 't' => 'q',
        'u' => 'p', 'v' => 'o', 'w' => 'n', 'x' => 'm', 'y' => 'l',
        'z' => 'k', '0' => 'e', '1' => 'j', '2' => 'f', '3' => 'i',
        '4' => 'd', '5' => 'g', '6' => 'h', '7' => 'a', '8' => 'c', '9' => 'b'
    );

    $scrambled = '';

    // Convert input to lowercase to ensure consistent mapping
    $input = strtolower($input);

    // Iterate through each character in the input
    for ($i = 0; $i < strlen($input); $i++) {
        $char = $input[$i];

        // Check if the character exists in the map, otherwise keep it unchanged
        if (array_key_exists($char, $charMap)) {
            $scrambled .= $charMap[$char];
        } else {
            $scrambled .= $char; // Keep non-mapped characters unchanged
        }
    }

    return $scrambled;
}
function unscrambleLetters($input) {
  // Define the reverse mapping from scrambled characters to original characters
  $reverseMap = array(
      '7' => 'a', '2' => 'b', '9' => 'c', '4' => 'd', '0' => 'e',
      '8' => 'f', '5' => 'g', '6' => 'h', '3' => 'i', '1' => 'j',
      'w' => 'k', 'x' => 'l', 'y' => 'm', 'z' => 'n', 'v' => 'o',
      'u' => 'p', 't' => 'q', 's' => 'r', 'r' => 's', 'q' => 't',
      'p' => 'u', 'o' => 'v', 'n' => 'w', 'm' => 'x', 'l' => 'y',
      'k' => 'z', 'e' => '0', 'j' => '1', 'f' => '2', 'i' => '3',
      'd' => '4', 'g' => '5', 'h' => '6', 'a' => '7', 'c' => '8', 'b' => '9'
  );

  $unscrambled = '';

  // Convert input to lowercase to ensure consistent mapping
  $input = strtolower($input);

  // Iterate through each character in the input
  for ($i = 0; $i < strlen($input); $i++) {
      $char = $input[$i];

      // Check if the character exists in the reverse map, otherwise keep it unchanged
      if (array_key_exists($char, $reverseMap)) {
          $unscrambled .= $reverseMap[$char];
      } else {
          $unscrambled .= $char; // Keep non-mapped characters unchanged
      }
  }

  return $unscrambled;
}
// Function to check if the user is banned
function isUserBanned($conn, $username) {
    $query = $conn->prepare("SELECT banned FROM users WHERE username = ?");
    $query->bind_param('s', $username);
    $query->execute();
    $query->bind_result($banned);
    $query->fetch();
    $query->close();
    return $banned;
}
function isValidCaptcha($token) 
{
    if(!use_captcha) {
        return true;
    }
    try {

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret'   => captcha_private_key,
                 'response' => $token,
                 'remoteip' => $ip];
                 
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data) 
            ]
        ];
    
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result)->success;
    }
    catch (Exception $e) {
        return null;
    }
}
?>
