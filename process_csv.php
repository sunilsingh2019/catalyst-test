<?php
// process_csv.php
$mysqli = new mysqli("database", "myuser", "mypassword", "mydb");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (($handle = fopen($argv[1], "r")) !== FALSE) {
    $stmt = $mysqli->prepare("INSERT INTO users (name, surname, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $surname, $email);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $name = ucfirst(strtolower($data[0])); // Capitalize first letter, make lowercase
        $surname = ucfirst(strtolower($data[1])); // Capitalize first letter, make lowercase
        $email = strtolower($data[2]); // Make lowercase

        // Check if the email is valid using regular expression
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/', $email)) {
            echo "Invalid email: $email\n";
            continue; // Skip the current iteration
        }

        // Check for duplicate emails before inserting
        $check_duplicate = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $check_duplicate->bind_param("s", $email);
        $check_duplicate->execute();
        $check_duplicate->store_result();

        if ($check_duplicate->num_rows > 0) {
            echo "Error inserting record: Duplicate entry for email '$email'\n";
            $check_duplicate->close();
            continue; // Skip the current iteration
        }

        $check_duplicate->close();

        // Insert the record
        if (!$stmt->execute()) {
            echo "Error inserting record: " . $stmt->error . "\n";
        }
    }
    fclose($handle);
    $stmt->close();
} else {
    echo "Error opening file\n";
}

$mysqli->close();
?>
