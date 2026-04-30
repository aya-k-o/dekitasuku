<?php
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}