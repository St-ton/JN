{foreach $zuschlaglisten as $zuschlagliste}
    <div class="row">
        {include file='snippets/zuschlagliste.tpl' zuschlagliste=$zuschlagliste}
    </div>
    <hr>
{/foreach}