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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['genre_name']) && !isset($_POST['edit_id'])) {
    $genre_name = trim($_POST['genre_name']);
    if (!empty($genre_name)) {
        $stmt = $conn->prepare("INSERT INTO genres (name) VALUES (?)");
        $stmt->bind_param("s", $genre_name);
        if ($stmt->execute()) {
            $info = "Gatunek dodany pomyślnie!";
        } else {
            $info = "Błąd podczas dodawania gatunku.";
        }
        $stmt->close();
    } else {
        $info = "Nazwa gatunku nie może być pusta.";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM genres WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $info = "Gatunek został usunięty.";
    } else {
        $info = "Błąd podczas usuwania gatunku.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $new_name = trim($_POST['genre_name']);
    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE genres SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $id);
        if ($stmt->execute()) {
            $info = "Gatunek został zaktualizowany.";
        } else {
            $info = "Błąd podczas aktualizacji gatunku.";
        }
        $stmt->close();
    } else {
        $info = "Nazwa gatunku nie może być pusta.";
    }
}

$edit_mode = false;
$edit_id = null;
$edit_name = '';

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT name FROM genres WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_name);
    if ($stmt->fetch()) {
        $edit_mode = true;
    }
    $stmt->close();
}

$sql = "SELECT id, name FROM genres ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Gatunki - Biblioteka</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .info {
            text-align: center;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h1>Lista gatunków</h1>

<?php if (!empty($info)): ?>
    <div class="info"><?= htmlspecialchars($info) ?></div>
<?php endif; ?>

<form method="post" action="genre.php">
    <input type="text" name="genre_name" value="<?= htmlspecialchars($edit_mode ? $edit_name : '') ?>" placeholder="Nazwa gatunku" required>
    <?php if ($edit_mode): ?>
        <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <input type="submit" value="Zapisz zmiany">
        <a href="genre.php">Anuluj</a>
    <?php else: ?>
        <input type="submit" value="Dodaj gatunek">
    <?php endif; ?>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Gatunek</th>
        <th>Akcje</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td class="actions">
                    <a href="genre.php?edit=<?= $row['id'] ?>" class="edit-btn">Edytuj</a>
                    <a href="genre.php?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć ten gatunek?')">Usuń</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="3">Brak gatunków w bazie danych.</td></tr>
    <?php endif; ?>
</table>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php $conn->close(); ?>

</body>
</html>
