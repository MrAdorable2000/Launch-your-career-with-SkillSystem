<?php
/**
 * Shared Messages Inbox — Real AJAX chat for all roles.
 *
 * Data: $inbox (paginated), $unread, $activeConversation, $activeMessages, $withUserId
 *
 * This view is rendered inside layouts/app.php for any logged-in role.
 * The sidebar menu is role-based (handled by the layout).
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;

$pageTitle = 'Messages';

$conversations = $inbox['data'] ?? [];
$total = (int)($inbox['total'] ?? 0);

// Active conversation partner
$partner = $activeConversation;
$partnerName = $partner ? trim(($partner['first_name'] ?? '') . ' ' . ($partner['last_name'] ?? '')) : '';
$partnerInitial = $partner ? strtoupper(substr($partnerName, 0, 1)) : '?';
$partnerRole = $partner['role_name'] ?? '';
$partnerId = $partner['id'] ?? 0;
$myId = Session::userId();
$myName = Session::userName();
$myInitial = strtoupper(substr($myName ?? 'U', 0, 1));

// Role-based dashboard URL for back links
$role = Session::userRole();
$dashboardUrl = URL::to($role . '/dashboard');
?>

<?= Component::pageHeader(
    'Messages',
    '<a href="' . $dashboardUrl . '">Dashboard</a> / <span>Messages</span>',
    '<button class="ss-btn ss-btn-gradient ss-btn-sm" data-bs-toggle="modal" data-bs-target="#newMsgModal"><i class="fas fa-pen"></i> New Message</button>'
) ?>

<div class="ss-chat" id="ssChat">
    <!-- ==================== CONVERSATION LIST ==================== -->
    <div class="ss-chat-list" id="ssChatList">
        <div class="ss-chat-list-header">
            <h5>Conversations</h5>
            <div class="ss-topbar-search" style="width:100%;">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search conversations..." id="convSearch" oninput="filterConversations(this.value)">
            </div>
        </div>
        <div class="ss-chat-list-body" id="convList">
            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $c):
                    // The improved getInbox() returns 'other_id' (the conversation partner's user ID)
                    // and the partner's first_name/last_name/avatar directly.
                    $otherId = (int)($c['other_id'] ?? 0);
                    if ($otherId === 0) {
                        // Fallback for old query format
                        $otherId = ($c['sender_id'] == $myId) ? $c['receiver_id'] : $c['sender_id'];
                    }
                    $otherName = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?: 'Unknown User';
                    $otherInitial = strtoupper(substr($otherName, 0, 1));
                    $isUnread = ($c['receiver_id'] == $myId) && empty($c['read_at']);
                    $isActive = ($partnerId == $otherId);
                    $lastMsg = $c['body'] ?? '';
                    if (strlen($lastMsg) > 50) $lastMsg = substr($lastMsg, 0, 50) . '...';
                    $time = date('M j', strtotime($c['created_at'] ?? 'now'));
                ?>
                <div class="ss-chat-item <?= $isActive ? 'active' : '' ?> <?= $isUnread ? 'unread' : '' ?>" data-conv-id="<?= (int)$otherId ?>" data-conv-name="<?= htmlspecialchars($otherName) ?>" onclick="loadConversation(<?= (int)$otherId ?>)">
                    <div class="ss-avatar ss-avatar-md"><?= $otherInitial ?></div>
                    <div class="chat-meta">
                        <div class="chat-name">
                            <span><?= htmlspecialchars($otherName) ?></span>
                            <span style="font-size:0.68rem;color:var(--ss-text-3);font-weight:400;"><?= $time ?></span>
                        </div>
                        <div class="chat-preview"><?= htmlspecialchars($lastMsg) ?></div>
                    </div>
                    <?php if ($isUnread): ?><span class="unread-badge">New</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center" style="padding:2rem 1rem;color:var(--ss-text-3);">
                    <i class="fas fa-inbox mb-2 d-block" style="font-size:2rem;opacity:0.3;"></i>
                    <div style="font-size:0.85rem;">No conversations yet</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== CHAT WINDOW ==================== -->
    <div class="ss-chat-window" id="ssChatWindow">
        <?php if ($partner): ?>
            <div class="ss-chat-window-header">
                <button class="ss-btn ss-btn-ghost ss-btn-sm d-md-none" onclick="document.getElementById('ssChatList').classList.add('show'); document.getElementById('ssChatWindow').style.display='none';"><i class="fas fa-arrow-left"></i></button>
                <div class="ss-avatar ss-avatar-md"><?= $partnerInitial ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:0.95rem;"><?= htmlspecialchars($partnerName) ?></div>
                    <div style="font-size:0.75rem;color:var(--ss-text-3);">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--ss-success);margin-right:4px;"></span>
                        <?= htmlspecialchars($partnerRole) ?> · <?= htmlspecialchars($partner['email'] ?? '') ?>
                    </div>
                </div>
                <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Call"><i class="fas fa-phone"></i></button>
                <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Video"><i class="fas fa-video"></i></button>
            </div>
            <div class="ss-chat-messages" id="chatMessages">
                <?php if (!empty($activeMessages)): ?>
                    <?php
                    $lastDate = '';
                    foreach ($activeMessages as $msg):
                        $isMe = ($msg['sender_id'] == $myId);
                        $msgDate = date('M j, Y', strtotime($msg['created_at'] ?? 'now'));
                        if ($msgDate !== $lastDate):
                            $lastDate = $msgDate;
                            echo '<div style="text-align:center;margin:1rem 0;font-size:0.72rem;color:var(--ss-text-3);font-weight:600;">' . htmlspecialchars($msgDate) . '</div>';
                        endif;
                    ?>
                        <div class="ss-msg <?= $isMe ? 'ss-msg-sent' : 'ss-msg-received' ?>">
                            <?= nl2br(htmlspecialchars($msg['body'] ?? '')) ?>
                            <div class="ss-msg-time"><?= htmlspecialchars(date('g:i A', strtotime($msg['created_at'] ?? 'now'))) ?> <?= $isMe ? '<i class="fas fa-check-double" style="font-size:0.6rem;margin-left:2px;"></i>' : '' ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" style="padding:3rem 1rem;color:var(--ss-text-3);">
                        <i class="fas fa-comments mb-2 d-block" style="font-size:2rem;opacity:0.3;"></i>
                        <div style="font-size:0.85rem;">Start the conversation</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ss-chat-input">
                <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Attach file" onclick="window.ssToast && ssToast.show('File attachment coming soon!', 'info')"><i class="fas fa-paperclip"></i></button>
                <input type="text" id="msgInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter')sendMessage()" autofocus>
                <button class="ss-btn ss-btn-gradient ss-btn-sm" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;flex-direction:column;gap:1rem;color:var(--ss-text-3);">
                <i class="fas fa-comments" style="font-size:3rem;opacity:0.3;"></i>
                <div style="font-size:0.95rem;font-weight:600;color:var(--ss-text-2);">Select a conversation</div>
                <div style="font-size:0.82rem;">Choose a conversation from the list to start chatting</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== NEW MESSAGE MODAL ==================== -->
<div class="modal fade" id="newMsgModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="newMsgForm" onsubmit="sendNewMessage(event)">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary me-2"></i> New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group mb-3">
                        <label class="ss-form-label">To (User ID or Email)</label>
                        <input type="text" name="recipient" class="ss-input" placeholder="Enter user ID or email" required id="newMsgRecipient">
                    </div>
                    <div class="ss-form-group mb-3">
                        <label class="ss-form-label">Subject (optional)</label>
                        <input type="text" name="subject" class="ss-input" placeholder="Subject" id="newMsgSubject">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Message</label>
                        <textarea name="body" class="ss-textarea" placeholder="Type your message..." required id="newMsgBody"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================================
// AJAX MESSAGING ENGINE
// ============================================================
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const MY_ID = <?= (int)$myId ?>;
const MY_INITIAL = '<?= htmlspecialchars($myInitial) ?>';
const PARTNER_ID = <?= (int)$partnerId ?>;

// Load a conversation via AJAX
async function loadConversation(userId) {
    // Update active state
    document.querySelectorAll('.ss-chat-item').forEach(el => el.classList.remove('active'));
    const item = document.querySelector('[data-conv-id="' + userId + '"]');
    if (item) {
        item.classList.add('active');
        item.classList.remove('unread');
        const badge = item.querySelector('.unread-badge');
        if (badge) badge.remove();
    }

    try {
        const res = await fetch('<?= URL::to('api/messages/conversation/') ?>' + userId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.success) {
            renderConversation(data.partner, data.messages);
            // Update URL without reload
            history.replaceState(null, '', '<?= URL::to($role . '/messages') ?>?with=' + userId);
        }
    } catch (e) {
        console.error('Failed to load conversation:', e);
        if (window.ssToast) ssToast.show('Failed to load conversation', 'error');
    }

    // On mobile, show chat window
    if (window.innerWidth < 768) {
        document.getElementById('ssChatList').classList.remove('show');
        document.getElementById('ssChatWindow').style.display = 'flex';
    }
}

// Render the conversation in the chat window
function renderConversation(partner, messages) {
    const win = document.getElementById('ssChatWindow');
    const pName = (partner.first_name || '') + ' ' + (partner.last_name || '');
    const pInitial = pName.charAt(0).toUpperCase();
    const pRole = partner.role_name || '';
    const pEmail = partner.email || '';

    let html = `
        <div class="ss-chat-window-header">
            <button class="ss-btn ss-btn-ghost ss-btn-sm d-md-none" onclick="document.getElementById('ssChatList').classList.add('show');document.getElementById('ssChatWindow').style.display='none';"><i class="fas fa-arrow-left"></i></button>
            <div class="ss-avatar ss-avatar-md">${pInitial}</div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:0.95rem;">${escapeHtml(pName)}</div>
                <div style="font-size:0.75rem;color:var(--ss-text-3);">
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--ss-success);margin-right:4px;"></span>
                    ${escapeHtml(pRole)} · ${escapeHtml(pEmail)}
                </div>
            </div>
            <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Call"><i class="fas fa-phone"></i></button>
            <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Video"><i class="fas fa-video"></i></button>
        </div>
        <div class="ss-chat-messages" id="chatMessages">
    `;

    let lastDate = '';
    messages.forEach(msg => {
        const isMe = msg.sender_id == MY_ID;
        const msgDate = formatDate(msg.created_at);
        if (msgDate !== lastDate) {
            lastDate = msgDate;
            html += `<div style="text-align:center;margin:1rem 0;font-size:0.72rem;color:var(--ss-text-3);font-weight:600;">${msgDate}</div>`;
        }
        html += `<div class="ss-msg ${isMe ? 'ss-msg-sent' : 'ss-msg-received'}">${escapeHtml(msg.body).replace(/\n/g, '<br>')}<div class="ss-msg-time">${formatTime(msg.created_at)} ${isMe ? '<i class="fas fa-check-double" style="font-size:0.6rem;margin-left:2px;"></i>' : ''}</div></div>`;
    });

    if (messages.length === 0) {
        html += `<div class="text-center" style="padding:3rem 1rem;color:var(--ss-text-3);"><i class="fas fa-comments mb-2 d-block" style="font-size:2rem;opacity:0.3;"></i><div style="font-size:0.85rem;">Start the conversation</div></div>`;
    }

    html += `</div>
        <div class="ss-chat-input">
            <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Attach" onclick="window.ssToast && ssToast.show('File attachment coming soon!', 'info')"><i class="fas fa-paperclip"></i></button>
            <input type="text" id="msgInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter')sendMessage()" autofocus>
            <button class="ss-btn ss-btn-gradient ss-btn-sm" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    `;

    win.innerHTML = html;
    win.style.display = 'flex';
    scrollToBottom();

    // Update the partner ID for sending
    window.currentPartnerId = partner.id;
}

// Send a message via AJAX
async function sendMessage() {
    const input = document.getElementById('msgInput');
    if (!input) return;
    const body = input.value.trim();
    if (!body) return;
    const partnerId = window.currentPartnerId || PARTNER_ID;
    if (!partnerId) return;

    input.value = '';
    input.disabled = true;

    try {
        const res = await fetch('<?= URL::to('api/messages/send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                receiver_id: partnerId,
                body: body,
                _token: CSRF_TOKEN
            }),
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.success) {
            // Append the message to the chat
            const container = document.getElementById('chatMessages');
            if (container) {
                const div = document.createElement('div');
                div.className = 'ss-msg ss-msg-sent';
                div.innerHTML = escapeHtml(body).replace(/\n/g, '<br>') + '<div class="ss-msg-time">' + new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) + ' <i class="fas fa-check-double" style="font-size:0.6rem;margin-left:2px;"></i></div>';
                container.appendChild(div);
                scrollToBottom();
            }
        } else {
            if (window.ssToast) ssToast.show(data.message || 'Failed to send', 'error');
            input.value = body; // Restore the message
        }
    } catch (e) {
        console.error('Send failed:', e);
        if (window.ssToast) ssToast.show('Network error', 'error');
        input.value = body;
    }
    input.disabled = false;
    input.focus();
}

// Send a new message from the modal
async function sendNewMessage(e) {
    e.preventDefault();
    const recipient = document.getElementById('newMsgRecipient').value.trim();
    const subject = document.getElementById('newMsgSubject').value.trim();
    const body = document.getElementById('newMsgBody').value.trim();

    if (!recipient || !body) return;

    // Try to resolve recipient as user ID first, then by email
    let receiverId = parseInt(recipient);
    if (isNaN(receiverId) || receiverId <= 0) {
        // It's an email — we need to look it up
        if (window.ssToast) ssToast.show('Please enter a numeric user ID. Email lookup coming soon!', 'warning');
        return;
    }

    try {
        const res = await fetch('<?= URL::to('api/messages/send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                body: body,
                subject: subject,
                _token: CSRF_TOKEN
            }),
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newMsgModal'))?.hide();
            if (window.ssToast) ssToast.show('Message sent!', 'success');
            // Reload to show the new conversation
            setTimeout(() => location.reload(), 800);
        } else {
            if (window.ssToast) ssToast.show(data.message || 'Failed to send', 'error');
        }
    } catch (e) {
        if (window.ssToast) ssToast.show('Network error', 'error');
    }
}

// Filter conversations by search
function filterConversations(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.ss-chat-item').forEach(el => {
        const name = el.dataset.convName || '';
        el.style.display = name.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Scroll to bottom of chat
function scrollToBottom() {
    const c = document.getElementById('chatMessages');
    if (c) c.scrollTop = c.scrollHeight;
}

// Helpers
function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function formatDate(dt) { const d = new Date(dt + 'Z'); return d.toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}); }
function formatTime(dt) { const d = new Date(dt + 'Z'); return d.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}); }

// Auto-scroll on load
scrollToBottom();

// Poll for new messages every 15 seconds (lightweight — just reload the conversation)
setInterval(() => {
    const pid = window.currentPartnerId || PARTNER_ID;
    if (pid && document.getElementById('chatMessages')) {
        loadConversation(pid);
    }
}, 15000);
</script>
