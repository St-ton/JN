{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=#dbcheck# cBeschreibung=#dbcheckDesc# cDokuURL=#dbcheckURL#}
<div id="content" class="container-fluid">
    <div id="pageCheck">
        {if isset($cDBFileStruct_arr) && $cDBFileStruct_arr|@count > 0}
            <div id="contentCheck">
                <div class="alert alert-info"><strong>Anzahl Tabellen:</strong> {$cDBFileStruct_arr|@count}<br /><strong>Anzahl modifizierter Tabellen:</strong> {$cDBError_arr|@count}</div>
                {if $cDBError_arr|@count > 0}
                    <p>
                        <button id="viewAll" name="viewAll" type="button" class="btn btn-primary hide" value="Alle anzeigen"><i class="fa fa-share"></i> Alle anzeigen</button>
                        <button id="viewModified" name="viewModified" type="button" class="btn btn-danger viewModified" value="Modifizierte anzeigen"><i class="fa fa-warning"></i> Modifizierte anzeigen</button>
                    </p>
                    <br />
                {/if}
                <table class="table req">
                    <thead>
                    <tr>
                        <th>Tabelle</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    {foreach name=datei from=$cDBFileStruct_arr key=cTable item=oDatei}
                        <tr class="filestate mod{$smarty.foreach.datei.iteration%2} {if !$cTable|array_key_exists:$cDBError_arr}unmodified{else}modified{/if}">
                            <td>{$cTable}</td>
                            <td>
                            {if $cTable|array_key_exists:$cDBError_arr}
                                <span class="badge red">{$cDBError_arr[$cTable]}</span>
                            {else}
                                <span class="badge green">Ok</span>
                            {/if}
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {else}
            {if isset($cFehler) && $cFehler|count_characters > 0}
                <div class="alert alert-danger">{$cFehler}</div>
            {/if}
        {/if}
    </div>
</div>

<script>
    {literal}
    $(document).ready(function () {
        $('#viewAll').click(function () {
            $('#viewAll').hide();
            $('#viewModified').show().removeClass('hide');
            $('.unmodified').show();
            $('.modified').show();
            colorLines();
        });

        $('#viewModified').click(function () {
            $('#viewAll').show().removeClass('hide');
            $('#viewModified').hide();
            $('.unmodified').hide();
            $('.modified').show();
            colorLines();
        });

        function colorLines() {
            var mod = 1;
            $('.req li:not(:hidden)').each(function () {
                if (mod == 1) {
                    $(this).removeClass('mod0');
                    $(this).removeClass('mod1');
                    $(this).addClass('mod1');
                    mod = 0;
                } else {
                    $(this).removeClass('mod1');
                    $(this).removeClass('mod0');
                    $(this).addClass('mod0');
                    mod = 1;
                }
            });
        }
    });

    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}