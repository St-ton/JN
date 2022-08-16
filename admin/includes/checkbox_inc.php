<?php declare(strict_types=1);

use JTL\CheckBox;
use JTL\Language\LanguageModel;

/**
 * @param array           $post
 * @param LanguageModel[] $languages
 * @return array
 * @deprecated since 5.2.0
 */
function plausiCheckBox(array $post, array $languages): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param array $post - pre-filtered post data
 * @param LanguageModel[] $languages
 * @return CheckBox
 * @deprecated since 5.2.0
 */
function speicherCheckBox(array $post, array $languages): CheckBox
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new CheckBox();
}
