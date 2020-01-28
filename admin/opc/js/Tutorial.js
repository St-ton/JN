class Tutorial
{
    constructor(gui, iframe, opc)
    {
        bindProtoOnHandlers(this);

        this.gui    = gui;
        this.iframe = iframe;
        this.opc    = opc;
        this.tourId = null;
        this.stepId = null;
    }

    init()
    {
        installGuiElements(this, [
            'opcSidebar',
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
        this.tourId = tourId;
        this.tourModal.modal('hide');
        this.tutorials.addClass('active');
        this.startStep(0);
    }

    reset()
    {
        this.tutBox.removeClass('centered');
        this.tutBox.removeClass('centered-v');
        this.tutBox.removeClass('centered-h');
        this.tutBox.css('left', '');
        this.tutBox.css('top', '');
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
        this.tutboxTitle.html(title);
        this.tutboxContent.html(content);
        this.reset();

        switch (stepId) {
            case 0: {
                this.tutBox.addClass('centered');
                break;}
            case 1: {
                this.tutBox.addClass('centered-v');
                this.tutBox.offset({left: 32});
                this.opcSidebar.addClass('hightlighted-element');
                break;}
            case 2: {
                this.tutBox.addClass('centered-v');
                this.tutBox.offset({left: this.opcSidebar.width() + 64});
                this.makeBackdrop('iframepanel');
                break;}
            case 3: {
                this.tutBox.addClass('centered-v');
                this.tutBox.offset({left: this.opcSidebar.width() - 32});
                this.portlets.addClass('hightlighted-element');
                break;}
            case 4: {
                this.tutBox.addClass('centered-v');
                this.tutBox.offset({left: this.opcSidebar.width() + 64});
                this.makeBackdrop('iframe');
                this.iframe.dropTargets().addClass('hightlighted-element');
                break;}
            case 5: {
                this.tutBox.addClass('centered-v');
                this.tutBox.offset({left: this.opcSidebar.width() + 64});
                this.makeBackdrop('iframe');
                $('[data-portlet-class="Heading"]').addClass('hightlighted-element');
                this.iframe.dropTargets().addClass('hightlighted-element');
                opc.once('portlet.dragend', () => this.reset());
                $('#configModal').one('shown.bs.modal', () => this.tutboxNext.click());
                break;}
            case 6: {
                this.tutBox.addClass('centered');
                $('#configModal').addClass('hightlighted-modal');
                break;}
            case 7: {
                let configModal = $('#configModal');
                this.tutBox.addClass('centered');
                this.makeBackdrop('modal', configModal);
                configModal.addClass('hightlighted-modal');
                $('#config-text').addClass('hightlighted-element');
                $('#configSave').addClass('hightlighted-element');
                configModal.one('hide.bs.modal', () => this.reset());
                configModal.one('hidden.bs.modal', () => this.tutboxNext.click());
                break;}
            case 8: {
                this.tutBox.addClass('centered');
                this.tutBox.offset({left: this.opcSidebar.width() + 64});
                this.makeBackdrop('iframe');
                this.iframe.portletToolbar.addClass('hightlighted-element');
                break;}
            case 9:{
                let modal = $('#publishModal');
                let btn = $('#btnPublishDraft');
                this.tutBox.addClass('centered-h');
                btn.addClass('hightlighted-element');
                this.tutBox.offset({top: btn.offset().top - 100});
                modal.one('show.bs.modal', () => this.reset());
                modal.one('shown.bs.modal', () => this.tutboxNext.click());
                break;}
            case 10:{
                let modal  = $('#publishModal');
                let dialog = modal.find('.modal-dialog');
                this.tutBox.addClass('centered-h');
                this.tutBox.offset({top: dialog.offset().top + dialog.height() + 32});
                modal.addClass('hightlighted-modal');
                modal.one('show.bs.modal', () => this.reset());
                modal.one('shown.bs.modal', () => this.tutboxNext.click());
                break;}
            case 11:{
                let modal = $('#publishModal');
                let dialog = modal.find('.modal-dialog');
                this.tutBox.addClass('centered-h');
                this.tutBox.offset({top: dialog.offset().top + dialog.height() + 32});
                this.makeBackdrop('modal', modal);
                modal.addClass('hightlighted-modal');
                $('#btnApplyPublish').addClass('hightlighted-element');
                modal.one('hide.bs.modal', () => this.reset());
                modal.one('hidden.bs.modal', () => this.tutboxNext.click());
                break;}
            default:
                this.tutBox.addClass('centered');
        }

        let nextStep = stepId + 1;

        if(opc.messages["tutStepTitle_" + this.tourId + "_" + nextStep]) {
            this.tutboxNext.off('click').on('click', () => this.startStep(nextStep));
        } else {
            this.tutboxNext.off('click').on('click', () => this.stopTutorial());
        }
    }

    stopTutorial()
    {
        this.reset();
        this.tutorials.removeClass('active');
    }

    makeBackdrop(type, modal)
    {
        switch(type) {
            case 'iframepanel':
                this.tutBackdrop.width(this.opcSidebar.width());
                break;
            case 'iframe':
                this.tutBackdrop.width(this.opcSidebar.width());
                this.tutBackdrop2.appendTo(this.iframe.body);
                this.tutBackdrop2.addClass('active');
                break;
            case 'modal':
                this.tutBackdrop2.appendTo(modal.find('.modal-content'));
                this.tutBackdrop2.addClass('active');
                this.tutBackdrop2.css('width', '100%');
                this.tutBackdrop2.css('height', '100%');
                break;
        }
    }

    fixIframePos(element)
    {
        let off   = element.offset();
        let pLeft = this.gui.opcSidebar.outerWidth();

        element.offset({left: off.left + pLeft});
    }

    fixBackdrop()
    {
        let backdropTop    = $('.tour-backdrop.top');
        let backdropLeft   = $('.tour-backdrop.left');
        let backdropRight  = $('.tour-backdrop.right');
        let backdropBottom = $('.tour-backdrop.bottom');

        let off       = backdropTop.offset();
        let pTop      = this.gui.opcHeader.height();
        let pLeft     = this.gui.opcSidebar.outerWidth();
        let leftWidth = backdropLeft.width();

        // backdropTop.offset({top: off.top + pTop});

        off = backdropLeft.offset();
        // backdropLeft.offset({top: off.top + pTop});
        backdropLeft.width(leftWidth + pLeft);

        off = backdropRight.offset();
        // backdropRight.offset({top: off.top + pTop, left: off.left + pLeft});
        backdropRight.offset({left: off.left + pLeft});

        // off = backdropBottom.offset();
        // backdropBottom.offset({top: off.top + pTop});
    }

    _startTour_ht1()
    {
        let confModal = this.gui.configModal;

        let tour = new Tour({
            name: "tAllgemein",
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button>" +
                "<span data-role='separator'>|</span><button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    backdrop: true,
                    title: "Willkommen",
                    content: "In dieser kurzen Einführung wollen wir dir einen Überblick über dieses neue Feature geben."
                },
                {
                    backdrop: true,
                    element: this.gui.opcSidebar,
                    title: "Aufteilung",
                    content: "Grundsätzlich ist der Editor in die zwei Bereich aufgeteilt.<br>Hier siehst du die Sidebar."
                },
                {
                    backdrop: true,
                    element: this.gui.iframePanel,
                    placement: "top",
                    title: "Aufteilung",
                    content: "In diesem Bereich wird der aktuelle Stand deiner Bearbeitung gezeigt."
                },
                {
                    backdrop: true,
                    element: "#portlets",
                    placement: "right",
                    title: "Portlets",
                    content: "Das sind unserer Portlets. Diese kannst du nutzen um deine Seiten mit Inhalt zu füllen."
                },
                {
                    backdrop: true,
                    element: this.iframe.dropTargets().first(),
                    placement: "top",
                    title: "Portlets",
                    content: "Die grauen Bereiche auf dieser Seite zeigen dir wo du Portlets ablegen kannst.",
                    onShown: tour => {
                        this.fixIframePos($('#step-4'));
                        this.fixBackdrop();
                    },
                },
                {
                    element: "#portlets",
                    placement: "bottom",
                    title: "Portlets",
                    reflex: 'dragend',
                    content: "Ziehe nun das Portlet 'Überschrift' in den obersten grauen Bereich und du hast den " +
                        "ersten Inhalt auf dieser Seite eingefügt."
                },
                {
                    element: this.iframe.portletToolbar,
                    placement: "right",
                    title: "Einstellungen",
                    onShown: tour => {
                        this.fixIframePos($('#step-6'));
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                    content: "An diesem Portlet siehst du eine Leiste mit verschiedenen Icons. Klicke auf den Stift" +
                        " um die Einstellungen zu öffnen."
                },
                {
                    element: "#configModal .btn-primary",
                    placement: "bottom",
                    title: "Einstellungen",
                    reflex: true,
                    content: "Alle Portlets bieten verschiedene Einstellungen. Trage hier einen neuen Text für die " +
                        "Überschrift ein und klicke auf Speichern."
                },
                {
                    element: "#btnSave",
                    placement: "bottom",
                    title: "Seite Speichern",
                    reflex: true,
                    content: "Um deine Änderungen zu Sichern klicke bitte auf das Speichern-Symbol. Beachte bitte, dass " +
                        "man im Shop noch keine Änderungen sieht."
                },
                {
                    element: "#btnPublish",
                    placement: "bottom",
                    title: "Seite Veröffentlichen",
                    reflex: true,
                    content: "Nur veröffentlichte Seiten sind für deine Kunden sichtbar. " +
                             "Um die Änderungen im Shop zu veröffentlichen klicke auf das Zeitungsymbol."
                },
                {
                    element: "#publishModal .btn-primary",
                    placement: "bottom",
                    title: "Veröffentlichen",
                    reflex: true,
                    content: "Für jede Seite kannst du verschiedene Versionen haben. " +
                             "Z.B. eine allgemeine (nur mit 'veröffentlicht ab') und eine Weihnachtsversion " +
                             "(gültig während der Weihnachtszeit). Klicke auf 'veröffentlichen ab' und speichere danach."
                },
                {
                    element: "#btnClose",
                    placement: "bottom",
                    title: "Beenden",
                    reflex: true,
                    content: "Du kannst nun den OPC beenden und deine Seite anschauen oder mit einem Klick auf 'end Tour' diese Hilfe beenden." +
                             "Das waren die Basics. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!"
                },

            ]
        });

        // Initialize the tour
        tour.init();
        $('.tour-tAllgemein-5-element').on('dragend', () => {
            tour.next();
        });
        // Initialize the tour
        tour.start(true);
    }

    _startTour_ht2()
    {
        let confModal = this.gui.configModal;

        let tour2 = new Tour({
            name: "tAnimation",
            smartPlacement: false,
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                "<button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    element: '',
                    backdrop: true,
                    title: "Animationen",
                    content: "Lerne hier wie du Portlets mit einfachen Animationen erstellst. Es empfiehlt sich mit einer leeren Seite zu arbeiten."
                },
                {
                    backdrop: true,
                    title: "Animationen",
                    content: "Nicht jedes Portlet verfügt über die Einstellungen um Animationen zu erstellen."
                },
                {
                    title: "Animationen",
                    content: "Ziehe zunächst einen Button in deine Seite und öffne die Einstellungen.",
                    onShown: tour => {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    element: "#configModalBody .nav-tabs a[href='#Animation']",
                    title: "Animationen",
                    reflex: true,
                    content: "Wechsel nun zu dem Reiter 'Animation'.",
                },
                {
                    title: "Animationen",
                    content: "Die Einstellung 'animation-style' enthält viele verschiedene Typen." +
                             " Bitte wähle einen Animationsstil aus und speichere die Einstellungen.",
                    onShown: tour => {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Hier siehts du deine erste einfache Animation. Wenden wir uns einer etwas umfangreicheren Aufgabe zu. Lösche dazu den erstellten Button.",
                    onShown: tour => {
                        this.iframe.btnTrash.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Erstelle nun ein Spaltenportlet (in der Sidebar unter 'LAYOUT') und öffne die Einstellungen.",
                    onShown: tour => {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Setze das Layout auf die volle Breite (12). Im Tab 'Styles' änderst du 'margin-bottom' auf 50, wählst eine Hintergrundfarbe und wechselst zu Animationen.",
                    onShown: tour => {
                        $('#configModalBody .nav-tabs a[href="#Animation"]').off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Wähle 'fade-in' als Animationsstil und gibt bei 'offset' 150 ein. Nun Kannst du die Einstellungen schließen.",
                    onShown: tour => {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Füge in das Spaltenportlet eine Überschrift ein und öffne auch hier die Einstellungen.",
                    onShown: tour => {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Im Styles-tab gibst du unter 'margin-bottom' 250 ein und speicherst die Einstellungen.",
                    onShown: tour => {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "Kopiere nun das Spaltenportlet ein paar mal.",
                    onShown: tour => {
                        this.iframe.btnClone.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "1x",
                    onShown: tour => {
                        this.iframe.btnClone.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "2x",
                    onShown: tour => {
                        this.iframe.btnClone.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "3x",
                    onShown: tour => {
                        this.iframe.btnClone.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "einmal noch!",
                    onShown: tour => {
                        this.iframe.btnClone.off('click.tour').on('click.tour', () => {
                            tour.next();
                        });
                    },
                },
                {
                    title: "Animationen",
                    content: "<p>Du kannst die Seite jetzt speichern und dir im Frontend anschauen. <br/>" +
                             "Beim Scrollen solltest du nun sehen, dass die verschiedenen Zeilen nacheinander eingeblendet werden. </p><p>" +
                             "Man kann dieses Konzept auch weiter ausbauen und die Bereiche z.B. abwechselnd von rechts und links in die Seite einfahren lassen. " +
                             "Mit einem schönen zweifarbigen Design lassen sich damit effektvolle Seiten kreieren. " +
                             "Bedenke aber bitte, dass 'weniger oft mehr ist'. Soll heißen: geh sparsam mit Animationen um, damit deine Kunden nicht abgelenkt oder gar verschreckt werden.</p>" +
                             "<p>Damit sind wir mit dem Tutorial 'Animationen' durch. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>",
                },
            ]
        });
        tour2.init();
        tour2.start(true);
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
