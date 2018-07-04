{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<ul class="list-unstyled">
    {foreach $Tagging as $tag}
        <li class="tag"><a href="{$tag->cURLFull}">{$tag->cName}</a> <span class="badge pull-right">{$tag->Anzahl}</span></li>
    {/foreach}
</ul>