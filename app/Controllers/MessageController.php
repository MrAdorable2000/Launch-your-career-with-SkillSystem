<?php
/**
 * MessageController — Unified messaging for all roles.
 *
 * Features:
 *  - Inbox view (shared by student, employer, university, mentor)
 *  - AJAX get conversation (returns JSON message thread)
 *  - AJAX send message (returns JSON)
 *  - AJAX mark conversation as read
 */

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Helpers\Session;
use App\Helpers\URL;
use App\Models\MessageModel;
use App\Models\BaseModel;

class MessageController extends BaseController
{
    private MessageModel $msgModel;

    public function __construct()
    {
        $this->msgModel = new MessageModel();
        // Require login for all message routes
        if (!Session::isLoggedIn()) {
            URL::redirect('login');
            exit;
        }
    }

    /**
     * Display the inbox / chat interface.
     * Works for any logged-in role — the view auto-detects the role
     * to build the correct sidebar links.
     */
    public function inbox(): void
    {
        $page = $this->getInt('page', 1);
        $userId = Session::userId();
        $inbox = $this->msgModel->getInbox($userId, $page, 50);
        $unread = $this->msgModel->getUnreadCount($userId);

        // Get the active conversation if ?with=USER_ID is in the URL
        $withUserId = (int)($_GET['with'] ?? 0);
        $activeConversation = null;
        $activeMessages = [];
        if ($withUserId > 0) {
            $activeConversation = $this->getConversationPartner($withUserId);
            $activeMessages = $this->msgModel->getConversation($userId, $withUserId, 1, 100);
            // Mark messages from that user as read
            $this->markConversationReadInternal($withUserId);
        } elseif (!empty($inbox['data'])) {
            // Auto-select the first conversation
            $first = $inbox['data'][0];
            $partnerId = ($first['sender_id'] == $userId) ? $first['receiver_id'] : $first['sender_id'];
            $activeConversation = $this->getConversationPartner($partnerId);
            $activeMessages = $this->msgModel->getConversation($userId, $partnerId, 1, 100);
            $this->markConversationReadInternal($partnerId);
        }

        $this->view('messages/inbox', [
            'inbox' => $inbox,
            'unread' => $unread,
            'activeConversation' => $activeConversation,
            'activeMessages' => $activeMessages,
            'withUserId' => $withUserId,
        ]);
    }

    /**
     * AJAX: Get conversation with a specific user.
     * Returns JSON: { messages: [...], partner: {...} }
     */
    public function getConversation(int $userId): void
    {
        $myId = Session::userId();
        $messages = $this->msgModel->getConversation($myId, $userId, 1, 100);
        $partner = $this->getConversationPartner($userId);

        // Mark as read
        $this->markConversationReadInternal($userId);

        $this->json([
            'success' => true,
            'partner' => $partner,
            'messages' => $messages,
        ]);
    }

    /**
     * AJAX: Send a message.
     * Reads JSON body: { receiver_id, body, subject? }
     * Returns JSON: { success: true, message: {...} }
     */
    public function send(): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $_POST;
        }

        $receiverId = (int)($input['receiver_id'] ?? 0);
        $body = trim($input['body'] ?? '');
        $subject = trim($input['subject'] ?? '');

        if ($receiverId <= 0 || empty($body)) {
            $this->json(['success' => false, 'message' => 'Receiver and message body are required'], 400);
            return;
        }

        // Insert the message
        $base = new BaseModel();
        $base->execute(
            "INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)",
            [Session::userId(), $receiverId, $subject ?: null, $body]
        );
        $messageId = $base->lastId();

        // Fetch the inserted message with sender info
        $msg = $base->queryOne(
            "SELECT m.*, u.first_name, u.last_name, u.avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ?",
            [$messageId]
        );

        $this->json([
            'success' => true,
            'message' => 'Message sent',
            'data' => $msg,
        ]);
    }

    /**
     * AJAX: Mark all messages from a specific user as read.
     */
    public function markConversationRead(int $userId): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }
        $this->markConversationReadInternal($userId);
        $unread = $this->msgModel->getUnreadCount(Session::userId());
        $this->json(['success' => true, 'unread' => $unread]);
    }

    /**
     * Internal: mark all messages from $userId as read.
     */
    private function markConversationReadInternal(int $userId): void
    {
        try {
            $base = new BaseModel();
            $base->execute(
                "UPDATE messages SET read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL",
                [$userId, Session::userId()]
            );
        } catch (\Throwable $e) {}
    }

    /**
     * Get the conversation partner's user info.
     */
    private function getConversationPartner(int $userId): ?array
    {
        $base = new BaseModel();
        return $base->queryOne(
            "SELECT id, first_name, last_name, email, avatar, role_id, r.slug as role_slug, r.name as role_name
             FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?",
            [$userId]
        );
    }
}
