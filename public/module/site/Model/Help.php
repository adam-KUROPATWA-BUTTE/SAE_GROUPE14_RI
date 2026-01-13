<?php

namespace Model;

/**
 * Class Help
 *
 * Model responsible for providing help and support data,
 * such as Frequently Asked Questions (FAQ).
 */
class Help
{
    /**
     * Retrieves the list of frequently asked questions.
     *
     * @return array<int, array{question: string, answer: string}> List of FAQ items.
     */
    public static function getFaq(): array
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