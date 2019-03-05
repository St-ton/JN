{**
     * @copyright (c) JTL-Software-GmbH
     * @license https://jtl-url.de/jtlshoplicense
     *}
{$tabanzeige = $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
{$showProductWeight = false}
{$showShippingWeight = false}
{if isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0
&& $Einstellungen.artikeldetails.artikeldetails_artikelgewicht_anzeigen === 'Y'}
    {$showProductWeight = true}
{/if}
{if isset($Artikel->cGewicht) && $Artikel->fGewicht > 0
&& $Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y'}
    {$showShippingWeight = true}
{/if}
{$dimension = $Artikel->getDimension()}
{$funcAttr = $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]|default:0}
{$showAttributesTable = ($Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'
&& !empty($Artikel->oMerkmale_arr) || $showProductWeight || $showShippingWeight
|| $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'
&& (!empty($dimension['length']) || !empty($dimension['width']) || !empty($dimension['height']))
|| isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0
&& $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
|| ($Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $funcAttr == 1)
&& !empty($Artikel->Attribute))}
{$useDescriptionWithMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA'
&& $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung')
&& !empty($Artikel->cMedienTyp_arr))}
{$useDescription = (($Artikel->cBeschreibung|strlen > 0) || $useDescriptionWithMediaGroup || $showAttributesTable)}
{$useDownloads = (isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0)}
{$useVotes = $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
{$useQuestionOnItem = $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
{$usePriceFlow = ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf)}
{$useAvailabilityNotification = ($verfuegbarkeitsBenachrichtigung !== 0)}
{$useMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM'
&& $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab')
&& !empty($Artikel->cMedienTyp_arr))}
{$useTags = ($Einstellungen.artikeldetails.tagging_anzeigen === 'Y' && (count($ProduktTagging) > 0
|| $Einstellungen.artikeldetails.tagging_freischaltung !== 'N'))}
{$hasVotesHash = isset($smarty.get.ratings_nPage)
|| isset($smarty.get.bewertung_anzeigen)
|| isset($smarty.get.ratings_nItemsPerPage)
|| isset($smarty.get.ratings_nSortByDir)
|| isset($smarty.get.btgsterne)}
{section name=iterator start=1 loop=10}
    {$tab = tab}
    {$tabname = $tab|cat:$smarty.section.iterator.index|cat:" name"}
    {$tabinhalt = $tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
    {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname]
    && $Artikel->AttributeAssoc[$tabinhalt]}
        {$separatedTabs[{$tabname|replace:' ':'-'}] = [
        'id'      => {$tabname|replace:' ':'-'},
        'name'   => {$Artikel->AttributeAssoc[$tabname]},
        'content' => {$Artikel->AttributeAssoc[$tabinhalt]}
        ]}
    {/if}
{/section}
{$setActiveClass = [
'description'    => (!$hasVotesHash),
'downloads'      => (!$hasVotesHash && !$useDescription),
'separatedTabs'  => (!$hasVotesHash && !$useDescription && !$useDownloads),
'votes'          => ($hasVotesHash || !$useDescription && !$useDownloads && empty($separatedTabs)),
'questionOnItem' => (!$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs) && !$useVotes),
'priceFlow'      => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem),
'availabilityNotification' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads
&& empty($separatedTabs) && !$useQuestionOnItem && !$usePriceFlow),
'mediaGroup' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification),
'tags' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification && !$useMediaGroup)
]}

{if useDescription || $useDownloads || $useDescriptionWithMediaGroup || $useVotes || $useQuestionOnItem || $usePriceFlow
|| $useAvailabilityNotification || $useMediaGroup || $useTags || !empty($separatedTabs)}
    {if $tabanzeige}
        {tabs id="product-tabs" swipeable=true}
        {if $useDescription}
            {tab title="{lang key="description" section="productDetails"}" active=$setActiveClass.description id="tb-dsc" class="nav-item" swipeable=true}
                <div id="tab-description">
                    {block name='tab-description'}
                        {block name='tab-description-content'}
                            <div class="desc">
                                {$Artikel->cBeschreibung}
                                {if $useDescriptionWithMediaGroup}
                                    {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                                        <div class="media mt-3">
                                            {include file='productdetails/mediafile.tpl'}
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                        {/block}
                        {block name='tab-description-attributes'}
                            {include file='productdetails/attributes.tpl' tplscope='details'
                            showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                            dimension=$dimension showAttributesTable=$showAttributesTable}
                        {/block}
                    {/block}
                </div>
            {/tab}
        {/if}

        {if $useDownloads}
            {tab title="{lang section="productDownloads" key="downloadSection"}" active=$setActiveClass.downloads id="tb-dwnld" class="nav-item" swipeable=true}
                <div id="tab-downloads">
                    {include file='productdetails/download.tpl'}
                </div>
            {/tab}
        {/if}

        {if !empty($separatedTabs)}
            {foreach $separatedTabs as $separatedTab}
                {tab title=$separatedTab.name active=$setActiveClass.separatedTabs && $separatedTab@first id="tb-sprtd-{{$separatedTab.id}}" class="nav-item" swipeable=true}
                    <div id="tab-{$separatedTab.id}">
                        {$separatedTab.content}
                    </div>
                {/tab}
            {/foreach}
        {/if}

        {if $useVotes}
            {tab title="{lang key='Votes'}" active=$setActiveClass.votes id="tb-vts" class="nav-item" swipeable=true}
                <div id="tab-votes">
                    {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                </div>
            {/tab}
        {/if}

        {if $useQuestionOnItem}
            {tab title="{lang key="productQuestion" section="productDetails"}" active=$setActiveClass.questionOnItem id="tb-qstn" class="nav-item" swipeable=true}
                <div id="tab-questionOnItem">
                    {include file='productdetails/question_on_item.tpl' position="tab"}
                </div>
            {/tab}
        {/if}

        {if $usePriceFlow}
            {tab title="{lang key='priceFlow' section='productDetails'}" active=$setActiveClass.priceFlow id="tb-prcFlw" class="nav-item" swipeable=true}
                <div id="tab-priceFlow">
                    {include file='productdetails/price_history.tpl'}
                </div>
            {/tab}
        {/if}

        {if $useAvailabilityNotification}
            {tab title="{lang key='notifyMeWhenProductAvailableAgain'}" active=$setActiveClass.availabilityNotification id="tb-vlbltNtfctn" class="nav-item" swipeable=true}
                <div id="tab-availabilityNotification">
                    {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                </div>
            {/tab}
        {/if}

        {if $useMediaGroup}
            {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                {tab title=$cMedienTyp active=$setActiveClass.mediaGroup && $cMedienTyp@first id="tb-{$cMedienTypId}" class="nav-item" swipeable=true}
                    <div id="tab-{$cMedienTypId}">
                        {include file='productdetails/mediafile.tpl'}
                    </div>
                {/tab}
            {/foreach}
        {/if}

        {if $useTags}
            {tab title="{lang key='productTags' section='productDetails'}" active=$setActiveClass.tags id="tb-tgs" class="nav-item" swipeable=true}
                <div id="tab-tags">
                    {include file='productdetails/tags.tpl'}
                </div>
            {/tab}
        {/if}
        {/tabs}
    {else}
        <div class="accordion" id="tabAccordion">
            {if $useDescription}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-description-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-description"
                        ]
                        aria=["expanded" => $setActiveClass.description,
                            "controls" => "tab-description"
                        ]
                    }
                        {lang key='description' section='productDetails'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-description" class="mb-5" visible=$setActiveClass.description
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-description-head"]
                    }
                        {cardbody}
                            {block name='card-description'}
                                {block name='card-description-content'}
                                    <div class="desc">
                                        {$Artikel->cBeschreibung}
                                        {if $useDescriptionWithMediaGroup}
                                            {if $Artikel->cBeschreibung|strlen > 0}
                                                <hr>
                                            {/if}
                                            {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                                                <div class="media">
                                                    {include file='productdetails/mediafile.tpl'}
                                                </div>
                                            {/foreach}
                                        {/if}
                                    </div>
                                {/block}
                                {block name='card-description-attributes'}
                                    {if (!empty($Artikel->cBeschreibung) || $useDescriptionWithMediaGroup) && $showAttributesTable}
                                        <hr>
                                    {/if}
                                    {include file='productdetails/attributes.tpl' tplscope='details'
                                    showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                                    dimension=$dimension showAttributesTable=$showAttributesTable}
                                {/block}
                            {/block}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if $useDownloads}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-downloads-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-downloads"
                        ]
                        aria=["expanded" => $setActiveClass.downloads,
                            "controls" => "tab-downloads"
                        ]
                    }
                        {lang section='productDownloads' key='downloadSection'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-downloads" class="mb-5" visible=$setActiveClass.downloads
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-downloads-head"]
                    }
                        {cardbody}
                            {include file='productdetails/download.tpl'}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if !empty($separatedTabs)}
                {foreach $separatedTabs as $separatedTab}
                    {card no-body=true class="mb-3"}
                        {cardheader id="tab-{$separatedTab.id}-head"
                            class="h6 mb-0"
                            data=["toggle" => "collapse",
                                "target"=>"#tab-{$separatedTab.id}"
                            ]
                            aria=["expanded" => $setActiveClass.separatedTabs && $separatedTab@first,
                                "controls" => "tab-{$separatedTab.id}"
                            ]
                        }
                            {$separatedTab.name} <i class="fa fa-chevron-down float-right"></i>
                        {/cardheader}
                        {collapse id="tab-{$separatedTab.id}" class="mb-5" visible=($setActiveClass.separatedTabs && $separatedTab@first)
                            data=["parent"=>"#tabAccordion"]
                            aria=["labelledby"=>"tab-{$separatedTab.id}-head"]
                        }
                            {cardbody}
                                {$separatedTab.content}
                            {/cardbody}
                        {/collapse}
                    {/card}
                {/foreach}
            {/if}

            {if $useVotes}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-votes-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-votes"
                        ]
                        aria=["expanded" => $setActiveClass.votes,
                            "controls" => "tab-votes"
                        ]
                    }
                        {lang key='Votes'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-votes" class="mb-5" visible=$setActiveClass.votes
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-votes-head"]
                    }
                        {cardbody}
                            {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if $useQuestionOnItem}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-question-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-question"
                        ]
                        aria=["expanded" => $setActiveClass.questionOnItem,
                            "controls" => "tab-question"
                        ]
                    }
                        {lang key='productQuestion' section='productDetails'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-question" class="mb-5" visible=$setActiveClass.questionOnItem
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-question-head"]
                    }
                        {cardbody}
                            {include file='productdetails/question_on_item.tpl' position="tab"}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if $usePriceFlow}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-priceFlow-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-priceFlow"
                        ]
                        aria=["expanded" => $setActiveClass.priceFlow,
                            "controls" => "tab-priceFlow"
                        ]
                    }
                        {lang key='priceFlow' section='productDetails'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-priceFlow" class="mb-5" visible=$setActiveClass.priceFlow
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-priceFlow-head"]
                    }
                        {cardbody}
                            {include file='productdetails/price_history.tpl'}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if $useAvailabilityNotification}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-availabilityNotification-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-availabilityNotification"
                        ]
                        aria=["expanded" => $setActiveClass.availabilityNotification,
                            "controls" => "tab-availabilityNotification"
                        ]
                    }
                        {lang key='notifyMeWhenProductAvailableAgain'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-availabilityNotification" class="mb-5" visible=$setActiveClass.availabilityNotification
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-availabilityNotification-head"]
                    }
                        {cardbody}
                            {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}

            {if $useMediaGroup}
                {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                    {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                    {card no-body=true class="mb-3"}
                        {cardheader id="tab-{$cMedienTypId}-head"
                            class="h6 mb-0"
                            data=["toggle" => "collapse",
                                "target"=>"#tab-{$cMedienTypId}"
                            ]
                            aria=["expanded" => $setActiveClass.mediaGroup && $cMedienTyp@first,
                                "controls" => "tab-{$cMedienTypId}"
                            ]
                        }
                            {$cMedienTyp} <i class="fa fa-chevron-down float-right"></i>
                        {/cardheader}
                        {collapse id="tab-{$cMedienTypId}" class="mb-5" visible=($setActiveClass.mediaGroup && $cMedienTyp@first)
                            data=["parent"=>"#tabAccordion"]
                            aria=["labelledby"=>"tab-{$cMedienTypId}-head"]
                        }
                            {cardbody}
                                {include file='productdetails/mediafile.tpl'}
                            {/cardbody}
                        {/collapse}
                    {/card}
                {/foreach}
            {/if}

            {if $useTags}
                {card no-body=true class="mb-3"}
                    {cardheader id="tab-tags-head"
                        class="h6 mb-0"
                        data=["toggle" => "collapse",
                            "target"=>"#tab-tags"
                        ]
                        aria=["expanded" => $setActiveClass.tags,
                            "controls" => "tab-tags"
                        ]
                    }
                        {lang key='productTags' section='productDetails'} <i class="fa fa-chevron-down float-right"></i>
                    {/cardheader}
                    {collapse id="tab-tags" class="mb-5" visible=$setActiveClass.tags
                        data=["parent"=>"#tabAccordion"]
                        aria=["labelledby"=>"tab-tags-head"]
                    }
                        {cardbody}
                            {include file='productdetails/tags.tpl'}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}
        </div>
    {/if}
{/if}
