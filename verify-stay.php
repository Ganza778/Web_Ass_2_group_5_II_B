<?php
session_start();
require_once 'config.php';

// Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?page=dashboard');
    exit();
}

$id     = isset($_GET['id'])     ? (int)$_GET['id']     : 0;
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if (!$id || !in_array($action, ['verify', 'reject'])) {
    header('Location: index.php?page=admin');
    exit();
}

$success_msg = '';
$error_msg   = '';

// POST: admin confirmed their decision
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = isset($_POST['action'])  ? trim($_POST['action'])  : '';
    $post_id     = isset($_POST['stay_id']) ? (int)$_POST['stay_id'] : 0;

    if (!$post_id || !in_array($post_action, ['verify', 'reject'])) {
        header('Location: index.php?page=admin');
        exit();
    }

    if ($post_action === 'verify') {
        $stmt = $conn->prepare("UPDATE homestays SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $success_msg = "Homestay approved successfully. It is now visible to guests.";
        } else {
            $error_msg = "Database error. Please try again.";
        }
        $stmt->close();
    } else {
        // Soft-reject: verified = -1 so host can see it was reviewed and declined
        $stmt = $conn->prepare("UPDATE homestays SET verified = -1 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $success_msg = "Homestay rejected. The listing has been flagged and hidden from guests.";
        } else {
            $error_msg = "Database error. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch the stay (only if no decision was just made)
$stay = null;
if (!$success_msg && !$error_msg) {
    $stmt = $conn->prepare("
        SELECT h.*, u.name AS host_name, u.email AS host_email
        FROM   homestays h
        JOIN   users u ON h.host_id = u.id
        WHERE  h.id = ? AND h.verified = 0
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stay   = $result->fetch_assoc();
    $stmt->close();

    if (!$stay) {
        header('Location: index.php?page=admin');
        exit();
    }
}

include 'header.php';
?>

<div class="container">

<?php if ($success_msg): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success_msg); ?>
        <a href="index.php?page=admin" style="margin-left:1rem;">Back to Dashboard</a>
    </div>

<?php elseif ($error_msg): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_msg); ?>
        <a href="index.php?page=admin" style="margin-left:1rem;">Back to Dashboard</a>
    </div>

<?php else: ?>

    <h1>Review Listing</h1>
    <a href="index.php?page=admin" class="btn-small" style="margin-bottom:1rem;display:inline-block;">&larr; Back to Admin Dashboard</a>

    <table class="table">
        <thead>
            <tr>
                <th colspan="2">
                    <?php echo htmlspecialchars($stay['title']); ?>
                    &nbsp;<span style="font-size:.75rem;background:#fff3cd;color:#664d03;border:1px solid #ffda6a;border-radius:4px;padding:.2em .55em;font-weight:600;">Pending</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Host</strong></td>
                <td>
                    <?php echo htmlspecialchars($stay['host_name']); ?>
                    <small>(<?php echo htmlspecialchars($stay['host_email']); ?>)</small>
                </td>
            </tr>
            <tr>
                <td><strong>Location</strong></td>
                <td><?php echo htmlspecialchars($stay['location']); ?></td>
            </tr>
            <tr>
                <td><strong>Price / night</strong></td>
                <td>$<?php echo number_format($stay['price'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Cultural Tag</strong></td>
                <td><?php echo htmlspecialchars($stay['cultural_tag'] ?? 'none'); ?></td>
            </tr>
            <tr>
                <td><strong>Submitted</strong></td>
                <td><?php echo date('M d, Y \a\t H:i', strtotime($stay['created_at'])); ?></td>
            </tr>
            <?php if (!empty($stay['description'])): ?>
            <tr>
                <td><strong>Description</strong></td>
                <td><?php echo nl2br(htmlspecialchars($stay['description'])); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($stay['image']) && $stay['image'] !== 'placeholder.jpg'): ?>
        <p><strong>Listing Photo:</strong></p>
        <img src="uploads/<?php echo htmlspecialchars($stay['image']); ?>"
             alt="Listing photo"
             style="max-width:100%;max-height:350px;object-fit:cover;border-radius:6px;"
             onerror="this.style.display='none'">
    <?php endif; ?>

    <h2 style="margin-top:1.5rem;">Admin Decision</h2>

    <?php if ($action === 'verify'): ?>
        <p>Approving will make this listing <strong>live</strong> and visible to all guests.</p>
        <form method="POST" action="verify-stay.php?id=<?php echo $stay['id']; ?>&action=verify">
            <input type="hidden" name="stay_id" value="<?php echo $stay['id']; ?>">
            <input type="hidden" name="action"  value="verify">
            <a href="index.php?page=admin" class="btn-small btn-danger">Cancel</a>
            &nbsp;
            <button type="submit" class="btn-small"
                    onclick="return confirm('Approve this listing and make it live?')">
                &#10003; Confirm Approval
            </button>
        </form>

    <?php elseif ($action === 'reject'): ?>
        <p>Rejecting will <strong>hide</strong> this listing from guests and flag it for the host.</p>
        <form method="POST" action="verify-stay.php?id=<?php echo $stay['id']; ?>&action=reject">
            <input type="hidden" name="stay_id" value="<?php echo $stay['id']; ?>">
            <input type="hidden" name="action"  value="reject">
            <a href="index.php?page=admin" class="btn-small">Cancel</a>
            &nbsp;
            <button type="submit" class="btn-small btn-danger"
                    onclick="return confirm('Reject and hide this listing?')">
                &#10007; Confirm Rejection
            </button>
        </form>
    <?php endif; ?>

<?php endif; ?>

</div>

<?php include 'footer.php'; ?>
