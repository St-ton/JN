{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($linkgroupIdentifier)}
{strip}
{assign var=checkLinkParents value=false}
    {if isset($Link) && $Link->getID() > 0}
        {assign var=activeId value=$Link->getID()}
    {elseif Shop::$kLink > 0}
        {assign var=activeId value=JTL\Shop::$kLink}
        {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
    {/if}
    {if !isset($activeParents) && (isset($Link))}
        {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
        {assign var=checkLinkParents value=true}
    {/if}
    {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
    {if !empty($links)}
        {foreach $links as $li}
            {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}
                {navitemdropdown text=$li->getName() data=["tab"=>"lg{$li@iteration}"]}
                    {container}
                        {link href=$li->getURL() title=$li->getName()}
                            {$li->getName()}
                        {/link}
                        <hr class="hr-sm d-none d-md-block">
                        {row}
                        {foreach $li->getChildLinks() as $subli}
                            {col cols=12 md=6 lg=3}
                                {if !empty($subli->getName())}
                                    {dropdownitem tag="div" active=($subli->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($subli->getID(), $activeParents)))}
                                        <div class="title mt-3">
                                            {link href=$subli->getURL() rel="{if $subli->getNoFollow()}nofollow{/if}"}
                                                {$subli->getName()}
                                            {/link}
                                        </div>
                                    {/dropdownitem}
                                {/if}
                            {/col}
                        {/foreach}
                        {/row}
                    {/container}
                {/navitemdropdown}
            {else}
                {navitem href=$li->getURL() nofollow=$li->getNoFollow() data=["tab"=>"lg{$li@iteration}"]}
                    {$li->getName()}
                {/navitem}
            {/if}
        {/foreach}
    {/if}
{/strip}
{/if}
