<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("
    SELECT 
        t.id,
        t.user_id,
        b.title AS book_title,
        t.date_out,
        t.date_in
    FROM transactions t
    INNER JOIN books b ON t.book_id = b.id
    ORDER BY t.date_out DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$countResult = $conn->query("SELECT COUNT(*) AS total FROM transactions");
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Historia transakcji</title>
    <link rel="stylesheet" href="style.css">
    <style>

        .returned {
            color: green;
            font-weight: bold;
        }

        .not-returned {
            color: red;
            font-weight: bold;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 14px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a.disabled {
            background-color: #ccc;
            pointer-events: none;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h1>Historia transakcji</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>#</th>
            <th>Użytkownik</th>
            <th>Tytuł książki</th>
            <th>Data wypożyczenia</th>
            <th>Data zwrotu</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['book_title']) ?></td>
                <td><?= $row['date_out'] ?></td>
                <td><?= $row['date_in'] ?? '-' ?></td>
                <td class="<?= $row['date_in'] ? 'returned' : 'not-returned' ?>">
                    <?= $row['date_in'] ? 'Zwrócona' : 'Wypożyczona' ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">← Poprzednia</a>
        <?php else: ?>
            <a class="disabled">← Poprzednia</a>
        <?php endif; ?>

        <span>Strona <?= $page ?> z <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Następna →</a>
        <?php else: ?>
            <a class="disabled">Następna →</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p style="text-align: center;">Brak transakcji do wyświetlenia.</p>
<?php endif; ?>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
