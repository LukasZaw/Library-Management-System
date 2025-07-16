<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Brak ID książki.");
}

$book_id = (int)$_GET['id'];

$sql = "SELECT id, status FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Nie znaleziono książki.");
}

$book = $result->fetch_assoc();
$stmt->close();

if ($book['status'] !== 'borrowed') {
    die("Książka nie jest wypożyczona.");
}

$updateTransaction = $conn->prepare("
    UPDATE transactions 
    SET date_in = NOW() 
    WHERE book_id = ? AND date_in IS NULL
");
$updateTransaction->bind_param("i", $book_id);
$updateTransaction->execute();
$updateTransaction->close();

$updateBook = $conn->prepare("UPDATE books SET status = 'available' WHERE id = ?");
$updateBook->bind_param("i", $book_id);
$updateBook->execute();
$updateBook->close();

$conn->close();

header("Location: lista_ksiazek.php");
exit();

?>
