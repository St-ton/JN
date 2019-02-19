{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<ul class="filter_tag nav nav-list">
    {foreach $NaviFilter->getTagFilter() as $oTagFilter}
        <li>
            {link rel="nofollow" href=$NaviFilter->URL->getTags() class="active"}
                <span class="value">
                    <i class="fa fa-check-square-o text-muted"></i> {$oTagFilter->getName()}
                </span>
            {/link}
        </li>
    {/foreach}
</ul>
