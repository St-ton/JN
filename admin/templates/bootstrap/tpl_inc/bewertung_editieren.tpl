{include file='tpl_inc/seite_header.tpl' cTitel=__('bearbeiteBewertung')}
<div id="content">
    <div class="card">

            <form name="umfrage" method="post" action="bewertung.php">
                <div class="card-body">
                {$jtl_token}
                <input type="hidden" name="bewertung_editieren" value="1" />
                <input type="hidden" name="tab" value="{$cTab}" />
                {if isset($nFZ) && $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
                <input type="hidden" name="kBewertung" value="{$review->getId()}" />

                <table class="kundenfeld table table-border-light" id="formtable">
                    <tr class="border-top-0">
                        <td><label for="name">{__('customerName')}:</label></td>
                        <td><input class="form-control" id="name" name="cName" type="text" value="{$review->getName()}" /></td>
                    </tr>
                    <tr>
                        <td><label for="title">{__('name')}:</label></td>
                        <td><input class="form-control" id="title" name="cTitel" type="text" value="{$review->getTitle()}" /></td>
                    </tr>
                    <tr>
                        <td><label for="stars">{__('ratingStars')}:</label></td>
                        <td>
                            <select id="stars" name="nSterne" class="custom-select combo">
                                {for $i=1 to 5}
                                    <option value="{$i}"{if $review->getStars() === $i} selected{/if}>{$i}</option>
                                {/for}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="content">{__('ratingText')}:</label></td>
                        <td><textarea id="content" class="ckeditor" name="cText" rows="15" cols="60">{$review->getContent()}</textarea></td>
                    </tr>
                    <tr>
                        <td><label for="answer">{__('ratingReply')}</label></td>
                        <td><textarea id="answer" class="ckeditor" name="cAntwort" rows="15" cols="60">{$review->getAnswer()}</textarea></td>
                    </tr>
                </table>
                </div>
                <div class="save-wrapper card-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="bewertungsubmit" type="submit" value="{__('save')}" class="btn btn-primary btn-block"><i class="fa fa-save"></i> {__('save')}</button>
                        </div>
                    </div>
                </div>
            </form>

    </div>
</div>
