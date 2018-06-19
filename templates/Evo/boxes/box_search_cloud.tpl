{if (isset($Boxen) && isset($Boxen.Suchwolke) && $Boxen.Suchwolke->Suchbegriffe|@count > 0) || (isset($oBox->Suchbegriffe) && $oBox->Suchbegriffe|@count > 0)}
    <script>
        $('document').ready(function() {
            var searchItems = {$oBox->Suchbegriffe|json_encode};
            var searchcloudTags = [];
            $.each(searchItems, function(key, value) {
                searchcloudTags.push( { text: value.cSuche, weight: value.nAnzahlGesuche, link: 'index.php?qs=' + value.cSuche } );
            } );
            $("#searchcloud").jQCloud(searchcloudTags, {
                autoResize: true,
                steps: 7
            } );
        } );
    </script>
    <section class="panel panel-default box box-searchcloud" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="searchcloud" section="global"}</h5>
        </div>
        <div class="box-body panel-body">
            <div class="tagbox">
                {if isset($oBox->Suchbegriffe)}{*4.00*}
                    {assign var=from value=$oBox->Suchbegriffe}
                {else}{*3.19*}
                    {assign var=from value=$Boxen.Suchwolke->Suchbegriffe}
                {/if}
                <div id="searchcloud"></div>
            </div>
        </div>
    </section>
{/if}