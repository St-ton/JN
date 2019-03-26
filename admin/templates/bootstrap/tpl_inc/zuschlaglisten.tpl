{foreach $zuschlaglisten as $zuschlagliste}
    <div class="surcharge-box" data-surcharge-id="{$zuschlagliste->getID()}">
        {include file='snippets/zuschlagliste.tpl' zuschlagliste=$zuschlagliste}
        <hr>
    </div>
{/foreach}