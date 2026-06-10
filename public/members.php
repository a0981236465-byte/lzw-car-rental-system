<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/header.php';

$members = db()->query("SELECT * FROM members ORDER BY FIELD(name, '陳昱丞', '宋柏穎', '李諺儒', '侯冠丞', '張恩睿'), member_id ASC")->fetchAll();
?>
<h2>Project Members</h2>
<?php if (!$members): ?>
    <p>No member records found.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($members as $member): ?>
            <article class="card">
                <h3><?= e($member['name']) ?></h3>
                <p><strong>Student ID:</strong> <?= e($member['student_id']) ?></p>
                <p><strong>Role:</strong> <?= e($member['role']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
