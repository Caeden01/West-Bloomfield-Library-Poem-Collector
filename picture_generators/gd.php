<?php
// WARNING
// THIS FEATURE IS STILL EXPERIMENTAL
// Glitches are common!
// Most of the time though this works well and probably the best.
// Make sure to keep your auth_token private.

require "./include.php";
if (!isset($_GET["auth_token"]) || $_GET["auth_token"] != $auth_token) {
    header("HTTP/1.1 401 Unauthorized");
    die("Invalid Request - You need authorization.");
}
require "./libraries/qrcode.php";

// DO NOT USE FOR ANYTHING THAT'S NOT THE FIRST ID IN THE LIST
// TO DO: Make this function more general for $id
function removeFirstFromQueue($conn, $id, $queueNumber) {
    // Delete the item from the queue
    $deleteSql = "DELETE FROM printer_queue WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    if (!$deleteStmt) {
        // Rollback and handle prepare error
        $conn->rollback();
        die("Error preparing delete statement: " . $conn->error);
        return null;
    }
    $deleteStmt->bind_param("i", $id);
    $deleteStmt->execute();

    if (!$deleteStmt->affected_rows) {
        // Rollback and handle delete error
        $conn->rollback();
        die("Error deleting queue item: " . $conn->error);
        return null;
    }

    // Check if there are remaining items in the queue
    $checkRemainingSql = "SELECT COUNT(*) FROM printer_queue WHERE queue_number > ?";
    $checkRemainingStmt = $conn->prepare($checkRemainingSql);
    if (!$checkRemainingStmt) {
        // Rollback and handle prepare error
        $conn->rollback();
        die("Error preparing check remaining statement: " . $conn->error);
        return null;
    }
    $checkRemainingStmt->bind_param("i", $queueNumber);
    $checkRemainingStmt->execute();
    $checkRemainingStmt->bind_result($count);
    $checkRemainingStmt->fetch();
    $checkRemainingStmt->close();

    // Adjust queue numbers of remaining items if necessary
    if ($count > 0) {
        $adjustSql = "UPDATE printer_queue SET queue_number = queue_number - 1 WHERE queue_number > ?";
        $adjustStmt = $conn->prepare($adjustSql);
        if (!$adjustStmt) {
            // Rollback and handle prepare error
            $conn->rollback();
            die("Error preparing adjust statement: " . $conn->error);
            return null;
        }
        $adjustStmt->bind_param("i", $queueNumber);
        $adjustStmt->execute();

        if (!$adjustStmt->affected_rows) {
            // Rollback and handle update error
            $conn->rollback();
            die("Error updating queue numbers: " . $conn->error);
            return null;
        }
    }

    // Commit transaction
    $conn->commit();
}

// Fetch random approved poem or first item from queue
function fetchRandomApprovedPoem($conn) {
    // Begin transaction
    $conn->begin_transaction();

    // Fetch the first item from the queue
    $queueSql = "SELECT id, poem_id, queue_number FROM printer_queue ORDER BY queue_number ASC LIMIT 1";
    $queueResult = $conn->query($queueSql);

    if (!$queueResult) {
        // Rollback and handle query error
        $conn->rollback();
        die("Error fetching queue item: " . $conn->error);
        return null;
    }

    if ($queueResult->num_rows > 0) {
        $queueRow = $queueResult->fetch_assoc();

        // Retrieve the poem details using poem_id
        $poemSql = "SELECT id, name, email, title, poem, timestamp FROM poems WHERE id = ?";
        $poemStmt = $conn->prepare($poemSql);
        if (!$poemStmt) {
            // Rollback and handle prepare error
            $conn->rollback();
            die("Error preparing poem statement: " . $conn->error);
            return null;
        }
        $poemStmt->bind_param("s", $queueRow['poem_id']);
        $poemStmt->execute();
        $poemResult = $poemStmt->get_result();

        if (!$poemResult) {
            // Rollback and handle query error
            $conn->rollback();
            die("Error fetching poem details: " . $conn->error);
            return null;
        }

        if ($poemResult->num_rows > 0) {
            $poemRow = $poemResult->fetch_assoc();

            // Update print history
            updatePrintHistory($conn, $poemRow['id']);

            removeFirstFromQueue($conn, $queueRow["id"], $queueRow["queue_number"]);

            return $poemRow;
        } else {
            removeFirstFromQueue($conn, $queueRow["id"], $queueRow["queue_number"]);
            return fetchRandomApprovedPoem($conn);
        }
    } else {
        // Rollback transaction if queue is empty
        $conn->rollback();

        // Fetch a random approved poem
        $sql = "SELECT id, name, email, title, poem, timestamp FROM poems WHERE status = 'Approved' ORDER BY RAND() LIMIT 1";
        $result = $conn->query($sql);

        if (!$result) {
            // Handle query error
            die("Error fetching random poem: " . $conn->error);
            return null;
        }

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            updatePrintHistory($conn, $row['id']);
            return $row;
        }
    }
    return null;
}

// Update print history
function updatePrintHistory($conn, $id) {
    $stmt = $conn->prepare("UPDATE poems SET printHistory = JSON_ARRAY_APPEND(printHistory, '$', JSON_OBJECT('time', ?)) WHERE id = ? AND status = 'Approved'");
    $currentTime = date("F j, Y \a\\t g:i A");
    $stmt->bind_param("ss", $currentTime, $id);
    $stmt->execute();
    $stmt->close();
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$poem_data = fetchRandomApprovedPoem($conn);
$conn->close();

if (!$poem_data) {
    die("No poem found.");
}

// Scaling ratio for GD
$scaleRatio = 1;

// Adjust font size function
function adjustFontSize($size) {
    global $scaleRatio;
    return (int)($size * $scaleRatio);
}

// Wrap text function
function wrapText($text, $fontFile, $fontSize, $maxWidth) {
    $wrappedText = '';
    $lines = explode("\n", $text);

    foreach ($lines as $line) {
        $words = explode(' ', $line);
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $testLine);
            $lineWidth = $bbox[2] - $bbox[0];

            if ($lineWidth > $maxWidth) {
                // If breaking by word exceeds maxWidth, try breaking by characters
                if ($currentLine) {
                    $wrappedText .= trim($currentLine) . "\n";
                }
                $currentLine = '';

                // Attempt to break the word into characters
                for ($i = 0; $i < strlen($word); $i++) {
                    $char = substr($word, $i, 1);
                    $testLine = $currentLine . $char;
                    $bbox = imagettfbbox($fontSize, 0, $fontFile, $testLine);
                    $lineWidth = $bbox[2] - $bbox[0];

                    if ($lineWidth > $maxWidth) {
                        $wrappedText .= trim($currentLine) . "\n";
                        $currentLine = $char;
                    } else {
                        $currentLine = $testLine;
                    }
                }
            } else {
                $currentLine = $testLine;
            }
        }
        $wrappedText .= trim($currentLine) . "\n";
    }
    return $wrappedText;
}

// Function to calculate the width of the text
function getTextWidth($fontFile, $fontSize, $text) {
    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    return abs($bbox[2] - $bbox[0]);
}

function centerText($image, $fontFile, $text, $x, $y, $maxWidth, $fontSize) {
    $textWidth = getTextWidth($fontFile, $fontSize, $text);
    $x = intval(($maxWidth - $textWidth) / 2 + $x);
    $bbox = imagettftext($image, $fontSize, 0, $x, $y , imagecolorallocate($image, 0, 0, 0), $fontFile, $text);
    $lineHeight = $bbox[1] - $bbox[7];
    return $lineHeight;
}

// Adjust annotateText function for centering
function annotateText($image, $fontFile, $text, $x, $y, $maxWidth, $fontSize, $align = 'left') {
    $lines = explode("\n", wrapText($text, $fontFile, $fontSize, $maxWidth));
    $lineHeight = 0;
    
    foreach ($lines as $line) {
        // Adjust X based on alignment
        if ($align === 'center') {
            $textWidth = getTextWidth($fontFile, $fontSize, $line);
            $x = ($maxWidth - $textWidth) / 2 - 10;
        }
        $bbox = imagettftext($image, $fontSize, 0, $x, $y, imagecolorallocate($image, 0, 0, 0), $fontFile, $line);
        $lineHeight = max($bbox[1] - $bbox[7], $lineHeight);
        $y += $lineHeight + 10; // Adjust line height as needed
    }
    return $y;
}

// Function to convert image to grayscale
function convertToGrayscale($image) {
    imagefilter($image, IMG_FILTER_GRAYSCALE);
}

// Define image dimensions
$imageWidth = 400;
$imageHeight = 0; // To be calculated later

// Create a temporary image to calculate text height
$image = imagecreatetruecolor($imageWidth, 1);
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);
$fontFile = './fonts/noto_sans.ttf';

$footerFontSize = adjustFontSize(16);
$footerText = "Sent in by " . $poem_data["name"] . " on " . $poem_data["timestamp"] . ".";

// Calculate poem height
$dummyImage = imagecreatetruecolor($imageWidth, 1);
imagefill($dummyImage, 0, 0, $white);
$poemFontSize = adjustFontSize(16);
$poemHeight = annotateText($dummyImage, $fontFile, $poem_data["poem"], 0, 0, $imageWidth, $poemFontSize);
$footerHeight = annotateText($image, $fontFile, $footerText, 0, $poemHeight - 10, $imageWidth, $footerFontSize);
imagedestroy($dummyImage);

// Create the final image
$imageHeight = $footerHeight + 680; // Adjust height
$image = imagecreatetruecolor($imageWidth, $imageHeight);
imagefill($image, 0, 0, $white);

// Add header text
$headerFontSize = adjustFontSize(16);
$headerHeight = annotateText($image, $fontFile, library_name . " Poem Receipt Printer!", 200, 37, 200, $headerFontSize);

$logo_width_new = 200;
$logo_height_new = 163;

// Add logo
$logo = imagecreatefrompng(library_logo_bw);
// Create a white background image for the resized logo
$logoWidth = imagesx($logo);
$logoHeight = imagesy($logo);
$logoResized = imagecreatetruecolor($logo_width_new, $logo_height_new);
// Set the background color to white
$white = imagecolorallocate($logoResized, 255, 255, 255);
imagefill($logoResized, 0, 0, $white);
// Copy and resize the logo with transparency preserved
imagecopyresampled($logoResized, $logo, 0, 0, 0, 0, $logo_width_new, $logo_height_new, $logoWidth, $logoHeight);
// Copy the resized logo onto the main image
imagecopy($image, $logoResized, 0, 0, 0, 0, $logo_width_new, $logo_height_new);
// Clean up
imagedestroy($logo);
imagedestroy($logoResized);

// Add QR code
$qr_code = imagecreatefrompng(website_qr_code);
$qr_code_resized = imagecreatetruecolor(167, 167);
imagecopyresampled($qr_code_resized, $qr_code, 0, 0, 0, 0, 167, 167, imagesx($qr_code), imagesy($qr_code));
imagecopy($image, $qr_code_resized, 5, 190, 0, 0, 167, 167);
imagedestroy($qr_code);
imagedestroy($qr_code_resized);

// Add scan message
$scanMessageFontSize = adjustFontSize(16);
$scanMessageHeight = annotateText($image, $fontFile, "Send in your own poem scan the QR code to the left!", 200, 227, 200, $scanMessageFontSize);

// Add title (centered)
$titleFontSize = adjustFontSize(24);
$drawTitleHeight = centerText($image, $fontFile, $poem_data["title"], -7.5, 413, $imageWidth + 7.5, $titleFontSize);

// Add poem
$poemHeight = annotateText($image, $fontFile, $poem_data["poem"], 0, 450, $imageWidth, $poemFontSize);

// Add footer text
$footerHeight = annotateText($image, $fontFile, $footerText, 0, $poemHeight - 10, $imageWidth, $footerFontSize);

// Add QR code for comments
ob_start();
$generator = new QRCode(url_comment_path . scrambleLetters($poem_data["id"]), ["s" => "qr-".qr_code_quality, "p" => -16]);
$qr_code_img = $generator->render_image();
imagepng($qr_code_img);
$blob = ob_get_clean();
imagedestroy($qr_code_img);

$comment_qr_code = imagecreatefromstring($blob);
$comment_qr_code_resized = imagecreatetruecolor(190, 190);
imagecopyresampled($comment_qr_code_resized, $comment_qr_code, 0, 0, 0, 0, 190, 190, imagesx($comment_qr_code), imagesy($comment_qr_code));
imagecopy($image, $comment_qr_code_resized, 0, $footerHeight - 10, 0, 0, 190, 190);
imagedestroy($comment_qr_code);
imagedestroy($comment_qr_code_resized);

// Add comment request message
$commentMessageFontSize = adjustFontSize(16);
$commentMessageHeight = annotateText($image, $fontFile, "Do you like the poem you read? Why not leave a comment for the writer? Scan the QR code to the left.", 200, $footerHeight - 15, 200, $commentMessageFontSize);

// Resize image to 384 width while maintaining aspect ratio
$newWidth = 384;
$newHeight = (int)(($newWidth / $imageWidth) * $imageHeight);

$resizedImage = imagecreatetruecolor($newWidth, $newHeight);
$white = imagecolorallocate($resizedImage, 255, 255, 255);
imagefill($resizedImage, 0, 0, $white);

// Resample the image to the new size
imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);

// Convert the resized image to grayscale
convertToGrayscale($resizedImage);

// Output the final image
header('Content-Type: image/png');
imagepng($resizedImage);

// Clean up
imagedestroy($image);
imagedestroy($resizedImage);
?>
