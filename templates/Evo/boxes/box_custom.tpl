{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<section class="panel panel-default box box-custom" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{$oBox->getTitle()}</div>
    </div>
    <div class="box-body panel-body panel-strap">
        {eval var=$oBox->getContent()}
    </div>
</section>
