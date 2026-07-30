<?php

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigFilter;

class WisdomRenderingExtension
{
    #[AsTwigFilter('render_code', isSafe: ['html'])]
    public function renderCode(string $text): string
    {
        return preg_replace(
            '/`([^`]+)`/',
            '<code>$1</code>',
            $text
        );
    }

    #[AsTwigFilter('render_url', isSafe: ['html'])]
    public function renderUrl(string $text): string
    {
        return preg_replace_callback(
            '/(https?:\/\/[^\s<]+(?<![.,;:!?)]))([.,;:!?)]*)/i',
            static function (array $matches): string {
                $url = $matches[1];
                $punctuation = $matches[2];

                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>' . $punctuation;
            },
            $text
        );
    }
}
