<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_admin();

$allowedRoles = ['user', 'admin'];
$currentUser = current_user();
$currentUserId = (int)$currentUser['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $password = $_POST['password'] ?? '';

            if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !valid_status($role, $allowedRoles)) {
                set_flash('Invalid user data.', 'error');
                redirect_to('admin_users.php');
            }

            if ($action === 'create' && strlen($password) < 6) {
                set_flash('Password is required and must be at least 6 characters for new users.', 'error');
                redirect_to('admin_users.php');
            }

            if ($action === 'update' && $userId === $currentUserId && $role !== 'admin') {
                set_flash('You cannot demote your own admin account.', 'error');
                redirect_to('admin_users.php');
            }

            if ($action === 'create') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = db()->prepare('INSERT INTO users (name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $phone, $email, $hash, $role]);
                set_flash('User account created successfully.');
            } else {
                if ($password !== '') {
                    if (strlen($password) < 6) {
                        set_flash('New password must be at least 6 characters.', 'error');
                        redirect_to('admin_users.php?edit=' . $userId);
                    }
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = db()->prepare('UPDATE users SET name = ?, phone = ?, email = ?, password_hash = ?, role = ? WHERE user_id = ?');
                    $stmt->execute([$name, $phone, $email, $hash, $role, $userId]);
                } else {
                    $stmt = db()->prepare('UPDATE users SET name = ?, phone = ?, email = ?, role = ? WHERE user_id = ?');
                    $stmt->execute([$name, $phone, $email, $role, $userId]);
                }

                if ($userId === $currentUserId) {
                    login_user([
                        'user_id' => $currentUserId,
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                    ]);
                }

                set_flash('User account updated successfully.');
            }
        } elseif ($action === 'delete' && $userId > 0) {
            if ($userId === $currentUserId) {
                set_flash('You cannot delete your own admin account.', 'error');
                redirect_to('admin_users.php');
            }
            $stmt = db()->prepare('DELETE FROM users WHERE user_id = ?');
            $stmt->execute([$userId]);
            set_flash('User account deleted successfully.');
        }
    } catch (Throwable $e) {
        set_flash('Operation failed. The email may already exist, or the user may have rental records.', 'error');
    }

    redirect_to('admin_users.php');
}

require_once __DIR__ . '/../src/header.php';
$editId = (int)($_GET['edit'] ?? 0);
$editUser = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
}
$users = db()->query('SELECT user_id, name, phone, email, role, created_at FROM users ORDER BY user_id DESC')->fetchAll();
?>
<h2>Manage Users</h2>
<p>Admin can create accounts, edit account information, and promote or demote users.</p>

<h3><?= $editUser ? 'Edit User' : 'Add User' ?></h3>
<form method="post" action="admin_users.php">
    <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
    <?php if ($editUser): ?>
        <input type="hidden" name="user_id" value="<?= e($editUser['user_id']) ?>">
    <?php endif; ?>
    <div class="form-grid">
        <div>
            <label for="name">Name</label>
            <input id="name" name="name" required value="<?= e($editUser['name'] ?? '') ?>">
        </div>
        <div>
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="<?= e($editUser['phone'] ?? '') ?>">
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?= e($editUser['email'] ?? '') ?>">
        </div>
        <div>
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <?php foreach ($allowedRoles as $role): ?>
                    <option value="<?= e($role) ?>" <?= selected($editUser['role'] ?? 'user', $role) ?>><?= e($role) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="password">Password <?= $editUser ? '(leave blank to keep current password)' : '' ?></label>
            <input id="password" name="password" type="password" <?= $editUser ? '' : 'required' ?>>
        </div>
    </div>
    <button type="submit"><?= $editUser ? 'Update User' : 'Add User' ?></button>
    <?php if ($editUser): ?>
        <a class="button secondary" href="admin_users.php">Cancel</a>
    <?php endif; ?>
</form>

<h3>User Accounts</h3>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td data-label="ID"><?= e($user['user_id']) ?></td>
            <td data-label="Name"><?= e($user['name']) ?></td>
            <td data-label="Phone"><?= e($user['phone']) ?></td>
            <td data-label="Email"><?= e($user['email']) ?></td>
            <td data-label="Role"><span class="badge <?= e($user['role']) ?>"><?= e($user['role']) ?></span></td>
            <td data-label="Created"><?= e($user['created_at']) ?></td>
            <td data-label="Actions">
                <div class="actions">
                    <a class="button" href="admin_users.php?edit=<?= e($user['user_id']) ?>">Edit</a>
                    <?php if ((int)$user['user_id'] !== $currentUserId): ?>
                        <form method="post" action="admin_users.php">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= e($user['user_id']) ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    <?php else: ?>
                        Current admin
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
