<?php
// Goes further than db-test.php: actually creates a table, writes a row,
// reads it back, then cleans up. Confirms the DB user has real permissions,
// not just a connection.

$host = "mysql";
$dbname = getenv("MYSQL_DATABASE");
$username = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");

$steps = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $steps[] = ["Connect to MySQL", true, ""];

    // CREATE TABLE
    $pdo->exec("CREATE TABLE IF NOT EXISTS _sandbox_test (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $steps[] = ["Create test table", true, ""];

    // INSERT
    $stmt = $pdo->prepare("INSERT INTO _sandbox_test (message) VALUES (?)");
    $stmt->execute(["Hello from PHP at " . date("H:i:s")]);
    $insertedId = $pdo->lastInsertId();
    $steps[] = ["Insert a row", true, "Inserted row ID: $insertedId"];

    // SELECT
    $stmt = $pdo->prepare("SELECT * FROM _sandbox_test WHERE id = ?");
    $stmt->execute([$insertedId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $steps[] = ["Read the row back", (bool)$row, $row ? htmlspecialchars($row["message"]) : "No row found"];

    // DELETE (cleanup)
    $stmt = $pdo->prepare("DELETE FROM _sandbox_test WHERE id = ?");
    $stmt->execute([$insertedId]);
    $steps[] = ["Delete the row (cleanup)", true, ""];

} catch (PDOException $e) {
    $steps[] = ["Error", false, htmlspecialchars($e->getMessage())];
}
?>
<!DOCTYPE html>
<html>
<head><title>MySQL CRUD Test</title></head>
<body>
    <h1>MySQL Read/Write Test</h1>
    <table border="1" cellpadding="8">
        <tr><th>Step</th><th>Result</th><th>Details</th></tr>
        <?php foreach ($steps as [$label, $ok, $detail]): ?>
            <tr>
                <td><?= htmlspecialchars($label) ?></td>
                <td><?= $ok ? "✅" : "❌" ?></td>
                <td><?= $detail ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (end($steps)[1] ?? true): ?>
        <p style="color:green;">Full read/write cycle completed successfully — your database user has proper permissions.</p>
    <?php else: ?>
        <p style="color:red;">Something failed above — check the error detail.</p>
    <?php endif; ?>
</body>
</html>