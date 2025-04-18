<?php
// Include your DB connection here
require_once "../includes/connection.php";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$start = ($page - 1) * $perPage;

// Fetch total records
$totalResult = $conn->query("SELECT COUNT(*) as total FROM features");
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// Fetch paginated records
$features = $conn->query("SELECT * FROM features LIMIT $start, $perPage");
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Preview</th>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Type</th>
            <th>Toggle</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $features->fetch_assoc()): ?>
            <tr>
                <td><img src="../../assets/images/<?= $row['image'] ?>" width="100"></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['is_active'] ? "✅ Active" : "❌ Inactive" ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td>
                <button class="btn btn-sm btn-warning toggle-feature" data-id="<?= $row['id'] ?>">Toggle</button>
                <button class="btn btn-sm btn-danger delete-feature" data-id="<?= $row['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<nav>
    <ul class="pagination justify-content-center mt-3">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link feature-pagination" href="#" data-page="<?= $page - 1 ?>">Previous</a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link feature-pagination" href="#" data-page="<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link feature-pagination" href="#" data-page="<?= $page + 1 ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
