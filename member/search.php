<?php
include_once(realpath(dirname(__FILE__, 2) . '/php/session.php'));
include_once(realpath(dirname(__FILE__, 2) . '/php/path.php'));
include_once(ROOT_PATH . '/php/config.php');
$page_name = "member";

$q = trim($_GET['q'] ?? '');
$books = [];

if ($q !== '') {
    $safe = $db_connection->real_escape_string($q);
    $sql = "SELECT b.book_id, b.title, b.isbn,
                   (SELECT COUNT(*) FROM Copies c WHERE c.book_id = b.book_id AND c.status = 'in') AS available
            FROM Books b
            LEFT JOIN Book_Authors ba ON b.book_id = ba.book_id
            LEFT JOIN Authors a ON ba.author_id = a.author_id
            WHERE b.title LIKE '%$safe%' OR a.name LIKE '%$safe%' OR b.isbn LIKE '%$safe%'
            GROUP BY b.book_id
            ORDER BY b.title";
    $result = $db_connection->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head><?php include_once(ROOT_PATH . '/include/head.php'); ?></head>
<body class="<?php echo $page_name; ?>">
<?php include_once(ROOT_PATH . '/include/header.php'); ?>
<main class="container">
  <h1>Search books</h1>
  <form method="get" class="mb-4">
    <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Title, author, or ISBN" class="form-control" style="max-width:400px; display:inline-block;">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <?php if ($q !== ''): ?>
    <table class="table">
      <tr><th>Title</th><th>ISBN</th><th>Available</th><th></th></tr>
      <?php foreach ($books as $b): ?>
        <tr>
          <td><?php echo htmlspecialchars($b['title']); ?></td>
          <td><?php echo htmlspecialchars($b['isbn']); ?></td>
          <td><?php echo (int)$b['available']; ?></td>
          <td>
            <?php if ($b['available'] > 0): ?>
              <a class="btn btn-sm btn-success" href="checkout.php?book_id=<?php echo (int)$b['book_id']; ?>">Check out</a>
            <?php else: ?>
              <a class="btn btn-sm btn-warning" href="hold.php?book_id=<?php echo (int)$b['book_id']; ?>">Place hold</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (count($books) === 0): ?>
        <tr><td colspan="4">No matches</td></tr>
      <?php endif; ?>
    </table>
  <?php endif; ?>
</main>
<?php include_once(ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>