<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$info = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id'])) {
    $user_id = trim($_POST['user_id']);
    $now = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO transactions (book_id, user_id, date_out) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $book_id, $user_id, $now);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->close();

    header("Location: lista_ksiazek.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wypożycz książkę</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 style="text-align:center;">Wypożycz książkę (ID: <?= $book_id ?>)</h2>

<form method="post">
    <label for="user_id">Wpisz identyfikator użytkownika:</label>
    <input type="text" name="user_id" id="user_id" required>

    <input type="submit" value="Wypożycz">
</form>

<div class="back-link">
    <a href="lista_ksiazek.php">← Powrót do listy książek</a>

</div>

</body>
</html>
