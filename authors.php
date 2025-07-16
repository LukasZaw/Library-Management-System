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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['author_name']) && !isset($_POST['edit_id'])) {
    $author_name = trim($_POST['author_name']);
    if (!empty($author_name)) {
        $stmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $author_name);
        if ($stmt->execute()) {
            $info = "Autor dodany pomyślnie!";
        } else {
            $info = "Błąd podczas dodawania autora.";
        }
        $stmt->close();
    } else {
        $info = "Nazwa autora nie może być pusta.";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM authors WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $info = "Autor został usunięty.";
    } else {
        $info = "Błąd podczas usuwania autora.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $new_name = trim($_POST['author_name']);
    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE authors SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $id);
        if ($stmt->execute()) {
            $info = "Autor został zaktualizowany.";
        } else {
            $info = "Błąd podczas aktualizacji autora.";
        }
        $stmt->close();
    } else {
        $info = "Nazwa autora nie może być pusta.";
    }
}

$edit_mode = false;
$edit_id = null;
$edit_name = '';

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT name FROM authors WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_name);
    if ($stmt->fetch()) {
        $edit_mode = true;
    }
    $stmt->close();
}

$sql = "SELECT id, name FROM authors ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Autorzy - Biblioteka</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 60%;
        }
        
    </style>
</head>
<body>

<h1 style="text-align:center;">Lista autorów</h1>

<?php if (!empty($info)): ?>
    <div class="info"><?= htmlspecialchars($info) ?></div>
<?php endif; ?>

<form method="post" action="authors.php">
    <input type="text" name="author_name" value="<?= htmlspecialchars($edit_mode ? $edit_name : '') ?>" placeholder="Imię i nazwisko autora" required>
    <?php if ($edit_mode): ?>
        <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <input type="submit" value="Zapisz zmiany">
        <a href="authors.php">Anuluj</a>
    <?php else: ?>
        <input type="submit" value="Dodaj autora">
    <?php endif; ?>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Autor</th>
        <th>Akcje</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name'] ) ?></td>
                <td class="actions">
                    <a href="authors.php?edit=<?= $row['id'] ?>" class="edit-btn">Edytuj</a>
                    <a href="authors.php?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć tego autora?')">Usuń</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="3">Brak autorów w bazie danych.</td></tr>
    <?php endif; ?>
</table>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php $conn->close(); ?>

</body>
</html>
