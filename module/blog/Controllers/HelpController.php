<?php

require_once __DIR__ . '/../Model/help.php';

class HelpController
{
    public function index()
    {
        $faq = Help::getFaq();
        require __DIR__ . '/../View/help.php';
    }
}