<div id="zip-badge-{$surcharge->getID()}">
    {foreach $surcharge->getZIPCodes() as $zipCode}
        <button class="badge btn-primary zip-badge" data-surcharge-id="{$surcharge->getID()}" data-zip="{$zipCode}">
            {$zipCode} &times;
        </button>
    {/foreach}
    {foreach $surcharge->getZIPAreas() as $zipArea}
        <button class="badge btn-primary zip-badge" data-surcharge-id="{$surcharge->getID()}" data-zip="{$zipArea->getZIPFrom()}-{$zipArea->getZIPTo()}">
            {$zipArea->getArea()} &times;
        </button>
    {/foreach}
</div>
