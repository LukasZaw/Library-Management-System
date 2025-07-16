<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$where = [];
$params = [];
$types = '';

if (!empty($_GET['author_id'])) {
    $where[] = 'books.author_id = ?';
    $params[] = $_GET['author_id'];
    $types .= 'i';
}
if (!empty($_GET['genre_id'])) {
    $where[] = 'books.genre_id = ?';
    $params[] = $_GET['genre_id'];
    $types .= 'i';
}
if (!empty($_GET['status'])) {
    $where[] = 'books.status = ?';
    $params[] = $_GET['status'];
    $types .= 's';
}

if (!empty($_GET['year_min'])) {
    $where[] = 'books.year >= ?';
    $params[] = $_GET['year_min'];
    $types .= 'i';
}
if (!empty($_GET['year_max'])) {
    $where[] = 'books.year <= ?';
    $params[] = $_GET['year_max'];
    $types .= 'i';
}

$sql = "
    SELECT 
        books.id,
        books.title,
        books.year,
        books.status,
        authors.name AS author,
        genres.name AS genre
    FROM books
    LEFT JOIN authors ON books.author_id = authors.id
    LEFT JOIN genres ON books.genre_id = genres.id
";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY books.title ASC';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Lista książek</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>

</head>
<body>

<h1>Lista książek</h1>

<?php
$authors = $conn->query("SELECT DISTINCT authors.id, authors.name FROM authors INNER JOIN books ON books.author_id = authors.id");
$genres = $conn->query("SELECT DISTINCT genres.id, genres.name FROM genres INNER JOIN books ON books.genre_id = genres.id");
$yearsRange = $conn->query("SELECT MIN(year) AS min_year, MAX(year) AS max_year FROM books")->fetch_assoc();
$minYear = $yearsRange['min_year'] ?? 1900;
$maxYear = $yearsRange['max_year'] ?? date("Y");
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $maxYear;
?>
<form method="GET" style="text-align: center; margin-bottom: 16px;" class="filter-form">
    <label>Autor:
        <select name="author_id">
            <option value="">-- Wszyscy --</option>
            <?php while ($row = $authors->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= isset($_GET['author_id']) && $_GET['author_id'] == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </label>

    <label>Gatunek:
        <select name="genre_id">
            <option value="">-- Wszystkie --</option>
            <?php while ($row = $genres->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= isset($_GET['genre_id']) && $_GET['genre_id'] == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </label>

    <label>Status:
        <select name="status">
            <option value="">-- Wszystkie --</option>
            <option value="available" <?= isset($_GET['status']) && $_GET['status'] == 'available' ? 'selected' : '' ?>>Dostępna</option>
            <option value="borrowed" <?= isset($_GET['status']) && $_GET['status'] == 'borrowed' ? 'selected' : '' ?>>Wypożyczona</option>
        </select>
    </label>

    <label style="min-width: 240px;">
        Rok wydania: <span id="yearRangeOutput"><?= $minYear ?> - <?= $maxYear ?></span>
        <div id="yearRangeSlider" style="margin-top: 10px;"></div>
        <input type="hidden" name="year_min" id="yearMin" value="<?= isset($_GET['year_min']) ? $_GET['year_min'] : $minYear ?>">
        <input type="hidden" name="year_max" id="yearMax" value="<?= isset($_GET['year_max']) ? $_GET['year_max'] : $maxYear ?>">
    </label>


    <input type="submit" value="Filtruj">
    <a href="lista_ksiazek.php" style="margin-left: 10px;">Resetuj</a>
</form>
<table>
    <tr>
        <th>Tytuł</th>
        <th>Autor</th>
        <th>Gatunek</th>
        <th>Rok wydania</th>
        <th>Status</th>
        <th>Akcja</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['author'] ? htmlspecialchars($row['author']) : "<i>-brak autora-</i>"  ?></td>
                <td><?= htmlspecialchars($row['genre']) ?></td>
                <td><?= htmlspecialchars($row['year']) ?></td>
                <td class="<?= $row['status'] ?>">
                    <?= $row['status'] === 'available' ? "Dostępna" : "Wypozyczona"  ?>
                </td>
                <td>
                    <?php if ($row['status'] === 'available'): ?>
                        <a class="btn btn-borrow" href="borrow.php?id=<?= $row['id'] ?>">Wypożycz</a>
                    <?php else: ?>
                        <a class="btn btn-return" href="zwroc.php?id=<?= $row['id'] ?>">Zwróć</a>
                    <?php endif; ?>
                        <a href="?delete=<?= $row['id'] ?>" class="btn delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć tę książkę?')">Usuń</a>
                    </td>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">Brak książek w bazie danych.</td>
        </tr>
    <?php endif; ?>
</table>

<div class="back-link">
    <a href="index.html">← Powrót do strony głównej</a>
</div>

<?php $conn->close(); ?>

</body>

<script>
    const yearSlider = document.getElementById('yearRangeSlider');
    const minYear = <?= $minYear ?>;
    const maxYear = <?= $maxYear ?>;

    noUiSlider.create(yearSlider, {
        start: [
            <?= isset($_GET['year_min']) ? $_GET['year_min'] : $minYear ?>,
            <?= isset($_GET['year_max']) ? $_GET['year_max'] : $maxYear ?>
        ],
        connect: true,
        step: 1,
        range: {
            'min': minYear,
            'max': maxYear
        },
        format: {
            to: value => Math.round(value),
            from: value => parseInt(value)
        }
    });

    const output = document.getElementById('yearRangeOutput');
    const inputMin = document.getElementById('yearMin');
    const inputMax = document.getElementById('yearMax');

    yearSlider.noUiSlider.on('update', function (values) {
        output.textContent = values[0] + ' - ' + values[1];
        inputMin.value = values[0];
        inputMax.value = values[1];
    });
</script>
</html>

