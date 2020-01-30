class Tutorial
{
    constructor(gui, iframe, opc)
    {
        bindProtoOnHandlers(this);

        this.gui      = gui;
        this.iframe   = iframe;
        this.opc      = opc;
        this.tourId   = null;
        this.stepId   = null;
        this.handlers = [];
    }

    init()
    {
        installGuiElements(this, [
            'opcSidebar',
            'iframePanel',
            'previewPanel',
            'portlets',
            'tourModal',
            'tourForm',
            'tutorials',
            'tutBackdrop',
            'tutBackdrop2',
            'tutBox',
            'tutboxTitle',
            'tutboxContent',
            'tutboxNext',
        ]);
    }

    start()
    {
        this.tourModal.modal('show');
    }

    modalStartTour()
    {
        event.preventDefault();
        this.startTour(this.tourForm.find('input[name="help-tour"]:checked').val());
    }

    startTour(tourId)
    {
        this.tourId = parseInt(tourId);
        this.tourModal.modal('hide');
        this.tutorials.addClass('active');
        this.startStep(0);
    }

    reset()
    {
        this.tutBox.removeClass('centered-c');
        this.tutBox.removeClass('centered-v');
        this.tutBox.removeClass('centered-h');
        this.tutBox.css('left', '');
        this.tutBox.css('top', '');
        this.tutboxNext.prop('disabled', false);
        this.tutBackdrop.css('width', '');
        this.tutBackdrop2.remove();
        this.tutBackdrop2.removeClass('active');
        this.tutBackdrop2.css('width', '');
        this.tutBackdrop2.css('height', '');
        this.iframe.jq('.hightlighted-element').removeClass('hightlighted-element');
        $('.hightlighted-element').removeClass('hightlighted-element');
        $('.hightlighted-modal').removeClass('hightlighted-modal');
    }

    startStep(stepId)
    {
        let title   = opc.messages["tutStepTitle_" + this.tourId + "_" + stepId];
        let content = opc.messages["tutStepText_" + this.tourId + "_" + stepId];

        this.stepId = stepId;
        this.reset();
        this.tutboxTitle.html(title);
        this.tutboxContent.html(content);

        switch(this.tourId) {
            case 0:
                switch (stepId) {
                    case 0: {
                        this.makeTutbox({cls: 'c'});
                        break;}
                    case 1: {
                        this.makeTutbox({cls: 'v', left: 32});
                        this.highlightElms(this.opcSidebar);
                        break;}
                    case 2: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.highlightElms(this.iframePanel, this.previewPanel);
                        break;}
                    case 3: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() - 32});
                        this.highlightElms(this.portlets);
                        break;}
                    case 4: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.highlightElms(this.iframe.dropTargets());
                        break;}
                    case 5: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.highlightElms($('[data-portlet-class="Heading"]'), this.iframe.dropTargets());
                        this.bindResetEvent(opc.page.rootAreas, 'drop');
                        this.bindNextEvent($('#configModal'), 'shown.bs.modal');
                        break;}
                    case 6: {
                        this.makeTutbox({cls: 'c'});
                        this.highlightModal($('#configModal'));
                        break;}
                    case 7: {
                        let modal = $('#configModal');
                        this.makeTutbox({cls: 'c'});
                        this.makeBackdrop('modal', modal);
                        this.highlightModal(modal);
                        this.highlightElms($('#config-text'), $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 8: {
                        this.makeTutbox({cls: 'c', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.highlightElms(this.iframe.portletToolbar);
                        break;}
                    case 9:{
                        let modal = $('#publishModal');
                        let btn   = $('#btnPublishDraft');
                        this.makeTutbox({left: this.opcSidebar.width() + 64, top: btn.offset().top - 150});
                        this.highlightElms(btn);
                        this.bindResetEvent(modal, 'show.bs.modal');
                        this.bindNextEvent(modal, 'shown.bs.modal');
                        break;}
                    case 10:{
                        let modal  = $('#publishModal');
                        let dialog = modal.find('.modal-dialog');
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.highlightModal(modal);
                        break;}
                    case 11:{
                        let modal  = $('#publishModal');
                        let dialog = modal.find('.modal-dialog');
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightModal(modal);
                        this.highlightElms($('#btnApplyPublish'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    default:
                        this.makeTutbox({cls: 'c'});
                        break;
                }
                break;
            case 1:
                switch (stepId) {
                    case 0: {
                        this.makeTutbox({cls: 'c'});
                        break;}
                    case 1: {
                        let buttonPortlet = $('[data-portlet-class="Button"]');
                        this.makeTutbox({left: 32, top: this.elmBottom(buttonPortlet, 32)});
                        this.makeBackdrop('iframe');
                        this.highlightElms(buttonPortlet, this.iframe.dropTargets());
                        this.bindResetEvent(opc.page.rootAreas, 'drop');
                        this.bindNextEvent($('#configModal'), 'shown.bs.modal');
                        break;}
                    case 2: {
                        let modal  = $('#configModal');
                        let dialog = modal.find('.modal-dialog');
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.bindNextEvent($('[href="#conftab3"]'), 'shown.bs.tab');
                        break;}
                    case 3: {
                        let modal  = $('#configModal');
                        let formgr = $('#config-animation-style').closest('.form-group');
                        this.makeTutbox({left: formgr.offset().left, top: this.elmBottom(formgr, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms(formgr, $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 4: {
                        this.makeTutbox({cls:'c'});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm);
                        break;}
                    case 5: {
                        let modal    = $('#configModal');
                        this.makeTutbox({cls:'c'});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm, this.iframe.portletToolbar);
                        this.bindResetEvent(modal, 'show.bs.modal');
                        this.bindNextEvent(modal, 'shown.bs.modal');
                        break;}
                    case 6: {
                        let styleTab = $('[href="#conftab2"]');
                        let animTab  = $('[href="#conftab3"]');
                        let modal    = $('#configModal');
                        styleTab.click();
                        styleTab.one('shown.bs.tab', () => {
                            let marginInp = $('#margin-bottom-input');
                            this.makeBackdrop('modal', modal);
                            this.makeTutbox({cls:'h', top: this.elmBottom(marginInp, 32)});
                            this.highlightElms(marginInp, animTab.closest('.nav-item'));
                            this.bindNextEvent(animTab, 'shown.bs.tab');
                        });
                        break;}
                    case 7: {
                        let modal   = $('#configModal');
                        let formgr  = $('#config-animation-style').closest('.form-group');
                        let formgr2 = $('#config-wow-offset').closest('.form-group');
                        this.makeTutbox({cls:'c'});
                        this.makeTutbox({left: formgr2.offset().left, top: this.elmBottom(formgr2, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms(formgr, formgr2, $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 8: case 9: case 10: {
                        let tb = this.iframe.portletToolbar;
                        this.makeTutbox({left: 32, top: 128});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm, tb);
                        this.bindNextEvent(this.iframe.jq('#btnClone'), 'click');
                        break;}
                    case 11: {
                        let toggle = $('#previewToolbar').find('.toggle-switch');
                        this.makeTutbox({left: 32, top: toggle.offset().top - 200});
                        this.highlightElms(toggle);
                        this.bindNextEvent(toggle.find('.toggle-slider'), 'click');
                        break;}
                    case 12: {
                        let toggle = $('#previewToolbar').find('.toggle-switch');
                        this.makeTutbox({left: 32, top: toggle.offset().top - 400});
                        this.highlightElms(toggle, $('#previewPanel'));
                        break;}
                    default:
                        this.makeTutbox({cls:'c'});
                        break;
                }
                break;
        }
    }

    elmRight(elm, extraOffset)
    {
        return elm.offset().left + elm.width() + extraOffset;
    }

    elmBottom(elm, extraOffset)
    {
        return elm.offset().top + elm.height() + extraOffset;
    }

    goNextStep()
    {
        let nextStep = this.stepId + 1;

        if(opc.messages["tutStepTitle_" + this.tourId + "_" + nextStep]) {
            this.startStep(nextStep);
        } else {
            this.stopTutorial();
        }
    }

    bindEvent(elm, event, handler)
    {
        elm.one(event + '.tutorial', handler);
        this.handlers.push(elm);
    }

    bindNextEvent(elm, event)
    {
        this.bindEvent(elm, event, () => this.tutboxNext.click());
        this.tutboxNext.prop('disabled', true);
    }

    bindResetEvent(elm, event)
    {
        this.bindEvent(elm, event, () => this.reset());
    }

    stopTutorial()
    {
        this.reset();
        this.tutorials.removeClass('active');
        this.handlers.forEach(h => h.off('.tutorial'));
        this.handlers = [];
    }

    makeTutbox({cls, left, top, disable})
    {
        if(cls)     this.tutBox.addClass('centered-' + cls);
        if(left)    this.tutBox.offset({left: left});
        if(top)     this.tutBox.offset({top: top});
        if(disable) this.tutboxNext.prop('disabled', true);
    }

    makeBackdrop(type, modal)
    {
        switch(type) {
            case 'iframe':
                this.tutBackdrop.width(this.opcSidebar.width());
                this.tutBackdrop2.appendTo(this.iframe.body);
                this.tutBackdrop2.addClass('active');
                break;
            case 'modal':
                this.tutBackdrop2.appendTo(modal.find('.modal-content'));
                this.tutBackdrop2.addClass('active');
                break;
        }
    }

    highlightElms(...elms)
    {
        elms.forEach(elm => elm.addClass('hightlighted-element'));
    }

    highlightModal(modal)
    {
        modal.addClass('hightlighted-modal');
    }

    _startTour_ht3()
    {
        let tour3 = new Tour({
            name: "tBlueprint",
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                "<button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    title: "Blueprints",
                    content: "Lerne hier wie du Templates anlegst und wiederverwendest.<br/>" +
                             "Ziehe ein Spaltenportlet in die Seite und fülle die Spalten mit beliebigen Inhalt.<br/>" +
                             "Drücke dann auf 'Next' um fortzufahren."
                },
                {
                    onShown: tour => {
                        this.gui.blueprintModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                    title: "Blueprints",
                    content: "Selektiere das umschließende Spaltenportlet und klicke auf den Stern in der Toolbar um deine Auswahl als Vorlage zu speichern."
                },
                {
                    element: this.gui.blueprintForm,
                    backdrop: true,
                    onShown: tour => {
                        this.gui.blueprintForm.off('submit.tour').on('submit.tour', () => {
                            tour.next();
                        });
                    },
                    title: "Blueprints",
                    content: "<p>Trage hier einen aussagekräftigen Namen ein. Anhand dieses Bezeichners solltest du in Zukunft den Inhalt erkennen können.</p>" +
                             "<p>Bsp.:<br/>'Produkttabelle', 'Video und Beschreibungstext' oder '3-spaltiger Text'</p>"
                },
                {
                    element: '#composer-tabs a[href="#blueprints"]',
                    backdrop: true,
                    reflex: true,
                    title: "Blueprints",
                    content: "Alle vorhandenen Vorlagen findest du über den entsprechenden Tab in der Sidebar."
                },
                {
                    title: "Blueprints",
                    content: "<p>Du kannst jede Vorlage wie ein normales Portlet einfach in die Seite ziehen.<br/>" +
                             "Das Plugin 'JTL-Portlets' bietet bereits eine kleine Auswahl an Vorlagen, die nach der Plugininstallation hier sichtbar sind.<p/>" +
                             "<p>Damit hast du das Tutorial 'Vorlagen' abgeschlossen. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>"
                },
            ]
        });
        tour3.init();
        tour3.start(true);
    }

    _startTour_ht4()
    {
        let tour4 = new Tour({
           name: "tSettings",
           debug: true,
           orphan: true,
           template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                 "<div class='popover-content'></div><div class='popover-navigation'>" +
                 "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                 "<button class='btn btn-default' data-role='next'>Next »</button>" +
                 "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                 "</div></div>",
           steps: [
               {
                   title: "Settings",
                   onShown: tour => {
                       this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                           tour.next();
                       });
                   },
                   content: "Du möchtest auf unterschiedlichen Endgeräten das Design abwandeln?<br/>" +
                            "Hier erfährst du wie du über die Portleteinstellungen Einfluss auf das Aussehen nehmen kannst.<br/>" +
                            "Beginne damit ein neues Spaltenportlet in die Seite zu ziehen und die Einstellungen zu öffnen."
               },
               {
                   onShown: tour => {
                       $('#Allgemein a[href="#collapseContainerlayout-xs"]').off('click.tour').on('click.tour', () => {
                           tour.next();
                       });
                   },
                   title: "Blueprints",
                   content: "<p>Die Einstellung 'Layout XS' definiert die Aufteilung der Spalten für z.B. Smartphones. " +
                            "Soll dieses Layout für alle Gerätegrößen verwendet werden, so sind keine weiteren Änderungen notwendig. " +
                            "Die definierte Aufteilung wird für alle Größen übernommen sofern keine weiteren Einstellungen vorgenommen werden.</p>" +
                            "<p>Schauen wir uns diese ominösen 'weiteren Einstellungen' einmal an. Klicke dazu auf die Zahnräder an der Einstellung 'Layout XS'. </p>"
               },
               {
                   onShown: tour => {
                       this.gui.configForm.off('submit.tour').on('submit.tour', () => {
                           tour.next();
                       });
                   },
                   title: "Settings",
                   content: "<p>Hier siehst du die Spaltendefinitionen für weitere Größen. Die Einstellungen basieren auf dem Bootstrap-grid. " +
                            "Stell dir einfach vor, dass die Gesamtbreite 12 Spalten entspricht. Diese kannst du frei einteilen:</p>" +
                            "<p>Bsp.:<br/>" +
                            "'4+4+4', erzeugt drei gleichbreite Spalten<br/>" +
                            "8+4, erzeugt eine Breite und eine schmale Spalte</p>" +
                            "<p>Gib nun für das Smartphone den Wert '6+6+12+6+6' ein und speichere die Einstellungen.</p>"
               },
               {
                   title: "Settings",
                   onShown: tour => {
                       this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                           tour.next();
                       });
                   },
                   content: "<p>Du wunderst dich vielleicht warum wir soviele Werte eingetragen haben.<br/>" +
                            "Immer wenn wir in der Summe auf oder über 12 kommen wird eine neue Zeile erstellt. Du solltest fünf Bereiche innerhalb des Portlets sehen." +
                            "Wir haben noch mehr mit diesen Spalten vor. " +
                            "Öffne dazu wieder die Einstellungen.</p>"
               },
               {
                   onShown: tour => {
                       this.gui.configForm.off('submit.tour').on('submit.tour', () => {
                           tour.next();
                       });
                   },
                   title: "Settings",
                   content: "<p>Klappe bitte wieder die Einstellungen für alle Gerätegrößen aus.</p>" +
                            "Auf größeren Displays erscheint uns diese Aufteilung vielleicht eher ungeeignet.<br/>" +
                            "Trage in 'Layout M' bitte '3+3+6+4+8' ein.</p>" +
                            "<p>Wir verschieben also die ersten drei Spalten in eine Zeile und teilen die letzten Beiden etwas anders auf.<br/>" +
                            "Wieder gilt, dass die Einstellung für alle größeren Displays gelten (in dem Fall für 'Layout L'), auch wenn dort nichts definiert wurde.</p>" +
                            "<p>Kleiner Hinweis: Es empfiehlt sich immer die gleiche Anzahl an Spalten in jedes Einstellungsfeld einzutragen.</p>" +
                            "<p>Speichere die Einstellungen.</p>"
               },
               {
                   element: '#displayPreviews',
                   backdrop: true,
                   title: "Settings",
                   content: "<p>Die Spaltenaufteilung passt sich nun den Gerätegrößen an. " +
                            "Du kannst über die Vorschau die unterschiedlichen Displaybreiten simulieren um deine Einstellungen zu überprüfen.</p>" +
                            "<p>Wenn du bereit bist klicke auf 'Next'.</p>",
               },
               {
                   onShown: tour => {
                       this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                           tour.next();
                       });
                   },
                   content: "<p>Ziehe nun eine Überschrift in die Seite und öffne die Einstellungen.</p>",
               },
               {
                   title: "Settings",
                   onShown: tour => {
                       $('#configModalBody .nav-tabs a[href="#Styles"]').off('click.tour').on('click.tour', () => {
                           tour.next();
                       });
                   },
                   content: "<p>Wechsel in den Reiter 'Styles'.</p>",
               },
               {
                   title: "Settings",
                   onShown: tour => {
                       this.gui.configForm.off('submit.tour').on('submit.tour', () => {
                           tour.next();
                       });
                   },
                   content: "<p>Hier hast du die Möglichkeit das gesamte Portlet für bestimmte Displaygrößen auszublenden. " +
                            "Schalte die Sichtbarkeit des Portlets für Tablets aus und speichere.</p>",
               },
               {
                   title: "Settings",
                   content: "<p>Du kannst nun wieder über die Vorschau deine Einstellungen prüfen. </p>" +
                            "<p>Damit hast du das Tutorial 'Einstellungen' abgeschlossen. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>"
               },
           ]
        });
        tour4.init();
        tour4.start(true);
    }
}
