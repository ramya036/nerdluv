<?php
include("session_check.php");
include("top.php");
?>
<style>
    .match img {
        height: 100px;
        object-fit: cover;
    }
</style>

<h1>Matches for <?= htmlspecialchars($_GET["name"]) ?></h1>
<div class='match'>
    <?php printMatchesFromFile(); ?>
</div>

<?php include("bottom.php"); ?>

<?php

function printMatchesFromFile()
{
    // Fetch the user's name from the query string
    $name = $_GET["name"] ?? null;  // Using null coalescing to handle unset variable

    // Load the user data from the file
    $users = array_map('str_getcsv', file("singles.txt", FILE_IGNORE_NEW_LINES));

    // Find the logged-in user
    $loggedInUser = null;
    foreach ($users as $userData) {
        if ($userData[0] === $name) {
            $loggedInUser = $userData;
            break;
        }
    }

    // If the user does not exist, display a message
    if (!$loggedInUser) {
        echo "<p>User with the given name doesn't exist.</p>";
        return;
    }

    // Destructure the logged-in user's data
    list($loginName, $loginGender, $loginAge, $loginType, $loginOS, $loginMinAge, $loginMaxAge) = $loggedInUser;

    // Find matches for the logged-in user
    foreach ($users as $matchUserData) {
        list($matchName, $matchGender, $matchAge, $matchType, $matchOS) = $matchUserData;

        // Check matching criteria
        if (
            $matchName !== $loginName &&  
            $matchGender !== $loginGender && 
            $matchAge >= $loginMinAge && $matchAge <= $loginMaxAge &&
            $loginAge >= $matchUserData[5] && $loginAge <= $matchUserData[6] &&
            $matchOS === $loginOS &&
            countMatchingPersonalityLetters($loginType, $matchType) >= 1  
        ) {
            // Handle user image; default to a placeholder if none exists
            $userImage = $matchUserData[7] ?? 'images/user.jpg'; 
            echo renderUserProfile($matchName, $matchGender, $matchAge, $matchType, $matchOS, $userImage);
        }
    }
}

function countMatchingPersonalityLetters($type1, $type2)
{
    // Count matching characters in personality types
    return count(array_intersect_assoc(str_split($type1), str_split($type2)));
}

function renderUserProfile($name, $gender, $age, $type, $os, $image)
{
    // Generate the HTML for displaying user profile
    ob_start();  // Start output buffering
    ?>
    <p>
        <img src='<?= htmlspecialchars($image) ?>' alt='user icon' class='user-icon'>
        <?= htmlspecialchars($name) ?>
    </p>
    <ul>
        <li><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></li>
        <li><strong>Age:</strong> <?= htmlspecialchars($age) ?></li>
        <li><strong>Type:</strong> <?= htmlspecialchars($type) ?></li>
        <li><strong>OS:</strong> <?= htmlspecialchars($os) ?></li>
    </ul>
    <?php
    return ob_get_clean();  // Return the buffered content
}
?>

