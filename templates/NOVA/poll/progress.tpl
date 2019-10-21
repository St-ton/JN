{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='poll-progress'}
    {block name='poll-progress-heading'}
        {opcMountPoint id='opc_before_heading'}
        {if !empty($oUmfrage->getName())}
            <h1>{$oUmfrage->getName()}</h1>
        {else}
            <h1>{lang key='umfrage' section='umfrage'}</h1>
        {/if}
    {/block}

    {if $oUmfrage->getQuestionCount() > 0}
        {block name='poll-progress-form'}
            {opcMountPoint id='opc_before_questions'}
            {form method="post" action="{if empty($oUmfrage->getURL())}{get_static_route id='umfrage.php'}{else}{$ShopURL}/{$oUmfrage->getURL()}{/if}" class="evo-validate"}
                {block name='poll-progress-form-content'}
                    {input name="u" type="hidden" value=$oUmfrage->getID()}
                    {block name='poll-progress-questions'}
                        {foreach $oUmfrage->getQuestions() as $question}
                            {assign var=questionID value=$question->getID()}
                            {input name="kUmfrageFrage[]" type="hidden" value=$questionID}
                            {card no-body=true class="border-0 mb-8"}
                                {cardheader}
                                    <div id="poll-question-label-{$questionID}" class="h3">{$question->getName()} {if !$question->isRequired()}<span class="optional"> - {lang key='optional'}</span>{/if}</div>
                                {/cardheader}
                                {cardbody}
                                    {if !empty($question->getDescription())}
                                        <p>{$question->getDescription()}</p>
                                        <hr>
                                    {/if}

                                    {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                                        <select name="sq{$questionID}[]" class="form-control mb-3"{if $question->isRequired()} required{/if} aria-labelledby="poll-question-label-{$questionID}">
                                            <option value="">{lang key='pleaseChoose'}</option>
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                                        <select name="sq{$questionID}[]" multiple="multiple mb-3" class="form-control"{if $question->isRequired()} required{/if} aria-labelledby="poll-question-label-{$questionID}">
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::TEXT_SMALL}
                                        {input name="sq{$questionID}[]"
                                            type="text"
                                            value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}{/if}"
                                            required=$question->isRequired()
                                            aria=["labelledby"=>"poll-question-label-{$questionID}"]
                                        }
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::TEXT_BIG}
                                        {strip}
                                            {textarea name="sq{$questionID}[]"
                                                rows="7"
                                                cols="60"
                                                required=$question->isRequired()
                                                aria=["labelledby"=>"poll-question-label-{$questionID}"]
                                            }
                                                {if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}
                                                    {$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}
                                                {/if}
                                            {/textarea}
                                        {/strip}
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                                        <table class="table table-striped">
                                        <thead>
                                            <td>&nbsp;</td>
                                            {foreach $question->getMatrixOptions() as $matrixOption}
                                                <td>{$matrixOption->getName()}</td>
                                            {/foreach}
                                        </thead>
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
                                        <table class="table table-striped">
                                        <tr>
                                            <td>&nbsp;</td>
                                            {foreach $question->getMatrixOptions() as $matrixOption}
                                                <td>{$matrixOption->getName()}</td>
                                            {/foreach}
                                        </tr>
                                    {/if}
                                    {block name='poll-progress-form-answers'}
                                        {foreach $question->getAnswerOptions() as $answer}
                                            {math equation='x-y' x=$answer@iteration y=1 assign='i'}

                                            {if $question->getType() === \JTL\Survey\QuestionType::MULTI_SINGLE}
                                                {radio name="sq{$questionID}[]"
                                                    value=$answer->getID()
                                                    checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID()))
                                                    aria=["label"=>$answer->getName()]
                                                }
                                                    {$answer->getName()}
                                                {/radio}
                                            {/if}

                                            {if $question->getType() === \JTL\Survey\QuestionType::MULTI}
                                                {checkbox name="sq{$questionID}[]"
                                                    value=$answer->getID()
                                                    checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID()))
                                                    aria=["label"=>$answer->getName()]
                                                }
                                                    {$answer->getName()}
                                                {/checkbox}
                                            {/if}

                                            {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                                                <option value={$answer->getID()}
                                                    {if $nSessionFragenWerte_arr[$questionID]->isActive($answer->getID())} selected{/if}>
                                                    {$answer->getName()}
                                                </option>
                                            {/if}

                                            {if $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                                                <option value={$answer->getID()}
                                                    {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} selected{/if}{/foreach}{/if}>
                                                    {$answer->getName()}
                                                </option>
                                            {/if}

                                            {if $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                                                <tr>
                                                    <td>{$answer->getName()}</td>
                                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                                        <td>
                                                            {radio name="sq{$questionID}_{$answer->getID()}"
                                                                value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                                checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID(), $oUmfrageMatrixOption->getID()))
                                                                aria=["label"=>$answer->getName()]
                                                                required=$question->isRequired()
                                                            }
                                                            {/radio}
                                                        </td>
                                                    {/foreach}
                                                </tr>
                                            {/if}

                                            {if $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
                                                <tr>
                                                    <td>{$answer->getName()}</td>
                                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                                        <td>
                                                            {checkbox name="sq{$questionID}[]"
                                                                value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                                checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $answerTMP}{if $answerTMP->kUmfrageFrageAntwort == $answer->getID() && $oUmfrageMatrixOption->getID() == $answerTMP->kUmfrageMatrixOption} true{/if}{/foreach}{/if}"
                                                                aria=["label"=>$answer->getName()]
                                                            }
                                                            {/checkbox}
                                                        </td>
                                                    {/foreach}
                                                </tr>
                                            {/if}
                                        {/foreach}
                                    {/block}
                                    {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                                        </select>
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                                        </select>
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                                         </table>
                                    {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
                                         </table>
                                    {/if}

                                    {if $question->hasFreeField()}
                                        {if $question->getType() === \JTL\Survey\QuestionType::MULTI_SINGLE}
                                            <div class="radio">
                                                <label>
                                                    {input
                                                        name="sq{$questionID}[]"
                                                        type="radio"
                                                        value="-1"
                                                        checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1}true{/if}{/foreach}{/if}"
                                                        required=$question->isRequired()
                                                    }
                                                    {input
                                                        name="sq{$questionID}[]"
                                                        type="text"
                                                        value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"
                                                    }
                                                </label>
                                            </div>
                                        {elseif $question->getType() === \JTL\Survey\QuestionType::MULTI}
                                            {checkbox name="sq{$questionID}[]"
                                                   size="sm"
                                                   value="-1"
                                                   checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} true{/if}{/foreach}{/if}"}
                                            {input name="sq{$questionID}[]" type="text" value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"}
                                            {/checkbox}
                                        {else}
                                            {input name="sq{$questionID}[]"
                                                   type="text"
                                                   value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"
                                                   checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} true{/if}{/foreach}{/if}"}
                                        {/if}
                                    {/if}
                                {/cardbody}
                            {/card}
                        {/foreach}
                    {/block}
                    {block name='poll-progress-form-pagination'}
                        {row}
                            {col cols=4}
                                {if $nAktuelleSeite <= $nAnzahlSeiten && $nAktuelleSeite != 1}
                                    {button variant="outline-primary" class="float-left" name="back" type="submit" value="back" formnovalidate=true}
                                        <span>&laquo; {lang key='umfrageBack' section='umfrage'}</span>
                                    {/button}
                                {/if}
                            {/col}
                            {col cols=4 class="text-center"}
                                <b>{lang key='umfrageQPage' section='umfrage'} {$nAktuelleSeite}</b>
                                {lang key='from' section='product rating'}
                                {$nAnzahlSeiten}
                            {/col}
                            {col cols=4}
                                {if $nAktuelleSeite > 0 && $nAktuelleSeite < $nAnzahlSeiten}
                                    {button variant="outline-primary" type="submit" class="float-right" name="next" value="next"}
                                        <span>{lang key='umfrageNext' section='umfrage'}</span>
                                    {/button}
                                {/if}
                            {/col}
                        {/row}
                    {/block}
                    {block name='poll-progress-fomr-submit'}
                        {input name="s" type="hidden" value=$nAktuelleSeite}
                        {if $nAktuelleSeite == $nAnzahlSeiten}
                            {opcMountPoint id='opc_before_submit'}
                            {button type="submit" name="end" value="1" variant="primary" class="mt-3"}
                                {lang key='umfrageSubmit' section='umfrage'}
                            {/button}
                        {/if}
                    {/block}
                {/block}
            {/form}
        {/block}
    {/if}
{/block}
