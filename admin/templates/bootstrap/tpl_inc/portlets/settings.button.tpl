
<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation" class=""><a aria-controls="icon" data-toggle="tab" id="icon-tab" role="tab" href="#icon" aria-expanded="false">Icon</a></li>
    <li role="presentation" class=""><a aria-controls="url-link" data-toggle="tab" id="url-link-tab" role="tab" href="#url-link" aria-expanded="false">Url (link)</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="form-group">
            <label for="button-text">Text</label>
            <input type="text" id="button-text" name="button-text" class="form-control" placeholder="Button text" value="{$properties['button-text']}">
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-type">Type</label>
                    <select class="form-control" id="button-type" name="button-type">
                        <option value="default"{if $properties['button-type'] === 'default'} selected{/if}>Default</option>
                        <option value="primary"{if $properties['button-type'] === 'primary'} selected{/if}>Primary</option>
                        <option value="success"{if $properties['button-type'] === 'success'} selected{/if}>Success</option>
                        <option value="info"{if $properties['button-type'] === 'info'} selected{/if}>Info</option>
                        <option value="warning"{if $properties['button-type'] === 'warning'} selected{/if}>Warning</option>
                        <option value="danger"{if $properties['button-type'] === 'danger'} selected{/if}>Danger</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-size">Size</label>
                    <select class="form-control" id="button-size" name="button-size">
                        <option value="xs"{if $properties['button-size'] === 'xs'} selected{/if}>Mini</option>
                        <option value="sm"{if $properties['button-size'] === 'sm'} selected{/if}>Small</option>
                        <option value="md"{if $properties['button-size'] === 'md'} selected{/if}>Normal</option>
                        <option value="lg"{if $properties['button-size'] === 'lg'} selected{/if}>Large</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-alignment">Alignment</label>
                    <select class="form-control" id="button-alignment" name="button-alignment">
                        <option value="inline"{if $properties['button-alignment'] === 'inline'} selected{/if}>Inline</option>
                        <option value="left"{if $properties['button-alignment'] === 'left'} selected{/if}>Left</option>
                        <option value="right"{if $properties['button-alignment'] === 'right'} selected{/if}>Right</option>
                        <option value="center"{if $properties['button-alignment'] === 'center'} selected{/if}>Center</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-full-width-flag">Full width?</label>
                    <div class="radio" id="button-full-width-flag">
                        <label class="radio-inline">
                            <input type="radio" name="button-full-width-flag" id="button-full-width-flag-0" value="no"{if $properties['button-full-width-flag'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="button-full-width-flag" id="button-full-width-flag-1" value="yes"{if $properties['button-full-width-flag'] === 'yes'} checked="checked"{/if}> Yes
                        </label>

                    </div>
                </div>
            </div>
        </div>


        <div class="form-group">
            <label for="class">Class name</label>
            <input type="text"  id="button-class" name="button-class" class="form-control" value="{$properties['button-class']}">
        </div>
    </div>
    <div id="icon" class="tab-pane fade in" role="tabpanel" aria-labelledby="icon-tab">
        <div class="form-group">
            <label for="button-icon-flag">Icon?</label>
            <div class="radio" id="button-icon-flag">
                <label class="radio-inline">
                    <input type="radio" name="button-icon-flag" id="button-icon-flag-0" value="no"{if $properties['button-icon-flag'] === 'no'} checked="checked"{/if}> No
                </label>
                <label class="radio-inline">
                    <input type="radio" name="button-icon-flag" id="button-icon-flag-1" value="yes"{if $properties['button-icon-flag'] === 'yes'} checked="checked"{/if}> Yes
                </label>
            </div>
            <div id="button-font-icon-container" {if $properties['button-icon-flag'] === 'no'}style="display:none;"{/if}>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="button-icon">Icon</label>
                            <div>
                                <div class="btn-group">
                                    <button class="btn btn-primary" type="button" onclick="$('#font-icon-container').toggle();" style="padding:4px 12px 2px;">
                                        <span id="span-button-icon">{if !empty($properties['button-icon'])}<i class="{$properties['button-icon']} fa-lg"></i>{/if}</span>
                                        <input type="hidden" id="button-icon" name="button-icon" value="{$properties['button-icon']}">
                                        <span class="caret"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="button-icon-alignment">Icon alignment</label>
                            <select name="button-icon-alignment" id="button-icon-alignment" class="form-control">
                                <option value="left"{if $properties['button-icon-alignment'] === 'left'} selected{/if}>Left</option>
                                <option value="right"{if $properties['button-icon-alignment'] === 'right'} selected{/if}>Right</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="well" id="font-icon-container" style="display:none;">
                    <!-- Font awesome -->
                    <div id="icons">
                        <section id="new">
                            <h4 class="page-header" style="margin-top:0px;">New Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-bluetooth"></i><i class="fa fa-bluetooth-b"></i><i class="fa fa-codiepie"></i><i class="fa fa-credit-card-alt"></i><i class="fa fa-edge"></i><i class="fa fa-fort-awesome"></i><i class="fa fa-hashtag"></i><i class="fa fa-mixcloud"></i><i class="fa fa-modx"></i><i class="fa fa-pause-circle"></i><i class="fa fa-pause-circle-o"></i><i class="fa fa-percent"></i><i class="fa fa-product-hunt"></i><i class="fa fa-reddit-alien"></i><i class="fa fa-scribd"></i><i class="fa fa-shopping-bag"></i><i class="fa fa-shopping-basket"></i><i class="fa fa-stop-circle"></i><i class="fa fa-stop-circle-o"></i><i class="fa fa-usb"></i></div>
                        </section>
                        <section id="web-application">
                            <h4 class="page-header">Web Application Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-adjust"></i><i class="fa fa-anchor"></i><i class="fa fa-archive"></i><i class="fa fa-area-chart"></i><i class="fa fa-arrows"></i><i class="fa fa-arrows-h"></i><i class="fa fa-arrows-v"></i><i class="fa fa-asterisk"></i><i class="fa fa-at"></i><i class="fa fa-automobile"></i><i class="fa fa-balance-scale"></i><i class="fa fa-ban"></i><i class="fa fa-bank"></i><i class="fa fa-bar-chart"></i><i class="fa fa-bar-chart-o"></i><i class="fa fa-barcode"></i><i class="fa fa-bars"></i><i class="fa fa-battery-0"></i><i class="fa fa-battery-1"></i><i class="fa fa-battery-2"></i><i class="fa fa-battery-3"></i><i class="fa fa-battery-4"></i><i class="fa fa-battery-empty"></i><i class="fa fa-battery-full"></i><i class="fa fa-battery-half"></i><i class="fa fa-battery-quarter"></i><i class="fa fa-battery-three-quarters"></i><i class="fa fa-bed"></i><i class="fa fa-beer"></i><i class="fa fa-bell"></i><i class="fa fa-bell-o"></i><i class="fa fa-bell-slash"></i><i class="fa fa-bell-slash-o"></i><i class="fa fa-bicycle"></i><i class="fa fa-binoculars"></i><i class="fa fa-birthday-cake"></i><i class="fa fa-bluetooth"></i><i class="fa fa-bluetooth-b"></i><i class="fa fa-bolt"></i><i class="fa fa-bomb"></i><i class="fa fa-book"></i><i class="fa fa-bookmark"></i><i class="fa fa-bookmark-o"></i><i class="fa fa-briefcase"></i><i class="fa fa-bug"></i><i class="fa fa-building"></i><i class="fa fa-building-o"></i><i class="fa fa-bullhorn"></i><i class="fa fa-bullseye"></i><i class="fa fa-bus"></i><i class="fa fa-cab"></i><i class="fa fa-calculator"></i><i class="fa fa-calendar"></i><i class="fa fa-calendar-check-o"></i><i class="fa fa-calendar-minus-o"></i><i class="fa fa-calendar-o"></i><i class="fa fa-calendar-plus-o"></i><i class="fa fa-calendar-times-o"></i><i class="fa fa-camera"></i><i class="fa fa-camera-retro"></i><i class="fa fa-car"></i><i class="fa fa-caret-square-o-down"></i><i class="fa fa-caret-square-o-left"></i><i class="fa fa-caret-square-o-right"></i><i class="fa fa-caret-square-o-up"></i><i class="fa fa-cart-arrow-down"></i><i class="fa fa-cart-plus"></i><i class="fa fa-cc"></i><i class="fa fa-certificate"></i><i class="fa fa-check"></i><i class="fa fa-check-circle"></i><i class="fa fa-check-circle-o"></i><i class="fa fa-check-square"></i><i class="fa fa-check-square-o"></i><i class="fa fa-child"></i><i class="fa fa-circle"></i><i class="fa fa-circle-o"></i><i class="fa fa-circle-o-notch"></i><i class="fa fa-circle-thin"></i><i class="fa fa-clock-o"></i><i class="fa fa-clone"></i><i class="fa fa-close"></i><i class="fa fa-cloud"></i><i class="fa fa-cloud-download"></i><i class="fa fa-cloud-upload"></i><i class="fa fa-code"></i><i class="fa fa-code-fork"></i><i class="fa fa-coffee"></i><i class="fa fa-cog"></i><i class="fa fa-cogs"></i><i class="fa fa-comment"></i><i class="fa fa-comment-o"></i><i class="fa fa-commenting"></i><i class="fa fa-commenting-o"></i><i class="fa fa-comments"></i><i class="fa fa-comments-o"></i><i class="fa fa-compass"></i><i class="fa fa-copyright"></i><i class="fa fa-creative-commons"></i><i class="fa fa-credit-card"></i><i class="fa fa-credit-card-alt"></i><i class="fa fa-crop"></i><i class="fa fa-crosshairs"></i><i class="fa fa-cube"></i><i class="fa fa-cubes"></i><i class="fa fa-cutlery"></i><i class="fa fa-dashboard"></i><i class="fa fa-database"></i><i class="fa fa-desktop"></i><i class="fa fa-diamond"></i><i class="fa fa-dot-circle-o"></i><i class="fa fa-download"></i><i class="fa fa-edit"></i><i class="fa fa-ellipsis-h"></i><i class="fa fa-ellipsis-v"></i><i class="fa fa-envelope"></i><i class="fa fa-envelope-o"></i><i class="fa fa-envelope-square"></i><i class="fa fa-eraser"></i><i class="fa fa-exchange"></i><i class="fa fa-exclamation"></i><i class="fa fa-exclamation-circle"></i><i class="fa fa-exclamation-triangle"></i><i class="fa fa-external-link"></i><i class="fa fa-external-link-square"></i><i class="fa fa-eye"></i><i class="fa fa-eye-slash"></i><i class="fa fa-eyedropper"></i><i class="fa fa-fax"></i><i class="fa fa-feed"></i><i class="fa fa-female"></i><i class="fa fa-fighter-jet"></i><i class="fa fa-file-archive-o"></i><i class="fa fa-file-audio-o"></i><i class="fa fa-file-code-o"></i><i class="fa fa-file-excel-o"></i><i class="fa fa-file-image-o"></i><i class="fa fa-file-movie-o"></i><i class="fa fa-file-pdf-o"></i><i class="fa fa-file-photo-o"></i><i class="fa fa-file-picture-o"></i><i class="fa fa-file-powerpoint-o"></i><i class="fa fa-file-sound-o"></i><i class="fa fa-file-video-o"></i><i class="fa fa-file-word-o"></i><i class="fa fa-file-zip-o"></i><i class="fa fa-film"></i><i class="fa fa-filter"></i><i class="fa fa-fire"></i><i class="fa fa-fire-extinguisher"></i><i class="fa fa-flag"></i><i class="fa fa-flag-checkered"></i><i class="fa fa-flag-o"></i><i class="fa fa-flash"></i><i class="fa fa-flask"></i><i class="fa fa-folder"></i><i class="fa fa-folder-o"></i><i class="fa fa-folder-open"></i><i class="fa fa-folder-open-o"></i><i class="fa fa-frown-o"></i><i class="fa fa-futbol-o"></i><i class="fa fa-gamepad"></i><i class="fa fa-gavel"></i><i class="fa fa-gear"></i><i class="fa fa-gears"></i><i class="fa fa-gift"></i><i class="fa fa-glass"></i><i class="fa fa-globe"></i><i class="fa fa-graduation-cap"></i><i class="fa fa-group"></i><i class="fa fa-hand-grab-o"></i><i class="fa fa-hand-lizard-o"></i><i class="fa fa-hand-paper-o"></i><i class="fa fa-hand-peace-o"></i><i class="fa fa-hand-pointer-o"></i><i class="fa fa-hand-rock-o"></i><i class="fa fa-hand-scissors-o"></i><i class="fa fa-hand-spock-o"></i><i class="fa fa-hand-stop-o"></i><i class="fa fa-hashtag"></i><i class="fa fa-hdd-o"></i><i class="fa fa-headphones"></i><i class="fa fa-heart"></i><i class="fa fa-heart-o"></i><i class="fa fa-heartbeat"></i><i class="fa fa-history"></i><i class="fa fa-home"></i><i class="fa fa-hotel"></i><i class="fa fa-hourglass"></i><i class="fa fa-hourglass-1"></i><i class="fa fa-hourglass-2"></i><i class="fa fa-hourglass-3"></i><i class="fa fa-hourglass-end"></i><i class="fa fa-hourglass-half"></i><i class="fa fa-hourglass-o"></i><i class="fa fa-hourglass-start"></i><i class="fa fa-i-cursor"></i><i class="fa fa-image"></i><i class="fa fa-inbox"></i><i class="fa fa-industry"></i><i class="fa fa-info"></i><i class="fa fa-info-circle"></i><i class="fa fa-institution"></i><i class="fa fa-key"></i><i class="fa fa-keyboard-o"></i><i class="fa fa-language"></i><i class="fa fa-laptop"></i><i class="fa fa-leaf"></i><i class="fa fa-legal"></i><i class="fa fa-lemon-o"></i><i class="fa fa-level-down"></i><i class="fa fa-level-up"></i><i class="fa fa-life-bouy"></i><i class="fa fa-life-buoy"></i><i class="fa fa-life-ring"></i><i class="fa fa-life-saver"></i><i class="fa fa-lightbulb-o"></i><i class="fa fa-line-chart"></i><i class="fa fa-location-arrow"></i><i class="fa fa-lock"></i><i class="fa fa-magic"></i><i class="fa fa-magnet"></i><i class="fa fa-mail-forward"></i><i class="fa fa-mail-reply"></i><i class="fa fa-mail-reply-all"></i><i class="fa fa-male"></i><i class="fa fa-map"></i><i class="fa fa-map-marker"></i><i class="fa fa-map-o"></i><i class="fa fa-map-pin"></i><i class="fa fa-map-signs"></i><i class="fa fa-meh-o"></i><i class="fa fa-microphone"></i><i class="fa fa-microphone-slash"></i><i class="fa fa-minus"></i><i class="fa fa-minus-circle"></i><i class="fa fa-minus-square"></i><i class="fa fa-minus-square-o"></i><i class="fa fa-mobile"></i><i class="fa fa-mobile-phone"></i><i class="fa fa-money"></i><i class="fa fa-moon-o"></i><i class="fa fa-mortar-board"></i><i class="fa fa-motorcycle"></i><i class="fa fa-mouse-pointer"></i><i class="fa fa-music"></i><i class="fa fa-navicon"></i><i class="fa fa-newspaper-o"></i><i class="fa fa-object-group"></i><i class="fa fa-object-ungroup"></i><i class="fa fa-paint-brush"></i><i class="fa fa-paper-plane"></i><i class="fa fa-paper-plane-o"></i><i class="fa fa-paw"></i><i class="fa fa-pencil"></i><i class="fa fa-pencil-square"></i><i class="fa fa-pencil-square-o"></i><i class="fa fa-percent"></i><i class="fa fa-phone"></i><i class="fa fa-phone-square"></i><i class="fa fa-photo"></i><i class="fa fa-picture-o"></i><i class="fa fa-pie-chart"></i><i class="fa fa-plane"></i><i class="fa fa-plug"></i><i class="fa fa-plus"></i><i class="fa fa-plus-circle"></i><i class="fa fa-plus-square"></i><i class="fa fa-plus-square-o"></i><i class="fa fa-power-off"></i><i class="fa fa-print"></i><i class="fa fa-puzzle-piece"></i><i class="fa fa-qrcode"></i><i class="fa fa-question"></i><i class="fa fa-question-circle"></i><i class="fa fa-quote-left"></i><i class="fa fa-quote-right"></i><i class="fa fa-random"></i><i class="fa fa-recycle"></i><i class="fa fa-refresh"></i><i class="fa fa-registered"></i><i class="fa fa-remove"></i><i class="fa fa-reorder"></i><i class="fa fa-reply"></i><i class="fa fa-reply-all"></i><i class="fa fa-retweet"></i><i class="fa fa-road"></i><i class="fa fa-rocket"></i><i class="fa fa-rss"></i><i class="fa fa-rss-square"></i><i class="fa fa-search"></i><i class="fa fa-search-minus"></i><i class="fa fa-search-plus"></i><i class="fa fa-send"></i><i class="fa fa-send-o"></i><i class="fa fa-server"></i><i class="fa fa-share"></i><i class="fa fa-share-alt"></i><i class="fa fa-share-alt-square"></i><i class="fa fa-share-square"></i><i class="fa fa-share-square-o"></i><i class="fa fa-shield"></i><i class="fa fa-ship"></i><i class="fa fa-shopping-bag"></i><i class="fa fa-shopping-basket"></i><i class="fa fa-shopping-cart"></i><i class="fa fa-sign-in"></i><i class="fa fa-sign-out"></i><i class="fa fa-signal"></i><i class="fa fa-sitemap"></i><i class="fa fa-sliders"></i><i class="fa fa-smile-o"></i><i class="fa fa-soccer-ball-o"></i><i class="fa fa-sort"></i><i class="fa fa-sort-alpha-asc"></i><i class="fa fa-sort-alpha-desc"></i><i class="fa fa-sort-amount-asc"></i><i class="fa fa-sort-amount-desc"></i><i class="fa fa-sort-asc"></i><i class="fa fa-sort-desc"></i><i class="fa fa-sort-down"></i><i class="fa fa-sort-numeric-asc"></i><i class="fa fa-sort-numeric-desc"></i><i class="fa fa-sort-up"></i><i class="fa fa-space-shuttle"></i><i class="fa fa-spinner"></i><i class="fa fa-spoon"></i><i class="fa fa-square"></i><i class="fa fa-square-o"></i><i class="fa fa-star"></i><i class="fa fa-star-half"></i><i class="fa fa-star-half-empty"></i><i class="fa fa-star-half-full"></i><i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-sticky-note"></i><i class="fa fa-sticky-note-o"></i><i class="fa fa-street-view"></i><i class="fa fa-suitcase"></i><i class="fa fa-sun-o"></i><i class="fa fa-support"></i><i class="fa fa-tablet"></i><i class="fa fa-tachometer"></i><i class="fa fa-tag"></i><i class="fa fa-tags"></i><i class="fa fa-tasks"></i><i class="fa fa-taxi"></i><i class="fa fa-television"></i><i class="fa fa-terminal"></i><i class="fa fa-thumb-tack"></i><i class="fa fa-thumbs-down"></i><i class="fa fa-thumbs-o-down"></i><i class="fa fa-thumbs-o-up"></i><i class="fa fa-thumbs-up"></i><i class="fa fa-ticket"></i><i class="fa fa-times"></i><i class="fa fa-times-circle"></i><i class="fa fa-times-circle-o"></i><i class="fa fa-tint"></i><i class="fa fa-toggle-down"></i><i class="fa fa-toggle-left"></i><i class="fa fa-toggle-off"></i><i class="fa fa-toggle-on"></i><i class="fa fa-toggle-right"></i><i class="fa fa-toggle-up"></i><i class="fa fa-trademark"></i><i class="fa fa-trash"></i><i class="fa fa-trash-o"></i><i class="fa fa-tree"></i><i class="fa fa-trophy"></i><i class="fa fa-truck"></i><i class="fa fa-tty"></i><i class="fa fa-tv"></i><i class="fa fa-umbrella"></i><i class="fa fa-university"></i><i class="fa fa-unlock"></i><i class="fa fa-unlock-alt"></i><i class="fa fa-unsorted"></i><i class="fa fa-upload"></i><i class="fa fa-user"></i><i class="fa fa-user-plus"></i><i class="fa fa-user-secret"></i><i class="fa fa-user-times"></i><i class="fa fa-users"></i><i class="fa fa-video-camera"></i><i class="fa fa-volume-down"></i><i class="fa fa-volume-off"></i><i class="fa fa-volume-up"></i><i class="fa fa-warning"></i><i class="fa fa-wheelchair"></i><i class="fa fa-wifi"></i><i class="fa fa-wrench"></i>
                            </div>
                        </section>
                        <section id="hand">
                            <h4 class="page-header">Hand Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-hand-grab-o"></i><i class="fa fa-hand-lizard-o"></i><i class="fa fa-hand-o-down"></i><i class="fa fa-hand-o-left"></i><i class="fa fa-hand-o-right"></i><i class="fa fa-hand-o-up"></i><i class="fa fa-hand-paper-o"></i><i class="fa fa-hand-peace-o"></i><i class="fa fa-hand-pointer-o"></i><i class="fa fa-hand-rock-o"></i><i class="fa fa-hand-scissors-o"></i><i class="fa fa-hand-spock-o"></i><i class="fa fa-hand-stop-o"></i><i class="fa fa-thumbs-down"></i><i class="fa fa-thumbs-o-down"></i><i class="fa fa-thumbs-o-up"></i><i class="fa fa-thumbs-up"></i>
                            </div>
                        </section><section id="transportation">
                            <h4 class="page-header">Transportation Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-ambulance"></i><i class="fa fa-automobile"></i><i class="fa fa-bicycle"></i><i class="fa fa-bus"></i><i class="fa fa-cab"></i><i class="fa fa-car"></i><i class="fa fa-fighter-jet"></i><i class="fa fa-motorcycle"></i><i class="fa fa-plane"></i><i class="fa fa-rocket"></i><i class="fa fa-ship"></i><i class="fa fa-space-shuttle"></i><i class="fa fa-subway"></i><i class="fa fa-taxi"></i><i class="fa fa-train"></i><i class="fa fa-truck"></i><i class="fa fa-wheelchair"></i>
                            </div>
                        </section><section id="gender">
                            <h4 class="page-header">Gender Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-genderless"></i><i class="fa fa-intersex"></i><i class="fa fa-mars"></i><i class="fa fa-mars-double"></i><i class="fa fa-mars-stroke"></i><i class="fa fa-mars-stroke-h"></i><i class="fa fa-mars-stroke-v"></i><i class="fa fa-mercury"></i><i class="fa fa-neuter"></i><i class="fa fa-transgender"></i><i class="fa fa-transgender-alt"></i><i class="fa fa-venus"></i><i class="fa fa-venus-double"></i><i class="fa fa-venus-mars"></i>
                            </div>
                        </section><section id="file-type">
                            <h4 class="page-header">File Type Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-file"></i><i class="fa fa-file-archive-o"></i><i class="fa fa-file-audio-o"></i><i class="fa fa-file-code-o"></i><i class="fa fa-file-excel-o"></i><i class="fa fa-file-image-o"></i><i class="fa fa-file-movie-o"></i><i class="fa fa-file-o"></i><i class="fa fa-file-pdf-o"></i><i class="fa fa-file-photo-o"></i><i class="fa fa-file-picture-o"></i><i class="fa fa-file-powerpoint-o"></i><i class="fa fa-file-sound-o"></i><i class="fa fa-file-text"></i><i class="fa fa-file-text-o"></i><i class="fa fa-file-video-o"></i><i class="fa fa-file-word-o"></i><i class="fa fa-file-zip-o"></i>
                            </div>
                        </section><section id="spinner">
                            <h4 class="page-header">Spinner Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-circle-o-notch fa-spin"></i><i class="fa fa-cog"></i><i class="fa fa-gear"></i><i class="fa fa-refresh"></i><i class="fa fa-spinner"></i>
                            </div>
                        </section><section id="form-control">
                            <h4 class="page-header">Form Control Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-check-square"></i><i class="fa fa-check-square-o"></i><i class="fa fa-circle"></i><i class="fa fa-circle-o"></i><i class="fa fa-dot-circle-o"></i><i class="fa fa-minus-square"></i><i class="fa fa-minus-square-o"></i><i class="fa fa-plus-square"></i><i class="fa fa-plus-square-o"></i><i class="fa fa-square"></i><i class="fa fa-square-o"></i>
                            </div>
                        </section><section id="payment">
                            <h4 class="page-header">Payment Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-cc-amex"></i><i class="fa fa-cc-diners-club"></i><i class="fa fa-cc-discover"></i><i class="fa fa-cc-jcb"></i><i class="fa fa-cc-mastercard"></i><i class="fa fa-cc-paypal"></i><i class="fa fa-cc-stripe"></i><i class="fa fa-cc-visa"></i><i class="fa fa-credit-card"></i><i class="fa fa-credit-card-alt"></i><i class="fa fa-google-wallet"></i><i class="fa fa-paypal"></i>
                            </div>
                        </section><section id="chart">
                            <h4 class="page-header">Chart Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-area-chart"></i><i class="fa fa-bar-chart"></i><i class="fa fa-bar-chart-o"></i><i class="fa fa-line-chart"></i><i class="fa fa-pie-chart"></i>
                            </div>
                        </section><section id="currency">
                            <h4 class="page-header">Currency Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-bitcoin"></i><i class="fa fa-btc"></i><i class="fa fa-cny"></i><i class="fa fa-dollar"></i><i class="fa fa-eur"></i><i class="fa fa-euro"></i><i class="fa fa-gbp"></i><i class="fa fa-gg"></i><i class="fa fa-gg-circle"></i><i class="fa fa-ils"></i><i class="fa fa-inr"></i><i class="fa fa-jpy"></i><i class="fa fa-krw"></i><i class="fa fa-money"></i><i class="fa fa-rmb"></i><i class="fa fa-rouble"></i><i class="fa fa-rub"></i><i class="fa fa-ruble"></i><i class="fa fa-rupee"></i><i class="fa fa-shekel"></i><i class="fa fa-sheqel"></i><i class="fa fa-try"></i><i class="fa fa-turkish-lira"></i><i class="fa fa-usd"></i><i class="fa fa-won"></i><i class="fa fa-yen"></i>
                            </div>

                        </section><section id="text-editor">
                            <h4 class="page-header">Text Editor Icons</h4>

                            <div class="fontawesome-icon-list">
                                <i class="fa fa-align-center"></i><i class="fa fa-align-justify"></i><i class="fa fa-align-left"></i><i class="fa fa-align-right"></i><i class="fa fa-bold"></i><i class="fa fa-chain"></i><i class="fa fa-chain-broken"></i><i class="fa fa-clipboard"></i><i class="fa fa-columns"></i><i class="fa fa-copy"></i><i class="fa fa-cut"></i><i class="fa fa-dedent"></i><i class="fa fa-eraser"></i><i class="fa fa-file"></i><i class="fa fa-file-o"></i><i class="fa fa-file-text"></i><i class="fa fa-file-text-o"></i><i class="fa fa-files-o"></i><i class="fa fa-floppy-o"></i><i class="fa fa-font"></i><i class="fa fa-header"></i><i class="fa fa-indent"></i><i class="fa fa-italic"></i><i class="fa fa-link"></i><i class="fa fa-list"></i><i class="fa fa-list-alt"></i><i class="fa fa-list-ol"></i><i class="fa fa-list-ul"></i><i class="fa fa-outdent"></i><i class="fa fa-paperclip"></i><i class="fa fa-paragraph"></i><i class="fa fa-paste"></i><i class="fa fa-repeat"></i><i class="fa fa-rotate-left"></i><i class="fa fa-rotate-right"></i><i class="fa fa-save"></i><i class="fa fa-scissors"></i><i class="fa fa-strikethrough"></i><i class="fa fa-subscript"></i><i class="fa fa-superscript"></i><i class="fa fa-table"></i><i class="fa fa-text-height"></i><i class="fa fa-text-width"></i><i class="fa fa-th"></i><i class="fa fa-th-large"></i><i class="fa fa-th-list"></i><i class="fa fa-underline"></i><i class="fa fa-undo"></i><i class="fa fa-unlink"></i>
                            </div>

                        </section><section id="directional">
                            <h4 class="page-header">Directional Icons</h4>

                            <div class="fontawesome-icon-list">
                                <i class="fa fa-angle-double-down"></i><i class="fa fa-angle-double-left"></i><i class="fa fa-angle-double-right"></i><i class="fa fa-angle-double-up"></i><i class="fa fa-angle-down"></i><i class="fa fa-angle-left"></i><i class="fa fa-angle-right"></i><i class="fa fa-angle-up"></i><i class="fa fa-arrow-circle-down"></i><i class="fa fa-arrow-circle-left"></i><i class="fa fa-arrow-circle-o-down"></i><i class="fa fa-arrow-circle-o-left"></i><i class="fa fa-arrow-circle-o-right"></i><i class="fa fa-arrow-circle-o-up"></i><i class="fa fa-arrow-circle-right"></i><i class="fa fa-arrow-circle-up"></i><i class="fa fa-arrow-down"></i><i class="fa fa-arrow-left"></i><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-up"></i><i class="fa fa-arrows"></i><i class="fa fa-arrows-alt"></i><i class="fa fa-arrows-h"></i><i class="fa fa-arrows-v"></i><i class="fa fa-caret-down"></i><i class="fa fa-caret-left"></i><i class="fa fa-caret-right"></i><i class="fa fa-caret-square-o-down"></i><i class="fa fa-caret-square-o-left"></i><i class="fa fa-caret-square-o-right"></i><i class="fa fa-caret-square-o-up"></i><i class="fa fa-caret-up"></i><i class="fa fa-chevron-circle-down"></i><i class="fa fa-chevron-circle-left"></i><i class="fa fa-chevron-circle-right"></i><i class="fa fa-chevron-circle-up"></i><i class="fa fa-chevron-down"></i><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-up"></i><i class="fa fa-exchange"></i><i class="fa fa-hand-o-down"></i><i class="fa fa-hand-o-left"></i><i class="fa fa-hand-o-right"></i><i class="fa fa-hand-o-up"></i><i class="fa fa-long-arrow-down"></i><i class="fa fa-long-arrow-left"></i><i class="fa fa-long-arrow-right"></i><i class="fa fa-long-arrow-up"></i><i class="fa fa-toggle-down"></i><i class="fa fa-toggle-left"></i><i class="fa fa-toggle-right"></i><i class="fa fa-toggle-up"></i>
                            </div>

                        </section><section id="video-player">
                            <h4 class="page-header">Video Player Icons</h4>

                            <div class="fontawesome-icon-list">
                                <i class="fa fa-arrows-alt"></i><i class="fa fa-backward"></i><i class="fa fa-compress"></i><i class="fa fa-eject"></i><i class="fa fa-expand"></i><i class="fa fa-fast-backward"></i><i class="fa fa-fast-forward"></i><i class="fa fa-forward"></i><i class="fa fa-pause"></i><i class="fa fa-pause-circle"></i><i class="fa fa-pause-circle-o"></i><i class="fa fa-play"></i><i class="fa fa-play-circle"></i><i class="fa fa-play-circle-o"></i><i class="fa fa-random"></i><i class="fa fa-step-backward"></i><i class="fa fa-step-forward"></i><i class="fa fa-stop"></i><i class="fa fa-stop-circle"></i><i class="fa fa-stop-circle-o"></i><i class="fa fa-youtube-play"></i>
                            </div>

                        </section><section id="brand">
                            <h4 class="page-header">Brand Icons</h4>
                            <div class="fontawesome-icon-list margin-bottom-lg">
                                <i class="fa fa-500px"></i><i class="fa fa-adn"></i><i class="fa fa-amazon"></i><i class="fa fa-android"></i><i class="fa fa-angellist"></i><i class="fa fa-apple"></i><i class="fa fa-behance"></i><i class="fa fa-behance-square"></i><i class="fa fa-bitbucket"></i><i class="fa fa-bitbucket-square"></i><i class="fa fa-bitcoin"></i><i class="fa fa-black-tie"></i><i class="fa fa-bluetooth"></i><i class="fa fa-bluetooth-b"></i><i class="fa fa-btc"></i><i class="fa fa-buysellads"></i><i class="fa fa-cc-amex"></i><i class="fa fa-cc-diners-club"></i><i class="fa fa-cc-discover"></i><i class="fa fa-cc-jcb"></i><i class="fa fa-cc-mastercard"></i><i class="fa fa-cc-paypal"></i><i class="fa fa-cc-stripe"></i><i class="fa fa-cc-visa"></i><i class="fa fa-chrome"></i><i class="fa fa-codepen"></i><i class="fa fa-codiepie"></i><i class="fa fa-connectdevelop"></i><i class="fa fa-contao"></i><i class="fa fa-css3"></i><i class="fa fa-dashcube"></i><i class="fa fa-delicious"></i><i class="fa fa-deviantart"></i><i class="fa fa-digg"></i><i class="fa fa-dribbble"></i><i class="fa fa-dropbox"></i><i class="fa fa-drupal"></i><i class="fa fa-edge"></i><i class="fa fa-empire"></i><i class="fa fa-expeditedssl"></i><i class="fa fa-facebook"></i><i class="fa fa-facebook-f"></i><i class="fa fa-facebook-official"></i><i class="fa fa-facebook-square"></i><i class="fa fa-firefox"></i><i class="fa fa-flickr"></i><i class="fa fa-fonticons"></i><i class="fa fa-fort-awesome"></i><i class="fa fa-forumbee"></i><i class="fa fa-foursquare"></i><i class="fa fa-ge"></i><i class="fa fa-get-pocket"></i><i class="fa fa-gg"></i><i class="fa fa-gg-circle"></i><i class="fa fa-git"></i><i class="fa fa-git-square"></i><i class="fa fa-github"></i><i class="fa fa-github-alt"></i><i class="fa fa-github-square"></i><i class="fa fa-gittip"></i><i class="fa fa-google"></i><i class="fa fa-google-plus"></i><i class="fa fa-google-plus-square"></i><i class="fa fa-google-wallet"></i><i class="fa fa-gratipay"></i><i class="fa fa-hacker-news"></i><i class="fa fa-houzz"></i><i class="fa fa-html5"></i><i class="fa fa-instagram"></i><i class="fa fa-internet-explorer"></i><i class="fa fa-ioxhost"></i><i class="fa fa-joomla"></i><i class="fa fa-jsfiddle"></i><i class="fa fa-lastfm"></i><i class="fa fa-lastfm-square"></i><i class="fa fa-leanpub"></i><i class="fa fa-linkedin"></i><i class="fa fa-linkedin-square"></i><i class="fa fa-linux"></i><i class="fa fa-maxcdn"></i><i class="fa fa-meanpath"></i><i class="fa fa-medium"></i><i class="fa fa-mixcloud"></i><i class="fa fa-modx"></i><i class="fa fa-odnoklassniki"></i><i class="fa fa-odnoklassniki-square"></i><i class="fa fa-opencart"></i><i class="fa fa-openid"></i><i class="fa fa-opera"></i><i class="fa fa-optin-monster"></i><i class="fa fa-pagelines"></i><i class="fa fa-paypal"></i><i class="fa fa-pied-piper"></i><i class="fa fa-pied-piper-alt"></i><i class="fa fa-pinterest"></i><i class="fa fa-pinterest-p"></i><i class="fa fa-pinterest-square"></i><i class="fa fa-product-hunt"></i><i class="fa fa-qq"></i><i class="fa fa-ra"></i><i class="fa fa-rebel"></i><i class="fa fa-reddit"></i><i class="fa fa-reddit-alien"></i><i class="fa fa-reddit-square"></i><i class="fa fa-renren"></i><i class="fa fa-safari"></i><i class="fa fa-scribd"></i><i class="fa fa-sellsy"></i><i class="fa fa-share-alt"></i><i class="fa fa-share-alt-square"></i><i class="fa fa-shirtsinbulk"></i><i class="fa fa-simplybuilt"></i><i class="fa fa-skyatlas"></i><i class="fa fa-skype"></i><i class="fa fa-slack"></i><i class="fa fa-slideshare"></i><i class="fa fa-soundcloud"></i><i class="fa fa-spotify"></i><i class="fa fa-stack-exchange"></i><i class="fa fa-stack-overflow"></i><i class="fa fa-steam"></i><i class="fa fa-steam-square"></i><i class="fa fa-stumbleupon"></i><i class="fa fa-stumbleupon-circle"></i><i class="fa fa-tencent-weibo"></i><i class="fa fa-trello"></i><i class="fa fa-tripadvisor"></i><i class="fa fa-tumblr"></i><i class="fa fa-tumblr-square"></i><i class="fa fa-twitch"></i><i class="fa fa-twitter"></i><i class="fa fa-twitter-square"></i><i class="fa fa-usb"></i><i class="fa fa-viacoin"></i><i class="fa fa-vimeo"></i><i class="fa fa-vimeo-square"></i><i class="fa fa-vine"></i><i class="fa fa-vk"></i><i class="fa fa-wechat"></i><i class="fa fa-weibo"></i><i class="fa fa-weixin"></i><i class="fa fa-whatsapp"></i><i class="fa fa-wikipedia-w"></i><i class="fa fa-windows"></i><i class="fa fa-wordpress"></i><i class="fa fa-xing"></i><i class="fa fa-xing-square"></i><i class="fa fa-y-combinator"></i><i class="fa fa-y-combinator-square"></i><i class="fa fa-yahoo"></i><i class="fa fa-yc"></i><i class="fa fa-yc-square"></i><i class="fa fa-yelp"></i><i class="fa fa-youtube"></i><i class="fa fa-youtube-play"></i><i class="fa fa-youtube-square"></i>
                            </div>
                        </section>
                        <section id="medical">
                            <h4 class="page-header">Medical Icons</h4>
                            <div class="fontawesome-icon-list">
                                <i class="fa fa-ambulance"></i><i class="fa fa-h-square"></i><i class="fa fa-heart"></i><i class="fa fa-heart-o"></i><i class="fa fa-heartbeat"></i><i class="fa fa-hospital-o"></i><i class="fa fa-medkit"></i><i class="fa fa-plus-square"></i><i class="fa fa-stethoscope"></i><i class="fa fa-user-md"></i><i class="fa fa-wheelchair"></i></div>
                        </section>
                    </div>
                    <!-- Font awesome -->
                </div>
            </div>
        </div>
    </div>
    <div id="url-link" class="tab-pane fade in" role="tabpanel" aria-labelledby="url-link-tab">
        <div class="form-group">
            <label for="link-flag">URL (link)</label>
            <div class="radio" id="link-flag">
                <label class="radio-inline">
                    <input type="radio" name="link-flag" id="link-flag-0" value="no"{if $properties['link-flag'] === 'no'} checked="checked"{/if}> No
                </label>
                <label class="radio-inline">
                    <input type="radio" name="link-flag" id="link-flag-1" value="yes"{if $properties['link-flag'] === 'yes'} checked="checked"{/if}> Yes
                </label>
            </div>
        </div>
        <div id="url-link-container" class="well" {if $properties['link-flag'] === 'no'}style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="link-url">Choose a link</label>
                        <input type="text" class="form-control" id="link-url" name="link-url" placeholder="URL: http://www.example.com" value="{$properties['link-url']}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="link-title">Link Title</label>
                        <input type="text" class="form-control" id="link-title" name="link-title" value="{$properties['link-title']}">
                    </div>
                </div>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="link-new-tab-flag" name="link-new-tab-flag" value="yes" {if $properties['link-new-tab-flag'] === 'yes'} checked="checked"{/if}> Open link in a new tab
                </label>
            </div>
        </div>
    </div>
    <div id="wow-animation" class="tab-pane fade in" role="tabpanel" aria-labelledby="wow-animation-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-style">Animation style</label>
                    <select id="animation-style" name="animation-style" class="form-control">
                        <option value="">None</option>
                        <optgroup label="Attention Seekers">
                            <option value="bounce"{if $properties['animation-style'] === 'bounce'} selected{/if}>bounce</option>
                            <option value="flash"{if $properties['animation-style'] === 'flash'} selected{/if}>flash</option>
                            <option value="pulse"{if $properties['animation-style'] === 'pulse'} selected{/if}>pulse</option>
                            <option value="rubberBand"{if $properties['animation-style'] === 'rubberBand'} selected{/if}>rubberBand</option>
                            <option value="shake"{if $properties['animation-style'] === 'shake'} selected{/if}>shake</option>
                            <option value="swing"{if $properties['animation-style'] === 'swing'} selected{/if}>swing</option>
                            <option value="tada"{if $properties['animation-style'] === 'tada'} selected{/if}>tada</option>
                            <option value="wobble"{if $properties['animation-style'] === 'wobble'} selected{/if}>wobble</option>
                            <option value="jello"{if $properties['animation-style'] === 'jello'} selected{/if}>jello</option>
                        </optgroup>

                        <optgroup label="Bouncing Entrances">
                            <option value="bounceIn"{if $properties['animation-style'] === 'bounceIn'} selected{/if}>bounceIn</option>
                            <option value="bounceInDown"{if $properties['animation-style'] === 'bounceInDown'} selected{/if}>bounceInDown</option>
                            <option value="bounceInLeft"{if $properties['animation-style'] === 'bounceInLeft'} selected{/if}>bounceInLeft</option>
                            <option value="bounceInRight"{if $properties['animation-style'] === 'bounceInRight'} selected{/if}>bounceInRight</option>
                            <option value="bounceInUp"{if $properties['animation-style'] === 'bounceInUp'} selected{/if}>bounceInUp</option>
                        </optgroup>

                        <optgroup label="Fading Entrances">
                            <option value="fadeIn"{if $properties['animation-style'] === 'fadeIn'} selected{/if}>fadeIn</option>
                            <option value="fadeInDown"{if $properties['animation-style'] === 'fadeInDown'} selected{/if}>fadeInDown</option>
                            <option value="fadeInDownBig"{if $properties['animation-style'] === 'fadeInDownBig'} selected{/if}>fadeInDownBig</option>
                            <option value="fadeInLeft"{if $properties['animation-style'] === 'fadeInLeft'} selected{/if}>fadeInLeft</option>
                            <option value="fadeInLeftBig"{if $properties['animation-style'] === 'fadeInLeftBig'} selected{/if}>fadeInLeftBig</option>
                            <option value="fadeInRight"{if $properties['animation-style'] === 'fadeInRight'} selected{/if}>fadeInRight</option>
                            <option value="fadeInRightBig"{if $properties['animation-style'] === 'fadeInRightBig'} selected{/if}>fadeInRightBig</option>
                            <option value="fadeInUp"{if $properties['animation-style'] === 'fadeInUp'} selected{/if}>fadeInUp</option>
                            <option value="fadeInUpBig"{if $properties['animation-style'] === 'fadeInUpBig'} selected{/if}>fadeInUpBig</option>
                        </optgroup>

                        <optgroup label="Flippers">
                            <option value="flip"{if $properties['animation-style'] === 'flip'} selected{/if}>flip</option>
                            <option value="flipInX"{if $properties['animation-style'] === 'flipInX'} selected{/if}>flipInX</option>
                            <option value="flipInY"{if $properties['animation-style'] === 'flipInY'} selected{/if}>flipInY</option>
                        </optgroup>

                        <optgroup label="Lightspeed">
                            <option value="lightSpeedIn"{if $properties['animation-style'] === 'lightSpeedIn'} selected{/if}>lightSpeedIn</option>
                        </optgroup>

                        <optgroup label="Rotating Entrances">
                            <option value="rotateIn"{if $properties['animation-style'] === 'rotateIn'} selected{/if}>rotateIn</option>
                            <option value="rotateInDownLeft"{if $properties['animation-style'] === 'rotateInDownLeft'} selected{/if}>rotateInDownLeft</option>
                            <option value="rotateInDownRight"{if $properties['animation-style'] === 'rotateInDownRight'} selected{/if}>rotateInDownRight</option>
                            <option value="rotateInUpLeft"{if $properties['animation-style'] === 'rotateInUpLeft'} selected{/if}>rotateInUpLeft</option>
                            <option value="rotateInUpRight"{if $properties['animation-style'] === 'rotateInUpRight'} selected{/if}>rotateInUpRight</option>
                        </optgroup>

                        <optgroup label="Sliding Entrances">
                            <option value="slideInUp"{if $properties['animation-style'] === 'slideInUp'} selected{/if}>slideInUp</option>
                            <option value="slideInDown"{if $properties['animation-style'] === 'slideInDown'} selected{/if}>slideInDown</option>
                            <option value="slideInLeft"{if $properties['animation-style'] === 'slideInLeft'} selected{/if}>slideInLeft</option>
                            <option value="slideInRight"{if $properties['animation-style'] === 'slideInRight'} selected{/if}>slideInRight</option>

                        </optgroup>

                        <optgroup label="Zoom Entrances">
                            <option value="zoomIn"{if $properties['animation-style'] === 'zoomIn'} selected{/if}>zoomIn</option>
                            <option value="zoomInDown"{if $properties['animation-style'] === 'zoomInDown'} selected{/if}>zoomInDown</option>
                            <option value="zoomInLeft"{if $properties['animation-style'] === 'zoomInLeft'} selected{/if}>zoomInLeft</option>
                            <option value="zoomInRight"{if $properties['animation-style'] === 'zoomInRight'} selected{/if}>zoomInRight</option>
                            <option value="zoomInUp"{if $properties['animation-style'] === 'zoomInUp'} selected{/if}>zoomInUp</option>
                        </optgroup>

                        <optgroup label="Specials">
                            <option value="hinge"{if $properties['animation-style'] === 'hinge'} selected{/if}>hinge</option>
                            <option value="rollIn"{if $properties['animation-style'] === 'rollIn'} selected{/if}>rollIn</option>
                        </optgroup>

                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-duration">Duration</label>
                    <input type="text" class="form-control" id="animation-duration" name="animation-duration" value="{$properties['animation-duration']}">
                    <span class="help-block">Change the animation duration.</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-delay">Delay</label>
                    <input type="text" class="form-control" id="animation-delay" name="animation-delay" value="{$properties['animation-delay']}">
                    <span class="help-block">Delay before the animation starts.</span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-offset">Offset</label>
                    <input type="text" class="form-control" id="animation-offset" name="animation-offset" value="{$properties['animation-offset']}">
                    <span class="help-block">Distance to start the animation.</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-iteration">Iteration</label>
                    <input type="text" class="form-control" id="animation-iteration" name="animation-iteration" value="{$properties['animation-iteration']}">
                    <span class="help-block">The animation number times is repeated.</span>
                </div>
            </div>
        </div>
    </div>
    <div id="style-design" class="tab-pane fade in" role="tabpanel" aria-labelledby="style-design-tab">
        <div class="row">
            <label for="background-color" class="col-sm-4 form-control-static">Background color</label>
            <div class="col-sm-4">
                <div class="input-group background-color-picker colorpicker-element">
                    <input type="text" class="form-control" name="background-color" id="background-color" value="{$properties['background-color']}">
                    <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <label for="margin" class="col-sm-2 form-control-static">Margin</label>
            <div class="col-sm-10" id="margin">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-top" value="{$properties['margin-top']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-right" value="{$properties['margin-right']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-bottom" value="{$properties['margin-bottom']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-left" value="{$properties['margin-left']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <label for="padding" class="col-sm-2 form-control-static">Padding</label>
            <div class="col-sm-10" id="padding">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-top" id="padding-top" value="{$properties['padding-top']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-right" id="padding-right" value="{$properties['padding-right']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-bottom" id="padding-bottom" value="{$properties['padding-bottom']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-left" id="padding-left" value="{$properties['padding-left']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row ">
            <label for="border-width" class="col-sm-2 form-control-static">Border</label>
            <div class="col-sm-10" id="border-width">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-top-width" id="border-top-width" value="{$properties['border-top-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-right-width" id="border-right-width" value="{$properties['border-right-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-bottom-width" id="border-bottom-width" value="{$properties['border-bottom-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-left-width" id="border-left-width" value="{$properties['border-left-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-sm-4 col-sm-offset-2">
                <select  class="form-control" name="border-style" id="border-style">
                    <option value=""></option>
                    <option value="none"{if $properties['border-style'] === 'none'} selected{/if}>none</option>
                    <option value="hidden"{if $properties['border-style'] === 'hidden'} selected{/if}>hidden</option>
                    <option value="dotted"{if $properties['border-style'] === 'dotted'} selected{/if}>dotted</option>
                    <option value="dashed"{if $properties['border-style'] === 'dashed'} selected{/if}>dashed</option>
                    <option value="solid"{if $properties['border-style'] === 'solid'} selected{/if}>solid</option>
                    <option value="double"{if $properties['border-style'] === 'double'} selected{/if}>double</option>
                    <option value="groove"{if $properties['border-style'] === 'groove'} selected{/if}>groove</option>
                    <option value="ridge"{if $properties['border-style'] === 'ridge'} selected{/if}>ridge</option>
                    <option value="inset"{if $properties['border-style'] === 'inset'} selected{/if}>inset</option>
                    <option value="outset"{if $properties['border-style'] === 'outset'} selected{/if}>outset</option>
                    <option value="initial"{if $properties['border-style'] === 'initial'} selected{/if}>initial</option>
                    <option value="inherit"{if $properties['border-style'] === 'inherit'} selected{/if}>inherit</option>
                </select> <span class="help-block">Style</span>
            </div>
            <div class="col-sm-4">
                <div class="input-group border-color-picker colorpicker-element">
                    <input type="text" class="form-control" name="border-color" id="border-color" value="{$properties['border-color']}">
                    <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                </div>
                <span class="help-block">Color</span>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            $('#config-modal-body .background-color-picker').colorpicker({
                format:'hex',
                colorSelectors: {
                    '#ffffff': '#ffffff',
                    '#777777': '#777777',
                    '#337ab7': '#337ab7',
                    '#5cb85c': '#5cb85c',
                    '#5bc0de': '#5bc0de',
                    '#f0ad4e': '#f0ad4e',
                    '#d9534f': '#d9534f',
                    '#000000': '#000000'
                }
            });
            $('#config-modal-body #background-color').click(function(){
                $('#config-modal-body .background-color-picker').colorpicker('show');
            });

            $('#config-modal-body .border-color-picker').colorpicker({
                format:'hex',
                colorSelectors: {
                    '#ffffff': '#ffffff',
                    '#777777': '#777777',
                    '#337ab7': '#337ab7',
                    '#5cb85c': '#5cb85c',
                    '#5bc0de': '#5bc0de',
                    '#f0ad4e': '#f0ad4e',
                    '#d9534f': '#d9534f',
                    '#000000': '#000000'
                }
            });
            $('#config-modal-body #border-color').click(function(){
                $('#config-modal-body .border-color-picker').colorpicker('show');
            });

            $('input[name="link-flag"]').click(function(){
                if ($(this).val() == 'yes'){
                    $('#url-link-container').show();
                }else{
                    $('#url-link-container').hide();
                }
            });

            $('input[name="button-icon-flag"]').click(function(){
                if ($(this).val() == 'yes'){
                    if ($('#config-modal-body #button-icon').val() == ''){
                        var first_icon = $("#config-modal-body  #font-icon-container i").first();
                        $('#config-modal-body #button-icon').val(first_icon.attr('class'));
                        $('#config-modal-body #span-button-icon').empty();
                        $('#config-modal-body #span-button-icon').append(first_icon.clone().addClass('fa-lg'));
                    }
                    $('#button-font-icon-container').show();
                }else{
                    $('#button-font-icon-container').hide();
                }
            });
            $("#config-modal-body  #font-icon-container i").click(function(){
                $('#config-modal-body #button-icon').val($(this).attr('class'));
                $('#config-modal-body #span-button-icon').empty();
                $('#config-modal-body #span-button-icon').append($(this).addClass('fa-lg'));
                $('#font-icon-container').toggle();
            });
        });
    </script>
</div>