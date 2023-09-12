#!/usr/bin/php
<?php
// Ensure that the script is run from the command line
if (php_sapi_name() !== 'cli') {
    exit("This script can only be run from the command line.\n");
}

// Check if a CSV file argument is provided
if ($argc < 2) {
    exit("Usage: php user_upload.php users.csv\n");
}

$inputFile = $argv[1];

// Check if the input file exists
if (!file_exists($inputFile)) {
    exit("Input file not found.\n");
}

// Create a PDO connection to the MySQL database
try {
    $db = new PDO('mysql:host=database;dbname=mydb', 'myuser', 'mypassword');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Database connection failed: " . $e->getMessage() . "\n");
}

// Create the users table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    surname VARCHAR(255),
    email VARCHAR(255) UNIQUE
)");

// Read and process the CSV file
$handle = fopen($inputFile, 'r');
if ($handle === false) {
    exit("Error opening input file.\n");
}

$invalidEmails = [];
$insertedRecords = 0;

while (($data = fgetcsv($handle)) !== false) {
    $name = ucfirst(strtolower(trim($data[0])));
    $surname = ucfirst(strtolower(trim($data[1])));
    $email = strtolower(trim($data[2]));

    // Validate the email format using filter_var
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $invalidEmails[] = $email;
        continue; // Skip this record
    }

    // Insert the record into the database
    try {
        $stmt = $db->prepare("INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $insertedRecords++;
    } catch (PDOException $e) {
        // Handle duplicate entry error or other database errors here
        // You can log the error or display a message as needed
    }
}

fclose($handle);

// Report invalid email addresses and the number of records inserted
if (!empty($invalidEmails)) {
    echo "Invalid email addresses:\n";
    foreach ($invalidEmails as $invalidEmail) {
        echo "Invalid email: $invalidEmail\n";
    }
}

echo "Inserted $insertedRecords records into the database.\n";
?>
