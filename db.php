<?php
$host = 'localhost';      
$user = 'root';           
$password = '';          
$database = 'library'; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}
echo "Połączono z bazą danych!<br>";

$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Tabele '$database':<br>";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "Brak tabel w bazie danych.";
}

$conn->close();
?>
