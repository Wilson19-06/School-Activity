<?php
include 'auth_check.php';
include 'config.php';

$group_id = intval($_GET['group_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT group_name FROM groups WHERE id = ?');
$stmt->bind_param('i', $group_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();
if (!$group) {
    echo "<div class='alert alert-danger'>Group not found.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    $file_path = null;

    if (!empty($_FILES['file']['name'])) {
        $target_dir = 'uploads/messages/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['file']['name']);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $target_file);
        $file_path = $target_file;
    }

    $stmt = $conn->prepare('INSERT INTO group_messages (group_id, user_id, content, file_path) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiss', $group_id, $user_id, $msg, $file_path);
    $stmt->execute();

    header('Location: group_chat.php?group_id=' . $group_id);
    exit;
}

$stmt = $conn->prepare('SELECT m.*, u.username
                        FROM group_messages m
                        JOIN users u ON u.id = m.user_id
                        WHERE group_id = ?
                        ORDER BY m.created_at ASC');
$stmt->bind_param('i', $group_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<style>
.chat-container {
  max-height: 450px;
  overflow-y: auto;
  background: #f4fafb;
  padding: 1rem;
  border-radius: 18px;
  border: 1px solid #d7eef1;
}
.chat-msg {
  max-width: 75%;
  padding: 0.6rem 1rem;
  border-radius: 13px;
  word-break: break-word;
  position: relative;
}
.chat-right {
  background: linear-gradient(135deg, #087e8b 0%, #0b3954 100%);
  color: white;
  border-bottom-right-radius: 0;
  margin-left: auto;
}
.chat-left {
  background-color: #ffffff;
  color: #000;
  border-bottom-left-radius: 0;
  margin-right: auto;
}
.chat-time {
  font-size: 0.75rem;
  color: #aaa;
  margin-top: 4px;
  text-align: right;
}
.chat-read {
  font-size: 0.75rem;
  color: #b9f5cb;
  margin-left: 6px;
}
.chat-username {
  font-weight: bold;
  font-size: 0.9rem;
  margin-bottom: 2px;
  color: #555;
}
.chat-preview a {
  text-decoration: none;
  color: #bfdbf7;
}
.chat-left .chat-preview a {
  color: #087e8b;
}
.chat-preview a:hover {
  text-decoration: underline;
}
.send-area textarea {
  resize: none;
}
.file-icon-btn {
  display: inline-block;
  width: 42px;
  height: 42px;
  border-radius: 13px;
  background: linear-gradient(135deg, #087e8b 0%, #0b3954 100%);
  color: white;
  text-align: center;
  line-height: 42px;
  font-size: 20px;
  cursor: pointer;
}
</style>

<h3 class="mb-3">Group Chat: <?= htmlspecialchars($group['group_name']) ?></h3>

<div class="chat-container mb-4">
  <?php while ($m = $messages->fetch_assoc()):
    $isMine = (int)$m['user_id'] === (int)$_SESSION['user_id'];
  ?>
    <div class="mb-3 d-flex flex-column <?= $isMine ? 'align-items-end' : 'align-items-start' ?>">
      <div class="chat-username"><?= $isMine ? 'Me' : htmlspecialchars($m['username']) ?></div>
      <div class="chat-msg <?= $isMine ? 'chat-right' : 'chat-left' ?>">
        <?= nl2br(htmlspecialchars($m['content'])) ?>
        <?php if (!empty($m['file_path'])): ?>
          <div class="chat-preview mt-2">
            <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank">View Attachment</a>
          </div>
        <?php endif; ?>
        <div class="chat-time">
          <?= date('H:i', strtotime($m['created_at'])) ?>
          <?= $isMine ? '<span class="chat-read">Seen</span>' : '' ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<form method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end send-area">
  <textarea name="message" class="form-control" rows="2" placeholder="Type a message..." required></textarea>
  <label for="fileInput" class="file-icon-btn" title="Upload file">+</label>
  <input type="file" name="file" id="fileInput" class="d-none">
  <button class="btn btn-primary">Send</button>
</form>

<a href="groups.php" class="btn btn-secondary mt-4">Back to Groups</a>

<?php include 'includes/footer.php'; ?>