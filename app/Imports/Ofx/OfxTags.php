<?php

namespace App\Imports\Ofx;

final class OfxTags
{
    /**
     * @return array<int, string>
     */
    public function blocks(string $contents, string $tag): array
    {
        $pattern = '/<' . preg_quote($tag, '/') . '\b[^>]*>(.*?)<\/' . preg_quote($tag, '/') . '>/is';

        return preg_match_all($pattern, $contents, $matches) ? $matches[1] : [];
    }

    public function value(string $block, string ...$names): ?string
    {
        foreach ($names as $name) {
            $pattern = '/<' . preg_quote($name, '/') . '\b[^>]*>([^<\r\n]*)/i';

            if (preg_match($pattern, $block, $match) && trim($match[1]) !== '') {
                return trim($match[1]);
            }
        }

        return null;
    }
}
