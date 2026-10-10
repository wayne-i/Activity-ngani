<?php
require_once __DIR__ . '/bago.php';

function escape(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editingUser = null;

if ($editId && $editId > 0) {
	$statement = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
	$statement->bind_param('i', $editId);
	$statement->execute();
	$editingUser = $statement->get_result()->fetch_assoc();
	$statement->close();

	if (!$editingUser) {
		header('Location: index.php');
		exit;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');

	if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = 'Enter a name and a valid email address.';
	} else {
		if (!$editId || $editId < 1) {
			$duplicateCheck = $conn->prepare('SELECT id FROM users WHERE name = ? AND email = ? LIMIT 1');
			$duplicateCheck->bind_param('ss', $name, $email);
			$duplicateCheck->execute();
			$duplicate = $duplicateCheck->get_result()->fetch_assoc();
			$duplicateCheck->close();

			if ($duplicate) {
				$error = 'This user is already registered.';
			}
		}

		if ($error === '') {
			if ($editId && $editId > 0) {
				$statement = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
				$statement->bind_param('ssi', $name, $email, $editId);
				$successMessage = 'updated';
			} else {
				$statement = $conn->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
				$statement->bind_param('ss', $name, $email);
				$successMessage = 'added';
			}
			$statement->execute();
			$statement->close();

			header('Location: index.php?msg=' . $successMessage);
			exit;
		}
	}
}

$messages = [
	'added' => 'User added successfully.',
	'updated' => 'User updated successfully.',
	'deleted' => 'User deleted successfully.',
];
$message = $messages[$_GET['msg'] ?? ''] ?? '';
$users = $conn->query('SELECT * FROM users ORDER BY id DESC');
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>User Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
	<div class="row justify-content-center">
			<div class="col-12 col-xxl-10">
				<h1 class="h2 mb-4 text-center">User Management System</h1>

			<?php if ($message !== ''): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<?= escape($message) ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php endif; ?>
			<?php if ($error !== ''): ?>
				<div class="alert alert-danger" role="alert"><?= escape($error) ?></div>
			<?php endif; ?>

			<div class="row g-4 align-items-start">
			<section class="col-lg-4">
				<div class="bg-white border rounded p-4">
				<h2 class="h5 mb-3"><?= $editId ? 'Edit User' : 'Add New User' ?></h2>
				<form method="post" action="index.php<?= $editId ? '?edit=' . (int) $editId : '' ?>" class="row g-3">
					<div class="col-12">
						<label for="name" class="form-label">Full Name</label>
						<input type="text" class="form-control" id="name" name="name" maxlength="255" placeholder="Enter Full Name" required value="<?= escape($_POST['name'] ?? $editingUser['name'] ?? '') ?>">
					</div>
					<div class="col-12">
						<label for="email" class="form-label">Email Address</label>
						<input type="email" class="form-control" id="email" name="email" maxlength="255" placeholder="Enter Email Address" required value="<?= escape($_POST['email'] ?? $editingUser['email'] ?? '') ?>">
					</div>
					<div class="col-12">
						<button type="submit" class="btn btn-primary w-100"><?= $editId ? 'Update User' : 'Submit' ?></button>
						<?php if ($editId): ?>
							<a href="index.php" class="btn btn-outline-secondary">Cancel</a>
						<?php endif; ?>
					</div>
				</form>
				</div>
			</section>

			<section class="col-lg-8">
				<h2 class="h5 mb-3">Registered Users</h2>
				<div class="table-responsive bg-white border rounded">
					<table class="table table-striped table-hover align-middle mb-0">
						<thead class="table-light text-dark">
							<tr><th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Joined</th>
                            <th scope="col">Actions</th></tr>
						</thead>
						<tbody>
						<?php if ($users->num_rows === 0): ?>
							<tr><td colspan="5" class="text-center py-4">No users found.</td></tr>
						<?php else: ?>
							<?php while ($user = $users->fetch_assoc()): ?>
								<tr>
									<td><?= (int) $user['id'] ?></td>
									<td><?= escape($user['name']) ?></td>
									<td><?= escape($user['email']) ?></td>
									<td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
									<td class="text-nowrap">
										<a class="btn btn-sm btn-success" href="index.php?edit=<?= (int) $user['id'] ?>">Edit</a>
										<a class="btn btn-sm btn-danger" 
                                        href="delete.php?id=<?= (int) $user['id'] ?>" 
                                        onclick="return confirm('Delete this user?');">Delete</a>
									</td>
								</tr>
							<?php endwhile; ?>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
			</div>
		</div>
	</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
