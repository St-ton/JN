{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='umfrage' section='umfrage'}</h1>

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{if $oUmfrage->getQuestionCount() > 0}
    <form method="post" action="{if empty($oUmfrage->getURL())}{get_static_route id='umfrage.php'}{else}{$ShopURL}/{$oUmfrage->getURL()}{/if}" class="evo-validate">
        {$jtl_token}
        <input name="u" type="hidden" value="{$oUmfrage->getID()}" />
        {foreach $oUmfrage->getQuestions() as $question}
            {assign var=questionID value=$question->getID()}
            <input name="kUmfrageFrage[]" type="hidden" value="{$questionID}">
            <div {if $question->isRequired()}class="required"{/if}>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$question->getName()} {if $question->isRequired()} *{/if}, Typ: {$question->getType()}</h3>
                    </div>
                    <div class="panel-body form-group">
                        {if !empty($question->getDescription())}
                            <p>{$question->getDescription()}</p>
                            <hr>
                        {/if}

                        {if $question->getType() === 'select_single'}
                            <select name="{$questionID}[]" class="form-control"{if $question->isRequired()} required{/if}>
                                <option value="">{lang key='pleaseChoose'}</option>
                        {elseif $question->getType() === 'select_multi'}
                            <select name="{$questionID}[]" multiple="multiple" class="form-control"{if $question->isRequired()} required{/if}>
                        {elseif $question->getType() === 'text_klein'}
                            <input name="{$questionID}[]"
                                   type="text"
                                   value="{if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[0])}{$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[0]}{/if}"
                                   class="form-control"{if $question->isRequired()} required{/if}>
                        {elseif $question->getType() === 'text_gross'}
                            <textarea name="{$questionID}[]" rows="7" cols="60" class="form-control"{if $question->isRequired()} required{/if}>{if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[0])}{$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[0]}{/if}</textarea>
                        {elseif $question->getType() === 'matrix_single'}
                            <table class="table table-bordered">
                            <tr>
                                <td>&nbsp;</td>
                                {foreach $question->getMatrixOptions() as $matrixOption}
                                    <td>{$matrixOption->getName()}</td>
                                {/foreach}
                            </tr>
                        {elseif $question->getType() === 'matrix_multi'}
                            <table class="table table-bordered">
                            <tr>
                                <td>&nbsp;</td>
                                {foreach $question->getMatrixOptions() as $matrixOption}
                                    <td>{$matrixOption->getName()}</td>
                                {/foreach}
                            </tr>
                        {/if}

                        {foreach name=umfragefrageantwort from=$question->getAnswerOptions() item=answer}
                            {math equation='x-y' x=$smarty.foreach.umfragefrageantwort.iteration y=1 assign='i'}

                            {if $question->getType() === 'multiple_single'}
                                <label>
                                    <input name="{$questionID}[]"
                                           type="radio"
                                           value="{$answer->getID()}" {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name="cumfragefrageantwort" from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} checked="checked"{/if}{/foreach}{/if} {if $question->isRequired()} required{/if}/>
                                    {$answer->getName()}
                                </label>
                            {/if}

                            {if $question->getType() === 'multiple_multi'}
                                <div class="checkbox">
                                    <label>
                                        <input name="{$questionID}[]"
                                               type="checkbox"
                                               value="{$answer->getID()}"
                                               {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} checked="checked"{/if}{/foreach}{/if}/> {$answer->getName()}
                                    </label>
                                </div>
                            {/if}

                            {if $question->getType() === 'select_single'}
                                <option value="{$answer->getID()}"
                                    {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} selected{/if}{/foreach}{/if} > {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === 'select_multi'}
                                <option value="{$answer->getID()}"
                                    {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} selected{/if}{/foreach}{/if} > {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === 'matrix_single'}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach name=umfragematrixoption from=$question->getMatrixOptions() item=oUmfrageMatrixOption}
                                        {math equation='x-y' x=$smarty.foreach.umfragefrageantwort.iteration y=1 assign='i'}
                                        <td>
                                            <div class="radio">
                                                <label><input name="{$questionID}_{$answer->getID()}"
                                                              type="radio"
                                                              value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                              {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=oUmfrageFrageAntwortTMP}{if $answerTMP->kUmfrageFrageAntwort == $answer->getID() && $oUmfrageMatrixOption->getID() == $answerTMP->kUmfrageMatrixOption} checked{/if}{/foreach}{/if} {if $question->isRequired()} required{/if}/>
                                                </label>
                                            </div>
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                            {if $question->getType() === 'matrix_multi'}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach name=umfragematrixoption from=$question->getMatrixOptions() item=oUmfrageMatrixOption}
                                        {math equation='x-y' x=$smarty.foreach.umfragefrageantwort.iteration y=1 assign='i'}
                                        <td>
                                            <input name="{$questionID}[]"
                                                   type="checkbox"
                                                   value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                   {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=oUmfrageFrageAntwortTMP}{if $answerTMP->kUmfrageFrageAntwort == $answer->getID() && $oUmfrageMatrixOption->getID() == $answerTMP->kUmfrageMatrixOption} checked{/if}{/foreach}{/if}/>
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                        {/foreach}
                        {if $question->getType() === 'select_single'}
                            </select>
                        {elseif $question->getType() === 'select_multi'}
                            </select>
                        {elseif $question->getType() === 'matrix_single'}
                             </table>
                        {elseif $question->getType() === 'matrix_multi'}
                             </table>
                        {/if}

                        {if $question->hasFreeField()}
                            {if $question->getType() === 'multiple_single'}
                                <div class="radio">
                                    <label><input name="{$questionID}[]" type="radio" value="-1"
                                                  {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort === '-1'} checked{/if}{/foreach}{/if} {if $question->isRequired()} required{/if}/>
                                        <input
                                            name="{$questionID}[]"
                                            type="text" class="form-control"
                                            value="{if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1])}{$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1]}{/if}"/>
                                    </label>
                                </div>
                            {elseif $question->getType() === 'multiple_multi'}
                                <input name="{$questionID}[]"
                                       type="checkbox"
                                       value="-1"
                                       {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort === '-1'} checked{/if}{/foreach}{/if}/>
                                <input name="{$questionID}[]" type="text" class="form-control" value="{if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1])}{$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1]}{/if}" />
                            {else}
                                <input name="{$questionID}[]"
                                       type="text" class="form-control"
                                       value="{if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1])}{$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr[1]}{/if}"
                                       {if !empty($nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr)}{foreach name=cumfragefrageantwort from=$nSessionFragenWerte_arr[$questionID]->cUmfrageFrageAntwort_arr item=cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort === '-1'} checked{/if}{/foreach}{/if} />
                            {/if}
                        {/if}
                        </div>{* /panel-body *}
                    </div>{* /panel *}
                </div>{* /well *}
            {/foreach}
        <div class="row">
            <div class="col-xs-4">
                {if $nAktuelleSeite <= $nAnzahlSeiten && $nAktuelleSeite != 1}
                    <button class="btn btn-default pull-left" name="back" type="submit" value="back">
                        <span>&laquo; {lang key='umfrageBack' section='umfrage'}</span>
                    </button>
                {/if}
            </div>
            <div class="col-xs-4 text-center">
                <b>{lang key='umfrageQPage' section='umfrage'} {$nAktuelleSeite}</b> {lang key='from' section='product rating'} {$nAnzahlSeiten}
            </div>
            <div class="col-xs-4">
                {if $nAktuelleSeite > 0 && $nAktuelleSeite < $nAnzahlSeiten}
                    <button class="btn btn-default pull-right" name="next" type="submit" value="next">
                        <span>{lang key='umfrageNext' section='umfrage'}</span>
                    </button>
                {/if}
            </div>
        </div>

        <input name="s" type="hidden" value="{$nAktuelleSeite}" />
        {if $nAktuelleSeite == $nAnzahlSeiten}
            <input name="end" type="submit" value="{lang key='umfrageSubmit' section='umfrage'}" class="btn btn-primary submit top17" />
        {/if}
    </form>
{/if}