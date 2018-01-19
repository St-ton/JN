<button type="button"
        class="btn-tooltip btn btn-info btn-heading"
        data-html="true"
        data-toggle="tooltip"
        data-placement="{$placement}"
        title="{if $description !== null}{$description}{/if}{if $cID !== null && $description !== null}<hr>{/if}{if $cID !== null}<p><strong>Einstellungsnr.:</strong>{$cID}</p>{/if}">
<i class="fa fa-question"></i></button>
