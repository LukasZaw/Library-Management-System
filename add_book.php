<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$info = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $author_id = (int)$_POST['author_id'];
    $genre_id = (int)$_POST['genre_id'];
    $year = (int)$_POST['year'];

    if (!empty($title) && $author_id > 0 && $genre_id > 0 && $year > 0) {
        $stmt = $conn->prepare("INSERT INTO books (title, author_id, genre_id, year, status) VALUES (?, ?, ?, ?, 'available')");
        $stmt->bind_param("siii", $title, $author_id, $genre_id, $year);
        if ($stmt->execute()) {
            $info = "Książka dodana pomyślnie!";
        } else {
            $info = "Błąd podczas dodawania książki.";
        }
        $stmt->close();
    } else {
        $info = "Wszystkie pola muszą być poprawnie wypełnione.";
    }
}

$authors = $conn->query("SELECT id, name FROM authors ORDER BY name ASC");
$genres = $conn->query("SELECT id, name FROM genres ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj książkę</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Dodaj nową książkę</h1>

<?php if (!empty($info)): ?>
    <div class="info"><?= htmlspecialchars($info) ?></div>
<?php endif; ?>

<form method="post" action="add_book.php">
    <label for="title">Tytuł książki:</label>
    <input type="text" id="title" name="title" required>

    <label for="author_id">Autor:</label>
    <select id="author_id" name="author_id" required>
        <option value="">-- Wybierz autora --</option>
        <?php while ($row = $authors->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="genre_id">Gatunek:</label>
    <select id="genre_id" name="genre_id" required>
        <option value="">-- Wybierz gatunek --</option>
        <?php while ($row = $genres->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="year">Rok wydania:</label>
    <input type="number" id="year" name="year" min="1000" max="2100" required>

    <input type="submit" value="Dodaj książkę">
</form>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php $conn->close(); ?>

</body>
</html>
