<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use Parsedown;

/**
 * Class Markdown
 * @package JTL\Plugin\Admin
 */
class Markdown extends Parsedown
{
    /**
     * @var string|null
     */
    private ?string $imagePrefixURL = null;

    /**
     * @param string $url
     */
    public function setImagePrefixURL(string $url): void
    {
        $this->imagePrefixURL = $url;
    }

    /**
     * @param array|mixed $excerpt
     * @return array|void|null
     */
    protected function inlineImage($excerpt)
    {
        $image = parent::inlineImage($excerpt);
        if (!isset($image)) {
            return null;
        }
        if ($this->imagePrefixURL === null
            || \str_starts_with($image['element']['attributes']['src'], 'http')
            || \str_starts_with($image['element']['attributes']['src'], '/')
        ) {
            return $image;
        }

        $image['element']['attributes']['src'] = $this->imagePrefixURL . $image['element']['attributes']['src'];

        return $image;
    }
}
