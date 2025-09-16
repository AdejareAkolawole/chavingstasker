<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// --- NEW: MESSAGE SENDING LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
    $sender_id = $_SESSION['user_id'];

    if (!empty(trim($content)) && !empty($receiver_id)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $content]);
            
            // Redirect to the same conversation to show the new message
            header("Location: messages.php?user_id=" . $receiver_id);
            exit;
        } catch (PDOException $e) {
            $error = "Could not send message.";
        }
    }
}
// --- END OF FIX ---


// Fetch conversations with unread message counts
$conversations_stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name,
        (SELECT COUNT(*) FROM messages m WHERE m.receiver_id = ? AND m.sender_id = u.id AND m.is_read = FALSE) as unread_count
    FROM users u
    WHERE u.id IN (
        SELECT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = ?
    ) AND u.id != ?
    ORDER BY (SELECT MAX(created_at) FROM messages m WHERE (m.sender_id = u.id AND m.receiver_id = ?) OR (m.receiver_id = u.id AND m.sender_id = ?)) DESC
");
$conversations_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversations = $conversations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages for selected conversation
$selected_user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
$messages = [];
$selected_user = null;
if ($selected_user_id) {
    $messages_stmt = $pdo->prepare("
        SELECT m.content, m.created_at, m.sender_id
        FROM messages m
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $messages_stmt->execute([$_SESSION['user_id'], $selected_user_id, $selected_user_id, $_SESSION['user_id']]);
    $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch the selected user's name for the header
    $selected_user_stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $selected_user_stmt->execute([$selected_user_id]);
    $selected_user = $selected_user_stmt->fetch(PDO::FETCH_ASSOC);

    // Mark messages as read
    $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND sender_id = ?")
        ->execute([$_SESSION['user_id'], $selected_user_id]);
}

// Fetch total unread message count for sidebar
$unread_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Chavings Tasker</title>
    <link rel="stylesheet" href="messages.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">Chavings Tasker</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Menu</p>
                <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                <a href="apply_job.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item active"><i class="fas fa-comments"></i><span>Messages</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                
                <p class="nav-section-title">Grow Your Business</p>
                <a href="promote_gig.php" class="nav-item premium"><i class="fas fa-rocket"></i><span>Promote Gig</span></a>
                <a href="featured_profile.php" class="nav-item premium"><i class="fas fa-star"></i><span>Featured Profile</span></a>
                <a href="business_tools.php" class="nav-item premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
            </nav>
            <div class="sidebar-footer">
                 <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Conversations</h1>
                    <p>Chat directly with clients and freelancers.</p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme"></button>
                </div>
            </header>

            <div class="chat-layout">
                <div class="conversations-panel">
                    <div class="panel-header"><h2>Inbox</h2></div>
                    <div class="conversations-list">
                        <?php if (empty($conversations)): ?>
                            <p class="empty-state">No conversations yet.</p>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                            <a href="messages.php?user_id=<?php echo $conv['id']; ?>" class="conversation-item <?php echo $selected_user_id == $conv['id'] ? 'active' : ''; ?>">
                                <div class="avatar"><?php echo strtoupper(substr($conv['first_name'], 0, 1)); ?></div>
                                <div class="conv-details">
                                    <span class="conv-name"><?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']); ?></span>
                                    <p class="conv-preview">Last message preview...</p>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-dot"></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chat-panel">
                    <?php if ($selected_user): ?>
                        <div class="chat-header">
                            <h3>Chat with <?php echo htmlspecialchars($selected_user['first_name'] . ' ' . $selected_user['last_name']); ?></h3>
                        </div>
                        <div class="chat-messages" id="chat-messages">
                            <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                                <div class="message-bubble"><?php echo htmlspecialchars($msg['content']); ?></div>
                                <span class="message-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="chat-input-area">
                            <form action="messages.php?user_id=<?php echo $selected_user_id; ?>" method="post" class="message-form">
                                <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                                <textarea name="content" placeholder="Type your message..." required></textarea>
                                <button type="submit" class="cta-btn send-btn"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-chat-selected">
                            <i class="fas fa-comments"></i>
                            <h2>Select a conversation</h2>
                            <p>Choose a person from the list to start chatting.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        // (Theme toggle script)
        // Auto-scroll to the bottom of the chat
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>