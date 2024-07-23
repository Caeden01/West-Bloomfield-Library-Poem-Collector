<?php
// WARNING UNSTABLE!!!
// Please fix bugs before using!!!
// Suffers major memory leak issues when coupled with Apache on  ImageMagick 6.9.11-60
// Make sure to upgrade to ImageMagick 7.1.1-35 - you may need to compile your own binaries.
// See https://stackoverflow.com/questions/78736667/problem-of-memory-leak-with-php-and-queryfontmetrics-from-imagick

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
function updatePrintHistory($conn, $id) {
    $stmt = $conn->prepare("UPDATE poems SET printHistory = JSON_ARRAY_APPEND(printHistory, '$', JSON_OBJECT('time', ?)) WHERE id = ?");
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

function wrapText($image, $draw, $text, $maxWidth) {
    $lines = preg_split('/\R/', $text);
    $wrappedLines = [];
    $lineHeight = 0;
    foreach ($lines as $line) {
        if (trim($line) === '') {
            $wrappedLines[] = '';
            continue;
        }
        $words = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
        $currentLine = '';
        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            $metrics = $image->queryFontMetrics($draw, $testLine);
            if ($metrics['textWidth'] > $maxWidth) {
                $wrappedLines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if (!empty($currentLine)) {
            $wrappedLines[] = trim($currentLine);
        }
        $lineHeight = max($metrics['textHeight'], $lineHeight);
    }
    return [$wrappedLines, $lineHeight];
}

function annotateText($image, $draw, $text, $x, $y, $maxWidth) {
    list($lines, $lineHeight) = wrapText($image, $draw, $text, $maxWidth);
    foreach ($lines as $i => $line) {
        $image->annotateImage($draw, $x, $y + $i * $lineHeight, 0, $line);
    }
    return $lineHeight * count($lines);
}

$draw = new ImagickDraw();
$draw->setGravity(1);
$draw->setFont('./fonts/noto_sans.ttf');
$draw->setFontSize(16);
$draw->setFillColor('black');

$image = new Imagick();

$image->setResourceLimit(imagick::RESOURCETYPE_MEMORY, 256);
$image->setResourceLimit(imagick::RESOURCETYPE_MAP, 256);
$image->setResourceLimit(imagick::RESOURCETYPE_AREA, 256);
$image->setResourceLimit(imagick::RESOURCETYPE_FILE, 256);
$image->setResourceLimit(imagick::RESOURCETYPE_DISK, -1);

$imageWidth = 300;
$imageHeight = 0; // To be calculated later
$image->newImage($imageWidth, 1, new ImagickPixel('white')); // Create a dummy image to calculate text heights

$dummyPoemImage = clone $image;
$poemHeight = annotateText($dummyPoemImage, $draw, $poem_data["poem"], 0, 0, 300);
$dummyPoemImage->clear();

$imageHeight += $poemHeight + 570; // Account for other elements
$image->newImage($imageWidth, $imageHeight, new ImagickPixel('white'));

// Add header text
$headerHeight = annotateText($image, $draw, library_name . " Poem Receipt Printer!", 150, 6, 140);

// Add logo
$logo = new Imagick(library_logo_bw);
$logo->resizeImage(150, 122, Imagick::FILTER_LANCZOS, 1);
$image->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, 0, 0);

// Add QR code
$qr_code = new Imagick(website_qr_code);
$qr_code->resizeImage(125, 125, Imagick::FILTER_LANCZOS, 1);
$image->compositeImage($qr_code, Imagick::COMPOSITE_DEFAULT, 5, 142);

// Add scan message
$scanMessageHeight = annotateText($image, $draw, "Send in your own poem scan the QR code to the left!", 150, 160.5, 140);

// Add title
$draw->setFontSize(24);
$draw->setGravity(2);
$image->annotateImage($draw, 0, 282, 0, $poem_data["title"]);

// Add poem
$draw->setFontSize(16);
$draw->setGravity(1);
$poemHeight = annotateText($image, $draw, $poem_data["poem"], 0, 320, 300);

// Add footer text
$endingHeight = 335 + $poemHeight;
$footerHeight = annotateText($image, $draw, "Sent in by ".$poem_data["name"]." on ".$poem_data["timestamp"].".", 0, $endingHeight, 300);
$endingHeight += $footerHeight + 10;

// Add QR code for comments
ob_start();
$generator = new QRCode(url_comment_path.scrambleLetters($poem_data["id"]), ["s" => "qr-m", "p" => -16]);
$qr_code_img = $generator->render_image();
imagepng($qr_code_img); 
$blob = ob_get_clean();
imagedestroy($qr_code_img);
$comment_qr_code = new Imagick(); 
$comment_qr_code->readImageBlob($blob);
$comment_qr_code->resizeImage(125, 125, Imagick::FILTER_LANCZOS, 1);
$image->compositeImage($comment_qr_code, Imagick::COMPOSITE_DEFAULT, 0, $endingHeight + 14);

// Add comment request message
$commentMessageHeight = annotateText($image, $draw, "Do you like the poem you read? Why not leave a comment for the writer? Scan the QR code to the left.", 145, $endingHeight, 150);

// Finalize image
$image->resizeImage(384, 0, Imagick::FILTER_LANCZOS, 1);
$image->setImageType(Imagick::IMGTYPE_GRAYSCALE);
$image->setImageFormat('png');

header('Content-type: image/png');
echo $image;

$image->clear();
$logo->clear();
$qr_code->clear();
$draw->clear();
$comment_qr_code->clear();
?>
