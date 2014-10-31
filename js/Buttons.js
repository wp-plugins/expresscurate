var Buttons = (function (jQuery) {
    var textboxCommand = function (ed, elem, cssClass, isVal) {
        if (isVal) {
            var id = elem,
                texboxElem = ed.getDoc().createElement('DIV');

            texboxElem.id = id;
            texboxElem.className = cssClass;
            texboxElem.innerHTML = '<p class="placeholder">Add your annotation</p>';

            ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
            var activeElem = jQuery(ed.selection.getNode()).parents('div').eq(0).attr('id');
            ed.controlManager.setActive(activeElem, true);
            var placeholders = tinyMCE.activeEditor.dom.select('p.placeholder');
            ed.selection.select(placeholders[placeholders.length - 1]);

        } else {
            var node = jQuery(ed.selection.getNode()),
                selectedId = jQuery(node).parents('div[class*=expresscurate]').attr('id') || jQuery(node).parents('div[class*=annotat]').attr('id'),
                nodeIsBox = jQuery(node).is('div') && jQuery(node).attr('class') && (jQuery(node).attr('class').indexOf('expresscurate') > -1 || jQuery(node).attr('class').indexOf('annotat') > -1),
                nodeIsWrapped = jQuery(node).parents('div[class*=expresscurate]').length > 0 || jQuery(node).parents('div[class*=annotat]').length>0;

            if (node.is('div') && jQuery(node).attr('class') && (jQuery(node).attr('class').indexOf('expresscurate')>-1 || jQuery(node).attr('class').indexOf('annotat')>-1) && jQuery(node).attr('id')==elem) {
                node.before(node.html());
                node.remove();
                       } else if (nodeIsWrapped && selectedId == elem) {
                if (jQuery(node).html() == '<p>&nbsp;</p>') {
                    jQuery(node).html('');
                    jQuery(node).remove();
                    } else {
                    unWrap(node, elem);
                    }
                    ed.controlManager.setActive(elem, false);
                    ed.selection.setCursorLocation(0);

            } else {
                var content = '';
                if (nodeIsBox) {
                    content = node.html();
                    node.remove();
                } else if (nodeIsWrapped) {
                    content = node.parents('div').eq(0).html();
                    node.parents('div').eq(0).remove();
                } else {
                    content = ed.selection.getContent();
                }

                var id = elem,
                    texboxElem = ed.getDoc().createElement('DIV');

                texboxElem.id = id;
                texboxElem.className = cssClass;
                texboxElem.innerHTML = content;
                ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
                var activeElem = id;

                ed.controlManager.setActive(activeElem, true);
            }
        }
    };

    var unWrap = function (elem, elemId) {
        var wrapper = elem.parents('div#' + elemId + '');
        if (elem.index() == 0) {
            wrapper.before(elem);
        } else if (wrapper.children().length == elem.index() + 1) {
            wrapper.after(elem);
        } else {
            var divhtml = wrapper.html(),
                divElem = jQuery(elem);

            var myps = divhtml.split(elem.html());
            wrapper.after(wrapper.clone().html(myps[1])).html(myps[0]).after(elem);
            if (elem.prev().children('blockquote').html() == '')
                elem.prev().children('blockquote').remove();
        }
        if (elem.next().text() == '')
            elem.next().remove();
        if (elem.prev().text() == '')
            elem.prev().remove();
    };
    var markKeywordsCommand = function (ed) {
        var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0 && jQuery('#expresscurate_defined_tags').val().length > 0) {
            if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
                check_editor = setTimeout(function check() {
                    clearTimeout(check_editor);
                    highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                    if (highlightedElems.length > 0) {
                        markKeywords(ed);
                        setTimeout(check, 15000);
                    }
                }, 1);
            }
            markKeywords(ed);
        } else {
            highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
            ed.controlManager.setActive('markKeywords', false);
        }
    }
    var markKeywords = function (ed){
        var bookmark = ed.selection.getBookmark(2, true),
            colors=['Red','Blue','Green','Orange','LightBlue','Yellow','LightGreen'],
            highlightedElems=jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        highlightedElems.each(function(index,val){
            jQuery(val).replaceWith(this.childNodes);
        });
        var keywords = jQuery('#expresscurate_defined_tags').val().split(', '),
            matches = ed.getBody(),
            i=0;
        keywords.forEach(function(val){
            jQuery(matches).html(function(index, oldHTML) {
                return oldHTML.replace(new RegExp('((^|\\s|>|))(' + val + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi'), '$2<span class="expresscurate_keywordsHighlight expresscurate_highlight'+colors[i%7]+'">$3</span>');
            });
            if(jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length>0){
                ed.controlManager.setActive('markKeywords', true);
            }
            i++;
        });
        ed.selection.moveToBookmark(bookmark);
    };

    var setupButtons = function () {
        tinymce.create('tinymce.plugins.expresscurate', {
            /**
             * Initializes the plugin, this will be executed after the plugin has been created.
             * This call is done before the editor instance has finished it's initialization so use the onInit event
             * of the editor instance to intercept that event.
             *
             * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
             * @param {string} url Absolute URL to where the plugin is located.
             */

            init: function (ed, url) {
                // if(ed.id != 'expresscurate_insight_editor' && ed.id != 'expresscurate_content_editor') {
                //if (ed.id != 'expresscurate_content_editor') {
                ed.addButton('annotation', {
                    title: 'Add Annotation (Alt + A | Ctrl + Up)',
                    cmd: 'annotation',
                    image: url + '/../images/annotate.png'
                });
                ed.addButton('righttextbox', {
                    title: 'Add Right-Box (Alt + R | Ctrl + Right)',
                    cmd: 'righttextbox',
                    image: url + '/../images/rightBox.png'
                });
                ed.addButton('justifytextbox', {
                     title: 'Add Center-Box (Alt + J | Ctrl + Down)',
                   cmd: 'justifytextbox',
                    image: url + '/../images/justifyBox.png'
                });
                ed.addButton('lefttextbox', {
                     title: 'Add Left-Box (Alt + L | Ctrl + Left)',
                   cmd: 'lefttextbox',
                    image: url + '/../images/leftBox.png'
                });
                ed.addButton('markKeywords', {
                    title: 'Highlight Keywords (Alt + H)',
                    cmd: 'markKeywords',
                    class: 'expresscurate_HighlightButton',
                    image: url + '/../images/markKeywords.png'
                });
                //  }

                ed.onKeyDown.add(function (ed, e) {
                    if ((e.altKey && e.keyCode == 76) || (e.ctrlKey && e.keyCode == 37)) {     // alt+l|ctrl+left
                        e.returnValue = false;
                        textboxCommand(ed, 'lefttextbox', 'expresscurate_fl_text_box');
                        e.preventDefault();
                        return false;
                    } else if ((e.altKey && e.keyCode == 82 ) || (e.ctrlKey && e.keyCode == 39)) {     // alt+r|Ctrl+right
                        e.returnValue = false;
                        textboxCommand(ed, 'righttextbox', 'expresscurate_fr_text_box');
                        e.preventDefault();
                        return false;
                    } else if ((e.altKey && e.keyCode == 74 ) || (e.ctrlKey && e.keyCode == 40)) {     // alt+j|Ctrl+down
                        e.returnValue = false;
                        textboxCommand(ed, 'justifytextbox', 'expresscurate_justify_text_box');
                        e.preventDefault();
                        return false;
                    } else if ((e.altKey && e.keyCode == 65) || (e.ctrlKey && e.keyCode == 38)) {     // alt+a |Ctrl+up
                        e.returnValue = false;
                        textboxCommand(ed, 'annotation', 'expresscurate_annotate');
                        e.preventDefault();
                        return false;
                    } else if (e.altKey && e.keyCode == 72) {     // alt+h
                        e.returnValue = false;
                        Keywords.markEditorKeywords();
                        e.preventDefault();
                        return false;
                    }
                });

                ed.onLoadContent.add(function (ed, o) {
                    var dom = tinymce.activeEditor.dom;
                    var divElements = dom.select('div[class*=expresscurate]');
                    dom.setStyle(divElements, 'height', 'auto');

                    // if(ed.id == 'expresscurate_insight_editor' || ed.id == 'expresscurate_content_editor') {
                    if (ed.id == 'expresscurate_content_editor') {
                        ed.controlManager.buttons && ed.controlManager.buttons.blockquote && ed.controlManager.buttons.blockquote.remove() ||
                        ed.controlManager.controls && ed.controlManager.controls.content_blockquote && ed.controlManager.controls.content_blockquote.remove();
                    }
                    jQuery(dom.select('div[id*=textbox]')).each(function (index, val) {
                        var id = jQuery(val).attr('id');
                        if (id.indexOf('-') > -1) {
                            id = id.substring(0, id.indexOf('-'));
                            jQuery(val).attr('id', id);
                        }
                });
                    jQuery(dom.select('div[id*=annotation]')).each(function (index, val) {
                        var id = jQuery(val).attr('id');
                        if (id.indexOf('-') > -1) {
                            id = id.substring(0, id.indexOf('-'));
                            jQuery(val).attr('id', id);
                        }
                    });
                });
                ed.onClick.add(function (ed, e) {
                    if (jQuery('.expresscurate_widget').length > 0) {
                        jQuery('.description  .descriptionWrap').addClass('textareaBorder');
                        jQuery('.description  p , .description .hint').addClass('expresscurate_displayNone');
                        jQuery('.description').css({'background-color': '#ffffff'});
                    }
                });
                ed.onNodeChange.add(function (ed) {

                    var node, elem = jQuery(ed.selection.getNode());
                    node = elem;

                    var nodeIsWrapped = jQuery(node).parents('div[class*=expresscurate]').length > 0 || jQuery(node).parents('div[class*=annotat]').length>0;
                    if (nodeIsWrapped) {
                        node = jQuery(node).parents('div[class*=expresscurate]')  || jQuery(node).parents('div[class*=annotat]');
                    }

                    var cssClass = jQuery(node).attr('class'),
                        activeButton = ' ';
                    if (cssClass == 'expresscurate_fl_text_box')
                        activeButton = 'lefttextbox';
                    else if (cssClass == 'expresscurate_fr_text_box')
                        activeButton = 'righttextbox';
                    else if (cssClass == 'expresscurate_justify_text_box')
                        activeButton = 'justifytextbox';
                    else if (cssClass == 'expresscurate_annotate' || cssClass == 'annotate')
                        activeButton = 'annotation';
                    ed.controlManager.setActive('lefttextbox', false);
                    ed.controlManager.setActive('righttextbox', false);
                    ed.controlManager.setActive('justifytextbox', false);
                    ed.controlManager.setActive('annotation', false);
                    if (activeButton != ' ')
                        ed.controlManager.setActive(activeButton, true);
                });

                // if(ed.id != 'expresscurate_insight_editor' && ed.id != 'expresscurate_content_editor') {
                if (ed.id != 'expresscurate_content_editor') {
                    //lefttextbox
                    ed.addCommand('lefttextbox', function () {
                        textboxCommand(ed, 'lefttextbox', 'expresscurate_fl_text_box');
                    });
                    //righttextbox
                    ed.addCommand('righttextbox', function () {
                        textboxCommand(ed, 'righttextbox', 'expresscurate_fr_text_box');
                    });
                    //justifytextbox
                    ed.addCommand('justifytextbox', function () {
                        textboxCommand(ed, 'justifytextbox', 'expresscurate_justify_text_box');
                    });
                    //annotation
                    ed.addCommand('annotation', function (ui, val) {
                        textboxCommand(ed, 'annotation', 'expresscurate_annotate', val);
                    });
                    ed.addCommand('markKeywords', function () {
                        Keywords.markEditorKeywords();
                    });
                }
            },
            /**
             * Creates control instances based in the incomming name. This method is normally not
             * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
             * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
             * method can be used to create those.
             *
             * @param {String} n Name of the control to create.
             * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
             * @return {tinymce.ui.Control} New control instance or null if no control was created.
             */
            createControl: function (n, cm) {
                return null;
            },
            /**
             * Returns information about the plugin as a name/value array.
             * The current keys are longname, author, authorurl, infourl and version.
             *
             * @return {Object} Name/value array containing information about the plugin.
             */
            getInfo: function () {
                return {
                    longname: 'Express curate Buttons',
                    author: 'ExpressCurate',
                    version: "0.1"
                };
            }
        });
        QTags.addButton('annotation', 'Annotation', '<div id="annotation" class="expresscurate_annotate"><p>&nbsp;', '</p></div>', '', 'Add Annotation');
        QTags.addButton('lefttextbox', 'Left-Box', '<div id="lefttextbox" class="expresscurate_fl_text_box"><p>&nbsp;', '</p></div>', '', 'Add Left-Box');
        QTags.addButton('justifytextbox', 'Center-Box', '<div id="justifytextbox" class="expresscurate_justify_text_box"><p>&nbsp;', '</p></div>', '', 'Add Center-Box');
        QTags.addButton('righttextbox', 'Right-Box', '<div id="righttextbox" class="expresscurate_fr_text_box"><p>&nbsp;', '</p></div>', '', 'Add Right-Box');


        // Register plugin
        tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
    };

    var isSetup = false;

    return{
        setup: function () {
            if (!isSetup) {
                setupButtons();
                isSetup = true;
            }
        }
    }
})(window.jQuery);

Buttons.setup();