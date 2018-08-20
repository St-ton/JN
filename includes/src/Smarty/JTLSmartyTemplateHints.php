<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Smarty;


/**
 * Class JTLSmartyTemplateHints
 */
class JTLSmartyTemplateHints extends JTLSmartyTemplateClass
{
    /**
     * Runtime function to render sub-template
     *
     * @param string  $template template name
     * @param mixed   $cache_id cache id
     * @param mixed   $compile_id compile id
     * @param integer $caching cache mode
     * @param integer $cache_lifetime life time of cache data
     * @param array   $data passed parameter template variables
     * @param int     $scope scope in which {include} should execute
     * @param bool    $forceTplCache cache template object
     * @param string  $uid file dependency uid
     * @param string  $content_func function name
     *
     */
    public function _subTemplateRender(
        $template,
        $cache_id,
        $compile_id,
        $caching,
        $cache_lifetime,
        $data,
        $scope,
        $forceTplCache,
        $uid = null,
        $content_func = null
    ) {
        $tplName = null;
        $tplID   = null;
        $tplName = \strpos($template, ':') !== false
            ? \substr($template, \strpos($template, ':') + 1)
            : $template;
        if (\SHOW_TEMPLATE_HINTS === 1) {
            echo '<!-- start ' . $tplName . '-->';
        } elseif (\SHOW_TEMPLATE_HINTS === 2) {
            if ($tplName !== 'layout/header.tpl') {
                echo '<section class="tpl-debug">';
                echo '<span class="badge tpl-name">' . $tplName . '</span></section>';
            }
        } elseif (\SHOW_TEMPLATE_HINTS === 3) {
            if ($tplName !== 'layout/header.tpl') {
                echo '<section class="tpl-debug">';
                echo '<span class="badge tpl-name">' . $tplName . '</span>';
            }
        } elseif (\SHOW_TEMPLATE_HINTS === 4) {
            $tplID = \uniqid('tpl');
            if ($tplName !== 'layout/header.tpl' && $tplName !== 'layout/header_custom.tpl') {
                echo '<span class="tpl-debug-start" data-uid="' . $tplID . '" style="display:none;" data-tpl="' . $tplName . '">';
                echo '<span class="tpl-name">' . $tplName . '</span>';
                echo '</span>';
            }
        }
        parent::_subTemplateRender(
            $this->smarty->getResourceName($template),
            $cache_id,
            $compile_id,
            $caching,
            $cache_lifetime,
            $data,
            $scope,
            $forceTplCache,
            $uid,
            $content_func
        );
        if (\SHOW_TEMPLATE_HINTS === 1) {
            echo '<!-- end ' . $tplName . '-->';
        } elseif (\SHOW_TEMPLATE_HINTS === 2 && $tplName === 'layout/header.tpl' && $tplName === 'layout/header_custom.tpl') {
            echo '<style>
                    .tpl-debug{border:1px dashed black;position:relative;min-height:25px;opacity:.75;z-index:9;}
                    .tpl-name{position:absolute;left:0;}
                </style>';
        } elseif (\SHOW_TEMPLATE_HINTS === 3) {
            if ($tplName !== 'layout/header.tpl') {
                echo '</section>';
            } else {
                echo
                '<style>
                    .tpl-debug{border:1px dashed black;position:relative;min-height:25px;z-index:9;overflow:hidden;}
                    .tpl-name{position:relative;left:0;min-height:25px;opacity:.75;}
                </style>';
            }
        } elseif (\SHOW_TEMPLATE_HINTS === 4) {
            if ($tplName !== 'layout/header.tpl' && $tplName !== 'layout/header_custom.tpl') {
                echo '<span class="tpl-debug-end" data-uid="' . $tplID . '" style="display:none"></span>';
            } else {
                echo
                '<style>
                        .tpl-debug-start{position:absolute;z-index:9;}
                        .tpl-name{position:relative;left:0;min-height:25px;opacity:.75;}
                        .bounding-box{border:1px dashed black;pointer-events:none;}
                    </style>';
                echo "<script type=\"text/javascript\">
                function getBoundingBoxes() {
                    $('.bounding-box').remove();
                    $('.tpl-debug-start').each(function(){
                        var elem = $(this),
                            boxElem;
                        uid  = elem.attr('data-uid'),
                        tpl  = elem.attr('data-tpl'),
                        next = elem.nextUntil('.tpl-debug-end[data-uid=\"' + uid +'\"]'),
                        box  = {
                            left: 999999,
                            right: 0,
                            top: 999999,
                            bottom: 0
                        };
                                
                        next.each(function(i, c) {
                            var bb, 
                                elem = $(c);
                            if (elem.css('display') === 'block' && elem.css('visibility') === 'visible') {
                                bb = c.getBoundingClientRect();
                                box = {
                                    left: Math.min(box.left, bb.left),
                                    right: Math.max(box.right, bb.right),
                                    top: Math.min(box.top, bb.top),
                                    bottom: Math.max(box.bottom, bb.bottom)
                                };
                            }
                        });
                         boxElem = $('<div class=bounding-box></div>');
                         boxElem.html('<span class=\"tpl-name badge\">' + tpl + '</span>')
                             .css('position', 'fixed')
                             .css('top', box.top + 'px')
                             .css('left', box.left + 'px')
                             .css('width', (box.right-box.left)  + 'px')
                             .css('height', (box.bottom-box.top) + 'px');
                         $('body').append(boxElem);
                    });
                }
                $(document).ready(function () {
                    getBoundingBoxes();                
                    $(window).scroll(getBoundingBoxes).resize(getBoundingBoxes);
                });
                </script>";
            }
        }
    }
}
