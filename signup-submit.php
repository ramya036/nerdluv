<?php
include("session_check.php"); 
include("top.php"); 
?>
<div>
    <?php
    function validateInput(): array
    {
        $errors = [];

        // Check for empty name
        $name = $_POST["name"] ?? '';
        if (empty($name)) {
            $errors[] = "Name should not be blank.";
        } else {
            // Check for existing name
            if (isNameTaken($name)) {
                $errors[] = "Name already exists. Please use a different name.";
            }
        }

        // Validate age
        if (!validateAge($_POST["age"] ?? null)) {
            $errors[] = "Age should be a number between 0 and 99.";
        }

        // Validate gender
        if (!validateGender($_POST["gender"] ?? null)) {
            $errors[] = "Gender should be either 'M' for Male or 'F' for Female.";
        }

        // Validate personality type
        if (!validatePersonalityType($_POST["type"] ?? null)) {
            $errors[] = "Personality type should be a valid 4-letter Keirsey type.";
        }

        // Validate OS
        if (!validateOS($_POST["OS"] ?? null)) {
            $errors[] = "Favorite OS should be one of the choices provided.";
        }

        // Validate seeking age range
        if (!validateSeekingAge($_POST["min"] ?? null, $_POST["max"] ?? null)) {
            $errors[] = "Minimum seeking age must be less than or equal to maximum seeking age and within the valid range.";
        }

        // Validate profile picture
        if (!validateProfilePicture($_FILES["profile_picture"] ?? [])) {
            $errors[] = "Profile picture upload failed. It must be a valid image format (JPEG, PNG, GIF, JPG, WEBP).";
        }

        return $errors;
    }

    function isNameTaken($name): bool
    {
        $filePath = "singles.txt";
        if (!file_exists($filePath)) {
            return false;
        }

        $file = fopen($filePath, "r");
        while (($line = fgets($file)) !== false) {
            $data = explode(",", trim($line));
            if (isset($data[0]) && $data[0] === $name) {
                fclose($file);
                return true;
            }
        }
        fclose($file);
        return false;
    }

    function validateAge($age): bool
    {
        return isset($age) && preg_match("/^[0-9]{1,2}$/", $age) && (int)$age >= 0 && (int)$age <= 99;
    }

    function validateGender($gender): bool
    {
        return isset($gender) && in_array($gender, ["M", "F"]);
    }

    function validatePersonalityType($type): bool
    {
        return isset($type) && preg_match("/^[IE][NS][FT][JP]$/", $type);
    }

    function validateOS($os): bool
    {
        $allowedOS = ["Windows", "Mac OS X", "Linux"];
        return isset($os) && in_array($os, $allowedOS);
    }

    function validateSeekingAge($min, $max): bool
    {
        return validateAge($min) && validateAge($max) && (int)$min <= (int)$max;
    }

    function validateProfilePicture($file): bool
    {
        return isset($file["error"]) && $file["error"] === UPLOAD_ERR_OK &&
               in_array(mime_content_type($file["tmp_name"]), ["image/jpeg", "image/png", "image/gif", "image/webp"]);
    }

    $errors = validateInput();
    if (!empty($errors)) {
        echo "<h1>Error! Invalid Data</h1>
              <p>We're sorry. You submitted invalid information. Please go back and try again.</p><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    } else {
        $imagePath = saveImage();
        writeToFile($imagePath);
        ?>
        <h1>Thank you!</h1>
        <p>
            Welcome to NerdLuv, <?= htmlspecialchars($name) ?>!<br/><br/>
            Now <a href="matches.php">log in to see your matches!</a>
        </p>
        <?php
    }
    ?>
</div>

<?php
function saveImage(): ?string
{
    $targetDir = "images/";
    $imageFileType = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $targetFile = $targetDir . basename($_POST["name"]) . "." . $imageFileType;

    return move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile) ? $targetFile : null;
}

function writeToFile($imagePath)
{
    $userInfo = implode(",", array_map('htmlspecialchars', $_POST)) . "," . htmlspecialchars($imagePath);
    file_put_contents("singles.txt", "\n" . $userInfo, FILE_APPEND);
}

include("bottom.php");
?>
