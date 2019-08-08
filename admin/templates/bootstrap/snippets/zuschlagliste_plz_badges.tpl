<div id="zip-badge-{$surcharge->getID()}">
    {foreach $surcharge->getZIPCodes() as $zipCode}
        <button class="badge btn-primary zip-badge" data-surcharge-id="{$surcharge->getID()}" data-zip="{$zipCode}">
            {$zipCode} <span class="fal fa-times ml-1"></span>
        </button>
    {/foreach}
    {foreach $surcharge->getZIPAreas() as $zipArea}
        <button class="badge btn-primary zip-badge" data-surcharge-id="{$surcharge->getID()}" data-zip="{$zipArea->getZIPFrom()}-{$zipArea->getZIPTo()}">
            {$zipArea->getArea()} <span class="fal fa-times ml-1"></span>
        </button>
    {/foreach}
</div>
