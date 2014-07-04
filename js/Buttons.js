var Buttons = (function(jQuery){
    var getId = function (prefix) {
        var uniqueId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });

        return (prefix || '') + '-' + uniqueId;
    };

    var getElem = function (node, cssClass) {
        if (node && node.className && !!~node.className.indexOf(cssClass)) {
            return node;
        }

        if (node.parentNode) {
            return getElem(node.parentNode, cssClass);
        }

        return false;
    };

    var textboxCommand = function (ed, elem, cssClass, isVal) {
        var node = jQuery(ed.selection.getNode());
        if (!node.is('div')) {
            node = node.parents('div');
        }
        var selectedClass = jQuery(node).attr('class'),
            isbox;

        if (isVal) {

            var id = elem + getId(),
                texboxElem = ed.getDoc().createElement('DIV');

            texboxElem.id = id;
            texboxElem.className = cssClass;
            texboxElem.innerHTML = '<p class="placeholder">Add your annotation</p>';

            ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
            var activeElem = jQuery(ed.selection.getNode()).parents('div').attr('id');
            activeElem = activeElem.substring(0, activeElem.indexOf('-'));
            ed.controlManager.setActive(activeElem, true);
            var placeholders = tinyMCE.activeEditor.dom.select('p.placeholder');
            ed.selection.select(placeholders[placeholders.length - 1]);

        } else {

            if (selectedClass)isbox = selectedClass.indexOf('text_box') >= 0 || selectedClass.indexOf('annotate') >= 0;
            if (selectedClass == cssClass) {
                var texbox = getElem(ed.selection.getNode(), cssClass);
                if (texbox) {
                    if (jQuery(texbox).html() == '<p>&nbsp;</p>') {
                        jQuery(texbox).html('');
                        jQuery(texbox).remove();
                    } else {
                        var unwrapElem = jQuery(ed.selection.getNode());
                        unWrap(unwrapElem);
                    }
                    ed.controlManager.setActive(elem, false);
                    ed.selection.setCursorLocation(0);
                }
            } else {

                var content = '';
                if (!node.is('div')) {
                    content = ed.selection.getContent();
                } else if (isbox) {
                    content = jQuery(ed.selection.getNode()).parents('div').html();
                    jQuery(node).remove();
                }
                if (content == '') {
                    content = '<p>&nbsp;</p>';
                } else if (content.indexOf('<p>') < 0) {
                    content = '<p>' + content + '</p>';
                }
                if (selectedClass && isbox) {
                    ed.execCommand('mceRemoveNode', 0, node.id);
                }

                var id = elem + getId(),
                    texboxElem = ed.getDoc().createElement('DIV');

                texboxElem.id = id;
                texboxElem.className = cssClass;
                texboxElem.innerHTML = content;
                ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);

                var activeElem = jQuery(ed.selection.getNode()).parents('div').attr('id');
                activeElem = activeElem.substring(0, activeElem.indexOf('-'));
                ed.controlManager.setActive(activeElem, true);
            }
        }
    };

    var unWrap = function (elem) {
        if (elem.is('span'))
            elem = elem.parent('p');
        if (elem.parents().is('blockquote')) {
            elem = elem.parents('blockquote');
        }
        if (elem.index() == 0) {
            elem.parents('div').before(elem);
        } else if (elem.parents('div').children().length == elem.index() + 1) {
            elem.parents('div').after(elem);
        } else {
            var divhtml = jQuery(elem).html(),
                divElem = jQuery(elem);
            if (!divElem.is('div')) {
                divhtml = divElem.parents('div').html();
            }
            var myps = divhtml.split(elem.html());
            elem.parents('div').after(elem.parent('div').clone().html(myps[1])).html(myps[0]).after(elem);
            if (elem.prev().children('blockquote').html() == '')
                elem.prev().children('blockquote').remove();
        }
        if (elem.next().text() == '')
            elem.next().remove();
        if (elem.prev().text() == '')
            elem.prev().remove();
    };

    var markKeywords = function (ed){
        var highlightedElems=jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        highlightedElems.each(function(index,val){
            jQuery(val).replaceWith(this.childNodes);
        });
        var keywords=jQuery('#expresscurate_defined_tags').val().split(', '),
            matches;

        keywords.forEach(function(val){
            matches =ed.getBody();
            jQuery(matches).html(function(index, oldHTML) {
                return oldHTML.replace(new RegExp('(\\b'+val+'\\b)(?=[^>]*(<|$))','gi'), '<span class="expresscurate_keywordsHighlight">$&</span>');
            });
            if(jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length>0){
                ed.controlManager.setActive('markKeywords', true);
            }
        });
    };

    var setupButtons = function(){
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
                    title: 'Add Annotation (Ctrl + Up)',
                    cmd: 'annotation',
                    image: url + '/../images/annotate.png'
                });
                ed.addButton('righttextbox', {
                    title: 'Add Right-Box (Ctrl + R | Ctrl + Right)',
                    cmd: 'righttextbox',
                    image: url + '/../images/rightBox.png'
                });
                ed.addButton('justifytextbox', {
                    title: 'Add Center-Box (Ctrl + J | Ctrl + Down)',
                    cmd: 'justifytextbox',
                    image: url + '/../images/justifyBox.png'
                });
                ed.addButton('lefttextbox', {
                    title: 'Add Left-Box (Ctrl + L | Ctrl + Left)',
                    cmd: 'lefttextbox',
                    image: url + '/../images/leftBox.png'
                });
                ed.addButton('markKeywords', {
                    title: 'Highlight Keywords',
                    cmd: 'markKeywords',
                    class:'expresscurate_HighlightButton',
                    image: url + '/../images/markKeywords.png'
                });
                //  }

                ed.onKeyDown.add(function (ed, e) {
                    if (e.ctrlKey && (e.keyCode == 76 || e.keyCode == 37)) {     // ctrl+l|ctrl+left
                        e.returnValue = false;
                        textboxCommand(ed, 'lefttextbox', 'expresscurate_fl_text_box');
                        e.preventDefault();
                        return false;
                    } else if (e.ctrlKey && (e.keyCode == 82 || e.keyCode == 39)) {     // ctrl+r|ctrl+right
                        e.returnValue = false;
                        textboxCommand(ed, 'righttextbox', 'expresscurate_fr_text_box');
                        e.preventDefault();
                        return false;
                    } else if (e.ctrlKey && (e.keyCode == 74 || e.keyCode == 40)) {     // ctrl+j|ctrl+down
                        e.returnValue = false;
                        textboxCommand(ed, 'justifytextbox', 'expresscurate_justify_text_box');
                        e.preventDefault();
                        return false;
                    } else if (e.ctrlKey && e.keyCode == 38) {     // ctrl+up
                        e.returnValue = false;
                        textboxCommand(ed, 'annotation', 'expresscurate_annotate');
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
                });
                ed.onClick.add(function (ed, e) {
                    if (jQuery('.expresscurate_widget').length > 0) {
                        jQuery('.description  textarea').addClass('textareaBorder');
                        jQuery('.description  p , .description div').addClass('expresscurate_displayNone');
                        jQuery('.description').css({'background-color': '#ffffff'});
                    }
                });
                ed.onNodeChange.add(function (ed) {

                    var node, elem = jQuery(ed.selection.getNode());

                    if (elem.is('div')) {
                        node = elem;
                    } else {
                        node = elem.parents('div');
                    }

                    var cssClass = jQuery(node).attr('class'),
                        activeButton = ' ';
                    if (cssClass == 'expresscurate_fl_text_box')
                        activeButton = 'lefttextbox';
                    else if (cssClass == 'expresscurate_fr_text_box')
                        activeButton = 'righttextbox';
                    else if (cssClass == 'expresscurate_justify_text_box')
                        activeButton = 'justifytextbox';
                    else if (cssClass == 'expresscurate_annotate')
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
                        var highlightedElems=jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                        if(jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length<=0 && jQuery('#expresscurate_defined_tags').val().length>0){
                            if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
                                check_editor = setTimeout(function check() {
                                    clearTimeout(check_editor);
                                    highlightedElems=jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                                    if(highlightedElems.length>0){
                                        markKeywords(ed);
                                        setTimeout(check, 15000);
                                    }
                                }, 1);
                            }
                            markKeywords(ed);
                        }else{
                            highlightedElems.each(function(index,val){
                                jQuery(val).replaceWith(this.childNodes);
                            });
                            ed.controlManager.setActive('markKeywords', false);
                        }
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
        QTags.addButton('annotation', 'Annotation', '<div id="annotation' + getId() + '" class="expresscurate_annotate"><p>&nbsp;', '</p></div>', '', 'Add Annotation');
        QTags.addButton('lefttextbox', 'Left-Box', '<div id="lefttextbox' + getId() + '" class="expresscurate_fl_text_box"><p>&nbsp;', '</p></div>', '', 'Add Left-Box');
        QTags.addButton('justifytextbox', 'Center-Box', '<div id="justifytextbox' + getId() + '" class="expresscurate_justify_text_box"><p>&nbsp;', '</p></div>', '', 'Add Center-Box');
        QTags.addButton('righttextbox', 'Right-Box', '<div id="righttextbox' + getId() + '" class="expresscurate_fr_text_box"><p>&nbsp;', '</p></div>', '', 'Add Right-Box');


        // Register plugin
        tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
    };

    var isSetup = false;

    return{
        setup: function(){
            if(!isSetup){
                setupButtons();
                isSetup = true;
            }
        }
    }
})(window.jQuery);

Buttons.setup();