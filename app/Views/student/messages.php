<?php
/**
 * Messages — Premium redesign (v3) (chat layout)
 *
 * Data passed from StudentController::messages():
 *   $inbox — paginated array with keys: data[], total, current_page, per_page, last_page
 *
 * Each row has: id, sender_id, receiver_id, subject, body, read_at, created_at,
 *               first_name, last_name, avatar, sender_role
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Messages';

$inbox         = $inbox ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1];
$conversations = $inbox['data'] ?? [];
$total         = (int)($inbox['total'] ?? 0);
$currentPage   = (int)($inbox['current_page'] ?? 1);
$lastPage      = (int)($inbox['last_page'] ?? 1);
$unreadCount   = 0;
foreach ($conversations as $c) if (empty($c['read_at'])) $unreadCount++;

// Pick the first conversation as the "active" one for the chat window
$active = $conversations[0] ?? null;

// Mock messages for the chat window (since the controller only sends the inbox list, not full threads)
$mockThread = [
    ['from' => 'them', 'text' => "Hi! Thanks for your interest in the Junior Backend Engineer role. I reviewed your application and was impressed by your portfolio.", 'time' => '10:32 AM'],
    ['from' => 'me',   'text' => "Thank you! I'm really excited about the opportunity to work with the Andela team.", 'time' => '10:35 AM'],
    ['from' => 'them', 'text' => "Could you walk me through your experience with PHP and Laravel? Specifically, how have you handled database migrations in production?", 'time' => '10:36 AM'],
    ['from' => 'me',   'text' => "Absolutely. In my last project I built a multi-tenant SaaS app with Laravel 10 — I used migration groups and zero-downtime deploys to safely roll out schema changes.", 'time' => '10:41 AM'],
    ['from' => 'them', 'text' => "That sounds great. Can we schedule a 30-minute call this week to dig deeper? I'm free Wednesday or Thursday afternoon.", 'time' => '10:43 AM'],
];

$activeName     = $active ? trim(($active['first_name'] ?? '') . ' ' . ($active['last_name'] ?? '')) : 'Select a conversation';
$activeRole     = $active['sender_role'] ?? '';
$activeInitial  = strtoupper(substr($activeName, 0, 1));
?>
<?= Component::pageHeader(
    'Messages',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Messages</span>',
    '<span class="ss-badge ss-badge-warning ss-badge-lg"><i class="fas fa-envelope"></i> ' . $unreadCount . ' unread</span>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#newMessageModal"><i class="fas fa-pen"></i> <span class="d-none d-md-inline">New Message</span></button>'
) ?>

<?php if (empty($conversations) && !$active): ?>
<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-body">
        <?= Component::emptyState([
            'icon'   => 'fa-envelope-open',
            'title'  => 'Your inbox is empty',
            'desc'   => "When employers or mentors message you, their conversations will appear here. Start a new conversation to reach out to someone.",
            'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#newMessageModal"><i class="fas fa-pen"></i> Start a Conversation</button>'
        ]) ?>
    </div>
</div>
<?php else: ?>
<div class="ss-chat ss-animate-fade-up">
    <!-- ============== CONVERSATION LIST ============== -->
    <div class="ss-chat-list">
        <div class="ss-chat-list-header">
            <h5><i class="fas fa-inbox text-primary me-1"></i> Inbox</h5>
            <div class="ss-input-icon mt-2">
                <i class="fas fa-search"></i>
                <input type="text" class="ss-input ss-input-sm" placeholder="Search conversations..." data-live-search="#chat-list-body" data-live-search-item=".ss-chat-item">
            </div>
        </div>
        <div class="ss-chat-list-body" id="chat-list-body">
            <?php foreach ($conversations as $i => $msg):
                $name      = trim(($msg['first_name'] ?? '') . ' ' . ($msg['last_name'] ?? '')) ?: 'Unknown sender';
                $initial   = strtoupper(substr($name, 0, 1));
                $preview   = $msg['subject'] ?? ($msg['body'] ?? '(No message)');
                $isUnread  = empty($msg['read_at']);
                $isActive  = $i === 0;
                $avatarSrc = $msg['avatar'] ?? null;
            ?>
            <div class="ss-chat-item <?= $isActive ? 'active' : '' ?> <?= $isUnread ? 'unread' : '' ?>" data-conv-id="<?= (int)$msg['id'] ?>">
                <div class="ss-avatar ss-avatar-md">
                    <?php if (!empty($avatarSrc)): ?>
                        <img src="<?= URL::asset($avatarSrc) ?>" alt="<?= htmlspecialchars($name) ?>">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                    <span class="online-dot"></span>
                </div>
                <div class="chat-meta">
                    <div class="chat-name">
                        <span class="ss-truncate"><?= htmlspecialchars($name) ?></span>
                        <span style="font-size:0.7rem;color:var(--ss-text-3);font-weight:500;"><?= htmlspecialchars(timeAgo($msg['created_at'])) ?></span>
                    </div>
                    <div class="chat-preview ss-truncate"><?= htmlspecialchars($preview) ?></div>
                    <div style="font-size:0.7rem;color:var(--ss-text-3);margin-top:2px;"><i class="fas fa-tag me-1"></i><?= htmlspecialchars(ucfirst($msg['sender_role'] ?? 'user')) ?></div>
                </div>
                <?php if ($isUnread): ?>
                <span class="unread-badge">New</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($lastPage > 1): ?>
        <div style="padding:0.65rem 1rem;border-top:1px solid var(--ss-border);background:var(--ss-surface-2);">
            <div class="d-flex align-items-center justify-content-between" style="font-size:0.75rem;">
                <span style="color:var(--ss-text-3);">Page <?= $currentPage ?>/<?= $lastPage ?></span>
                <div class="ss-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="page-btn" href="<?= URL::to('student/messages?page=' . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php if ($currentPage < $lastPage): ?>
                        <a class="page-btn" href="<?= URL::to('student/messages?page=' . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ============== CHAT WINDOW ============== -->
    <div class="ss-chat-window">
        <!-- Header -->
        <div class="ss-chat-window-header">
            <button class="ss-btn ss-btn-icon d-md-none" onclick="document.querySelector('.ss-chat-list').classList.add('show');"><i class="fas fa-arrow-left"></i></button>
            <div class="ss-avatar ss-avatar-md">
                <?php if (!empty($active['avatar'])): ?>
                    <img src="<?= URL::asset($active['avatar']) ?>" alt="<?= htmlspecialchars($activeName) ?>">
                <?php else: ?>
                    <?= $activeInitial ?>
                <?php endif; ?>
                <span class="online-dot" style="position:absolute;right:0;bottom:0;width:10px;height:10px;background:var(--ss-success);border:2px solid var(--ss-surface);border-radius:50%;"></span>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="fw-semibold" style="font-size:0.95rem;"><?= htmlspecialchars($activeName) ?></div>
                <div style="font-size:0.75rem;color:var(--ss-success);"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active now · <?= htmlspecialchars(ucfirst($activeRole)) ?></div>
            </div>
            <div class="d-flex gap-1">
                <button class="ss-btn ss-btn-icon" title="Call"><i class="fas fa-phone"></i></button>
                <button class="ss-btn ss-btn-icon" title="Video call"><i class="fas fa-video"></i></button>
                <button class="ss-btn ss-btn-icon" title="More"><i class="fas fa-ellipsis-v"></i></button>
            </div>
        </div>

        <!-- Messages -->
        <div class="ss-chat-messages" id="chat-messages">
            <!-- Day separator -->
            <div style="text-align:center;font-size:0.72rem;color:var(--ss-text-3);margin:0.5rem 0;position:relative;">
                <span style="background:var(--ss-surface-2);padding:0.25rem 0.85rem;border-radius:var(--ss-radius-pill);border:1px solid var(--ss-border);">Today</span>
            </div>

            <?php foreach ($mockThread as $m): ?>
                <div class="ss-msg ss-msg-<?= $m['from'] === 'me' ? 'sent' : 'received' ?>"><?= htmlspecialchars($m['text']) ?></div>
                <div class="ss-msg-time" style="<?= $m['from'] === 'me' ? 'align-self:flex-end;' : 'align-self:flex-start;' ?>"><?= htmlspecialchars($m['time']) ?><?= $m['from'] === 'me' ? ' · <i class="fas fa-check-double" style="color:var(--ss-primary);"></i>' : '' ?></div>
            <?php endforeach; ?>

            <!-- Typing indicator -->
            <div class="ss-typing-indicator">
                <span></span><span></span><span></span>
            </div>
            <div class="ss-msg-time" style="align-self:flex-start;"><?= htmlspecialchars($activeName) ?> is typing…</div>
        </div>

        <!-- Input -->
        <form class="ss-chat-input" onsubmit="event.preventDefault(); if (window.ssToast) { ssToast.show('Message sent (demo)', 'success'); } this.reset();">
            <?= $csrfField ?? '' ?>
            <button type="button" class="ss-btn ss-btn-icon" title="Attach file" onclick="document.getElementById('chat-file-input').click()"><i class="fas fa-paperclip"></i></button>
            <input type="file" id="chat-file-input" style="display:none;" multiple>
            <input type="text" name="body" placeholder="Type a message..." required>
            <button type="button" class="ss-btn ss-btn-icon" title="Emoji"><i class="fas fa-smile"></i></button>
            <button type="submit" class="ss-btn ss-btn-gradient" style="padding:0.55rem 1rem;"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<!-- Mobile back button hint -->
<div class="d-md-none text-center mt-2" style="font-size:0.75rem;color:var(--ss-text-3);">
    <i class="fas fa-info-circle"></i> Tap a conversation to view messages.
</div>
<?php endif; ?>

<!-- ============== NEW MESSAGE MODAL ============== -->
<div class="modal fade" id="newMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen text-primary me-1"></i> New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URL::to('student/messages/send') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="msg-to">To <span class="req">*</span></label>
                        <input type="text" name="recipient" id="msg-to" class="ss-input" placeholder="Name or email address" required>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="msg-subject">Subject <span class="req">*</span></label>
                        <input type="text" name="subject" id="msg-subject" class="ss-input" placeholder="What's this about?" required>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="msg-body">Message <span class="req">*</span></label>
                        <textarea name="body" id="msg-body" class="ss-textarea" rows="5" placeholder="Write your message..." required></textarea>
                    </div>
                    <label class="ss-file-upload" style="cursor:pointer;">
                        <input type="file" name="attachment" data-file-preview="#new-msg-attachment" style="display:none;">
                        <div class="upload-icon" style="font-size:1.25rem;"><i class="fas fa-paperclip"></i></div>
                        <div class="upload-text" style="font-size:0.85rem;">Attach a file (optional)</div>
                        <div class="upload-hint">PDF, DOC, JPG, PNG · Max 10MB</div>
                    </label>
                    <div id="new-msg-attachment" style="display:none;margin-top:0.75rem;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Lightweight chat behaviour: clicking a conversation in the list selects it.
(function() {
    const items = document.querySelectorAll('.ss-chat-item');
    items.forEach((item) => {
        item.addEventListener('click', () => {
            items.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');
            item.classList.remove('unread');
            const badge = item.querySelector('.unread-badge');
            if (badge) badge.remove();
            if (window.innerWidth < 768) {
                document.querySelector('.ss-chat-list').classList.remove('show');
            }
        });
    });

    const msgs = document.getElementById('chat-messages');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;
})();
</script>
