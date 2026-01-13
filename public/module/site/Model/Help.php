<?php

// phpcs:disable Generic.Files.LineLength

namespace Model;

class Help
{
    /**
     * Return a list of frequently asked questions (FAQ)
     *
     * Each FAQ item contains:
     * - question: string The question text
     * - answer: string The answer text, may contain HTML
     *
     * @return array List of FAQ items
     */
    public static function getFaq()
    {
        return [
            [
                'question' => "How to reset a user's password?",
                'answer' => "From the login page, click on \"Forgot password\" and follow the instructions."
            ],
            [
                'question' => "Who to contact in case of a technical issue?",
                'answer' => "Contact AMU IT support at <a href=\"mailto:support@amu.fr\">support@amu.fr</a>."
            ]
        ];
    }
}
