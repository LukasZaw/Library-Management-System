<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$info = '';
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_id'])) {
    $user_id = trim($_POST['user_id']);

    $stmt = $conn->prepare("
        SELECT 
            books.title,
            t.date_out,
            DATEDIFF(DATE_ADD(t.date_out, INTERVAL 30 DAY), CURDATE()) AS days_left
        FROM transactions t
        INNER JOIN books ON t.book_id = books.id
        WHERE t.user_id = ? AND t.date_in IS NULL
        ORDER BY t.date_out DESC
    ");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $info = "Brak aktywnych wypożyczeń dla użytkownika <b>" . htmlspecialchars($user_id) . "</b>.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wypożyczenia użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Sprawdź wypożyczenia użytkownika</h1>

<form method="post">
    <input type="text" name="user_id" placeholder="Wprowadź ID lub nazwę użytkownika" required>
    <input type="submit" value="Sprawdź">
</form>

<?php if (!empty($info)): ?>
    <div class="info"><?= $info ?></div>
<?php endif; ?>

<?php if (!empty($data)): ?>
    <table>
        <tr>
            <th>Tytuł książki</th>
            <th>Data wypożyczenia</th>
            <th>Termin zwrotu</th>
            <th>Status</th>
        </tr>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['date_out'] ?></td>
                <td><?= date('Y-m-d', strtotime($row['date_out'] . ' +30 days')) ?></td>
                <td class="<?= $row['days_left'] < 0 ? 'late' : 'ok' ?>">
                    <?php if ($row['days_left'] < 0): ?>
                        Po terminie o <?= abs($row['days_left']) ?> dni
                    <?php else: ?>
                        <?= $row['days_left'] ?> dni do zwrotu
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php $conn->close(); ?>
</body>
</html>
