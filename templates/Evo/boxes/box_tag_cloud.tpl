{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-tagcloud" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='tagcloud'}</div>
        </div>
        <div class="box-body panel-body">
            <div class="tagbox">
                {foreach $oBox->getItems() as $item}
                    <a href="{$item->cURLFull}" class="tag{$item->Klasse}">{$item->cName}</a>
                {/foreach}
            </div>
        </div>
    </section>
{/if}
