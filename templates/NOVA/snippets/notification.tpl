{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card no-body=true class="clearfix {if isset($inline)} m-0{/if}"}
    {if isset($title)}
        {cardheader}{$title}{/cardheader}
    {/if}

    {cardbody class="notification-alert bg-{if isset($type)}{$type}{else}info{/if}"}
        {$body}
    {/cardbody}

    {if isset($buttons)}
        {cardfooter}
            {buttongroup class="d-block"}
                {foreach $buttons as $button}
                    {link
                        href="{get_static_route id=$button->href}"
                        class="btn{if isset($button->primary) && $button->primary} btn-primary{else} btn-secondary{/if}"
                        data=["dismiss"=>"{if isset($button->dismiss)}{$button->dismiss}{/if}"]
                        aria=["label"=>"{if isset($button->dismiss)}Close{/if}"]
                    }
                        {if isset($button->fa)}<i class="fa {$button->fa}"></i>{/if}
                        {$button->title}
                    {/link}
                {/foreach}
            {/buttongroup}
        {/cardfooter}
    {/if}
{/card}
