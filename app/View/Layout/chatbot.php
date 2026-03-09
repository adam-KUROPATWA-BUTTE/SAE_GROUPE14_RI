<?php
/**
 * Chatbot/Assistant commun
 *
 * @var Closure(array<string, string>): string $t
 */
?>
<div id="help-bubble">💬</div>

<div id="help-popup" class="chat-popup">
    <div class="help-popup-header">
        <span><?= $t(['fr' => 'Assistant', 'en' => 'Assistant']) ?></span>
        <button>✖</button>
    </div>
    <div id="chat-messages" class="chat-messages"></div>
    <div id="quick-actions" class="quick-actions"></div>
</div>