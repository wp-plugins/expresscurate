var Buttons = (function (jQuery) {
    /*var getId = function (prefix) {
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
    };*/

    var textboxCommand = function (ed, elem, cssClass, isVal) {
        var id = elem,
            texboxElem = ed.getDoc().createElement('DIV'),
            activeElem;
        if (isVal) {
            texboxElem.id = id;
            texboxElem.className = cssClass;
            texboxElem.innerHTML = '<p class="placeholder">Add your annotation</p>';

            ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
            activeElem = jQuery(ed.selection.getNode()).parents('div').eq(0).attr('id');
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
                texboxElem.id = id;
                texboxElem.className = cssClass;
                texboxElem.innerHTML = content;
                ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
                activeElem = id;

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

    var noFollow = function (ed) {
        var elem = jQuery(ed.selection.getNode());
        if (elem.is('a')) {
            if (elem.attr('rel') == 'nofollow') {
                elem.removeAttr('rel');
                ed.controlManager.setActive('noFollow', true);
            } else {
                elem.attr('rel', 'nofollow');
                ed.controlManager.setActive('noFollow', false);
            }
        }
    };
    var wordCount = function (ed) {
        var lengthMessage, lengthColor;

        var content = ((jQuery('#content').css("display") == "block") ? jQuery('#content').val() : tinyMCE.get("content").getContent()),
            wordsCount = /*jQuery('#wp-word-count .word-count').text() ||*/ SEOControl.words_in_text(content).length;
        if (wordsCount < 700) {
            lengthColor = 'red';
            lengthMessage = 'Your post is currently ' + wordsCount + ' word long.  The optimal post length is 700-1,600 words.';
        } else if (wordsCount >= 700 && wordsCount <= 1600) {
            lengthColor = 'green';
            lengthMessage = 'Post length is ' + wordsCount + '. Good work! (The recommended length is 700-1600 words).';
        } else {
            lengthColor = 'red';
            lengthMessage = 'The post has ' + wordsCount + ' words, which is longer than the recommended 700-1600 word.';
        }

        if (jQuery(content).is('blockquote')) {
            var div = document.createElement('div');
            div.innerHTML = content;
            var blockquotes = jQuery(div).find('blockquote'),
                wordsInBlockquotes = 0;
            blockquotes.each(function (index, val) {
                wordsInBlockquotes += SEOControl.words_in_text(jQuery(val).text()).length;
            });
            var quotationPersent = Math.round((wordsInBlockquotes / wordsCount) * 100),
                quotationMessage = (quotationPersent > 20) ? "The quotation from the original source currently constitutes " + quotationPersent + "% your post.  Anything over 20% can be considered lower quality content." : "Good work! There is no more than 20% quotation used in the post.",
                quotationColor = (quotationPersent > 20) ? 'red' : 'green';

        } else {
            quotationColor = 'blue';
            quotationMessage = 'There is no quotation.';
        }
        if (jQuery(content).is('blockquote') && quotationPersent == 0) {
            quotationColor = 'blue';
            quotationMessage = 'There is no quotation.';
        }

        var messageHtml='<p class="lengthSuggestion ' + lengthColor + '">' + lengthMessage + '</p>\
                                    <p class="lengthSuggestion  ' + quotationColor + '">' + quotationMessage + '</p>';


        var imagesInPost =jQuery(content).find('img').length ? true: false,
            videoInPost=tinyMCE.get('content').getContent().indexOf('[embed]')>-1;
        messageHtml+=(!imagesInPost && !videoInPost)? '<p class="lengthSuggestion red">Your post currently doesn’t have an image(video). Adding a media is a good way to improve conversion rates by creating visual associations with your posts.</p>': '';
        messageHtml +=jQuery('.attachment-post-thumbnail').length ? '' :'<p class="lengthSuggestion red">Your post currently doesn’t have a featured image. Adding a featured image is a good way to improve conversion rates by creating visual associations with your posts.</p>';

            ed.windowManager.open({
                title: 'Post Analysis',
                id: 'expresscurate_wordCount_dialog',
                width: 450,
                html: messageHtml
            });
    };
    var addKeyword =function(){
        if(tinymce.activeEditor.selection.getContent().length > 3 && jQuery('#expresscurate_widget').length){
            var keyword = tinymce.activeEditor.selection.getContent(),
                input=jQuery('.addKeywords input');
            keyword = keyword.replace(/<[^>]+>[^<]*<[^>]+>|<[^\/]+\/>/ig, "");
            input.val(keyword);
            SEOControl.insertKeywordInWidget(KeywordUtils.multipleKeywords(input, undefined), jQuery('.addKeywords'));
        }
    };
    var setupButtons = function () {
        jQuery('html').on('click','.expresscurate_postAnalysis',function(){
           wordCount(tinymce.activeEditor);
        });
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
                    classes: "btn expresscurateCostom expresscurateAnnotate"
                });
                ed.addButton('righttextbox', {
                    title: 'Add Right-Box (Alt + R | Ctrl + Right)',
                    cmd: 'righttextbox',
                    classes: "btn expresscurateCostom expresscurateRightbox"
                });
                ed.addButton('justifytextbox', {
                    title: 'Add Center-Box (Alt + J | Ctrl + Down)',
                    cmd: 'justifytextbox',
                    classes: "btn expresscurateCostom expresscurateJustify"
                });
                ed.addButton('lefttextbox', {
                    title: 'Add Left-Box (Alt + L | Ctrl + Left)',
                    cmd: 'lefttextbox',
                    classes: "btn expresscurateCostom expresscurateLeftbox"
                });
                ed.addButton('markKeywords', {
                    title: 'Highlight Keywords (Alt + H)',
                    cmd: 'markKeywords',
                    classes: "btn expresscurateCostom expresscurateMarkKeywords"
                });
                ed.addButton('noFollow', {
                    title: 'Follow / No Follow (Alt + F)',
                    cmd: 'noFollow',
                    classes: "btn expresscurateCostom expresscurateFollow"
                });
                ed.addButton('wordCount', {
                    title: 'Post Analysis (Alt + W)',
                    cmd: 'wordCount',
                    classes: "btn expresscurateCostom expresscurateWordCount"
                });
                ed.addButton('addKeyword', {
                    title: 'Add Keyword (Alt + K)',
                    cmd: 'addKeyword',
                    classes: "btn expresscurateCostom expresscurateAddKeyword"
                });
                //  }

                ed.onKeyDown.add(function (ed, e) {
                    if (e.altKey && e.keyCode == 75) {
                        addKeyword();
                    }else if ((e.altKey && e.keyCode == 76) || (e.ctrlKey && e.keyCode == 37)) {     // alt+l|Ctrl+left
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
                    } else if (e.altKey && e.keyCode == 70) {     // alt+f
                        e.returnValue = false;
                        noFollow(ed);
                        e.preventDefault();
                        return false;
                    } else if (e.altKey && e.keyCode == 87) {     // alt+w
                        e.returnValue = false;
                        wordCount(ed);
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
                        jQuery('.description').removeClass('active');
                    }
                });
                ed.onNodeChange.add(function (ed) {
                    ed.controlManager.setActive('noFollow', false);
                    var node, elem = jQuery(ed.selection.getNode());
                    node = elem;

                    var nodeIsWrapped = jQuery(node).parents('div[class*=expresscurate]').length > 0 || jQuery(node).parents('div[class*=annotat]').length>0;
                    if (nodeIsWrapped) {
                        node = jQuery(node).parents('div[class*=expresscurate]')  || jQuery(node).parents('div[class*=annotat]');
                    }
                    if (elem.is('a')) {
                        if (elem.attr('rel') == 'nofollow') {
                            ed.controlManager.setActive('noFollow', false);
                        } else {
                            ed.controlManager.setActive('noFollow', true);
                        }
                    }
                    ed.controlManager.setDisabled('noFollow', ed.selection.getNode().nodeName != 'A');

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
                    ed.addCommand('noFollow', function () {
                        noFollow(ed);
                    });
                    ed.addCommand('wordCount', function () {
                        wordCount(ed);
                    });
                    ed.addCommand('addKeyword', function () {
                        addKeyword();
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

    return {
        setup: function () {
            if (!isSetup) {
                setupButtons();
                isSetup = true;
            }
        }
    }
})
(window.jQuery);

Buttons.setup();