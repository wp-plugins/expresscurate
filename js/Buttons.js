var ExpressCurateButtons = (function ($) {
    function textboxCommand(ed, elem, cssClass, isVal) {
        var id = elem,
            $textboxElem,
            $activeElem,
            $node = $(ed.selection.getNode()),
            selectedId = $node.parents('div[class*=expresscurate]').attr('id') || $node.parents('div[class*=annotat]').attr('id'),
            nodeIsBox = $node.is('div') && $node.attr('class') && ($node.attr('class').indexOf('expresscurate') > -1 || $node.attr('class').indexOf('annotat') > -1),
            nodeIsWrapped = $node.parents('div[class*=expresscurate]').length > 0 || $node.parents('div[class*=annotat]').length > 0,
            content = '';

        ExpressCurateUtils.track('/post/content/text-boxes');

        if (isVal) {
            $textboxElem = $('<div/>', {
                id: id,
                addClass: cssClass,
                html: '<p class="placeholder">Add your annotation</p>'
            });

            ed.execCommand('mceInsertContent', true, $textboxElem.wrap('<span/>').parent().html());
            $activeElem = $(ed.selection.getNode()).parents('div').eq(0).attr('id');
            ed.controlManager.setActive($activeElem, true);
            var $placeholders = tinyMCE.activeEditor.dom.select('p.placeholder');
            ed.selection.select($placeholders[$placeholders.length - 1]);
        } else {
            if ($node.is('div') && $node.attr('class') && ($node.attr('class').indexOf('expresscurate') > -1 || $node.attr('class').indexOf('annotat') > -1) && $node.attr('id') === elem) {
                $node.before($node.html());
                $node.remove();
            } else if (nodeIsWrapped && selectedId === elem) {
                if ($node.html() === '<p>&nbsp;</p>') {
                    $node.html('');
                    $node.remove();
                } else {
                    unWrap($node, elem);
                }
                ed.controlManager.setActive(elem, false);
                ed.selection.setCursorLocation(0);
            } else {
                if (nodeIsBox) {
                    content = $node.html();
                    $node.remove();
                } else if (nodeIsWrapped) {
                    content = $node.parents('div').eq(0).html();
                    $node.parents('div').eq(0).remove();
                } else {
                    content = ed.selection.getContent();
                }
                $textboxElem = $('<div/>', {
                    id: id,
                    addClass: cssClass,
                    html: content
                });
                ed.execCommand('mceInsertContent', true, $textboxElem.wrap('<span/>').parent().html());
                $activeElem = id;
                ed.controlManager.setActive($activeElem, true);
            }
        }
    }

    function unWrap(elem, elemId) {
        var $wrapper = elem.parents('div#' + elemId + '');
        if (elem.index() === 0) {
            $wrapper.before(elem);
        } else if ($wrapper.children().length === elem.index() + 1) {
            $wrapper.after(elem);
        } else {
            var divhtml = $wrapper.html();
            var myps = divhtml.split(elem.html());
            $wrapper.after($wrapper.clone().html(myps[1])).html(myps[0]).after(elem);
            if (elem.prev().children('blockquote').html() === '') {
                elem.prev().children('blockquote').remove();
            }
        }
        if (elem.next().text() === '') {
            elem.next().remove();
        }
        if (elem.prev().text() === '') {
            elem.prev().remove();
        }
    }

    function noFollow(ed) {
        var $elem = $(ed.selection.getNode());
        if ($elem.is('a')) {
            if ($elem.attr('rel') === 'nofollow') {
                $elem.removeAttr('rel');
                ed.controlManager.setActive('noFollow', true);
            } else {
                $elem.attr('rel', 'nofollow');
                ed.controlManager.setActive('noFollow', false);
            }
        }

        ExpressCurateUtils.track('/post/content/seo/follow');
    }

    function getInfo() {
        return {
            longname: "Recent Posts",
            author: 'Konstantinos Kouratoras',
            authorurl: 'http://www.kouratoras.gr',
            infourl: 'http://www.smashingmagazine.com',
            version: "1.0"
        };
    }

    function wordCount(ed) {
        ExpressCurateUtils.track('/post/content/seo/analytics');

        var lengthMessage, lengthColor,
            $contentWrap = $('#content'),
            content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
            wordsCount = ExpressCurateSEOControl.wordsInText(content).length,
            messageHtml;


        switch (true) {
            case wordsCount < 700:
                lengthColor = 'red';
                lengthMessage = 'Your post is currently ' + wordsCount + ' word long.  The optimal post length is 700-1,600 words.';
                break;
            case (wordsCount >= 700 && wordsCount <= 1600):
                lengthColor = 'green';
                lengthMessage = 'Post length is ' + wordsCount + '. Good work! (The recommended length is 700-1600 words).';
                break;
            case wordsCount > 1600:
                lengthColor = 'red';
                lengthMessage = 'The post has ' + wordsCount + ' words, which is longer than the recommended 700-1600 word.';
                break;
        }

        if ($(content).is('blockquote')) {
            var $div = $('<div/>', {
                html: content
            });
            var $blockquotes = $div.find('blockquote'),
                wordsInBlockquotes = 0;
            $blockquotes.each(function (index, val) {
                wordsInBlockquotes += ExpressCurateSEOControl.wordsInText($(val).text()).length;
            });
            var quotationPersent = Math.round((wordsInBlockquotes / wordsCount) * 100),
                quotationMessage = (quotationPersent > 20) ? "The quotation from the original source currently constitutes " + quotationPersent + "% your post.  Anything over 20% can be considered lower quality content." : "Good work! There is no more than 20% quotation used in the post.",
                quotationColor = (quotationPersent > 20) ? 'red' : 'green';
        } else {
            quotationColor = 'blue';
            quotationMessage = 'There is no quotation.';
        }
        if ($(content).is('blockquote') && quotationPersent == 0) {
            quotationColor = 'blue';
            quotationMessage = 'There is no quotation.';
        }
        messageHtml = '<p class="lengthSuggestion ' + lengthColor + '">' + lengthMessage + '</p>\
         <p class="lengthSuggestion  ' + quotationColor + '">' + quotationMessage + '</p>';


        var imagesInPost = $(content).find('img').length ? true : false,
            videoInPost = tinyMCE.get('content').getContent().indexOf('[embed]') > -1;
        messageHtml += (!imagesInPost && !videoInPost) ? '<p class="lengthSuggestion red">Your post currently doesn’t have an image(video). Adding a media is a good way to improve conversion rates by creating visual associations with your posts.</p>' : '';
        messageHtml += $('.attachment-post-thumbnail').length ? '' : '<p class="lengthSuggestion red">Your post currently doesn’t have a featured image. Adding a featured image is a good way to improve conversion rates by creating visual associations with your posts.</p>';

        ed.windowManager.open({
            title: 'Post Analysis',
            id: 'expresscurate_wordCount_dialog',
            width: 450,
            html: messageHtml
        });
    }

    function addKeyword() {
        if (tinymce.activeEditor.selection.getContent().length > 3 && $('#expresscurate_widget').length) {
            var keyword = tinymce.activeEditor.selection.getContent(),
                $input = $('.addKeywords input');
            keyword = keyword.replace(/<[^>]+>[^<]*<[^>]+>|<[^\/]+\/>/ig, "");
            $input.val(keyword);
            ExpressCurateSEOControl.insertKeywordInWidget(ExpressCurateKeywordUtils.multipleKeywords($input, undefined), $('.addKeywords'));
        }
    }

    function setupButtons() {

        $('html').on('click', '.expresscurate_postAnalysis', function () {
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

            visualizeShortcode: function (co) {
                return co.replace(/\[facebook([^\]]*)\]/g, function (a, b) {
                    return '<img src="" class="expresscurate_FacebookEmbed" title="facebook' + tinymce.DOM.encode(b) + '" />';
                });
            },

            recoverShortcode: function (co) {
                function getAttr(s, n) {
                    n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
                    return n ? tinymce.DOM.decode(n[1]) : '';
                }

                return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function (a, im) {
                    var cls = getAttr(im, 'class');
                    if (cls.indexOf('expresscurate_FacebookEmbed') != -1){
                        return '<p>[' + tinymce.trim(getAttr(im, 'title')) + ']</p>';
                    }
                    return a;
                });
            },
            init: function (ed, url) {
               /* var t = this;
                t.url = url;
                //replace shortcode before editor content set
                ed.onBeforeSetContent.add(function (ed, o) {
                    o.content = t.visualizeShortcode(o.content);
                });
                //replace shortcode as its inserted into editor
                ed.onExecCommand.add(function (ed, cmd) {
                    if (cmd === 'mceInsertContent') {
                        tinyMCE.activeEditor.setContent(t.visualizeShortcode(tinyMCE.activeEditor.getContent()));
                    }
                });
                //replace the image back to shortcode on save
                ed.onPostProcess.add(function (ed, o) {
                    if (o.get)
                        o.content = t.recoverShortcode(o.content);
                });*/

                // Register buttons - trigger above command when clicked
                ed.addButton('sochalPost', {
                    title: 'Insert social post',
                    cmd: 'sochalPost',
                    classes: "btn expresscurateCostom expresscurateAnnotate"
                });
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
               /* ed.addButton('sochalPost', {
                    title: 'Add post',
                    icon: 'icon expresscurateSocial mce-expresscurateCostom',
                    type: 'menubutton',
                    cmd: 'sochalPost',
                    menu: [
                        {
                            text: 'facebook',
                            icon: 'icon expresscurateSocial mce-expresscurateCostom',
                            onclick: function () {
                                ed.windowManager.open({
                                    title: 'Facebook embed',
                                    body: [{
                                        type: 'textbox',
                                        name: 'facebookEmbed'
                                    }],
                                    onsubmit: function (e) {
                                        var $elem=$(e.data.facebookEmbed)[2],
                                            url=$($elem).data('href');
                                        ed.insertContent('[facebook src="'+url+'"]');
                                    }
                                });
                            }
                        },
                        {
                            text: 'twitter',
                            icon: 'icon expresscurateSocial mce-expresscurateCostom',
                            onclick: function () {
                                ed.windowManager.open({
                                    title: 'Twitter embed',
                                    body: [{
                                        type: 'textbox',
                                        name: 'twitterEmbed'
                                    }],
                                    onsubmit: function (e) {
                                        var $elem=$(e.data.twitterEmbed)[0],
                                            url=$($elem).find('> a').attr('href');
                                        ed.insertContent('[embed]'+url+'[/embed]');
                                    }
                                });
                            }
                        }
                    ]
                });*/
                ed.onKeyDown.add(function (ed, e) {
                    if (e.altKey && e.keyCode === 75) {
                        addKeyword();
                    }
                    if ((e.altKey && e.keyCode === 76) || (e.ctrlKey && e.keyCode === 37)) {     // alt+l|Ctrl+left
                        e.returnValue = false;
                        textboxCommand(ed, 'lefttextbox', 'expresscurate_fl_text_box');
                        e.preventDefault();
                        return false;
                    }
                    if ((e.altKey && e.keyCode === 82 ) || (e.ctrlKey && e.keyCode === 39)) {     // alt+r|Ctrl+right
                        e.returnValue = false;
                        textboxCommand(ed, 'righttextbox', 'expresscurate_fr_text_box');
                        e.preventDefault();
                        return false;
                    }
                    if ((e.altKey && e.keyCode === 74 ) || (e.ctrlKey && e.keyCode === 40)) {     // alt+j|Ctrl+down
                        e.returnValue = false;
                        textboxCommand(ed, 'justifytextbox', 'expresscurate_justify_text_box');
                        e.preventDefault();
                        return false;
                    }
                    if ((e.altKey && e.keyCode === 65) || (e.ctrlKey && e.keyCode === 38)) {     // alt+a |Ctrl+up
                        e.returnValue = false;
                        textboxCommand(ed, 'annotation', 'expresscurate_annotate');
                        e.preventDefault();
                        return false;
                    }
                    if (e.altKey && e.keyCode === 72) {     // alt+h
                        e.returnValue = false;
                        ExpressCurateKeywords.markEditorKeywords();
                        e.preventDefault();
                        return false;
                    }
                    if (e.altKey && e.keyCode === 70) {     // alt+f
                        e.returnValue = false;
                        noFollow(ed);
                        e.preventDefault();
                        return false;
                    }
                    if (e.altKey && e.keyCode === 87) {     // alt+w
                        e.returnValue = false;
                        wordCount(ed);
                        e.preventDefault();
                        return false;
                    }
                });

                ed.onLoadContent.add(function (ed) {
                    var dom = tinymce.activeEditor.dom,
                        divElements = dom.select('div[class*=expresscurate]');
                    dom.setStyle(divElements, 'height', 'auto');

                    if (ed.id === 'expresscurate_content_editor') {
                        ed.controlManager.buttons && ed.controlManager.buttons.blockquote && ed.controlManager.buttons.blockquote.remove() ||
                        ed.controlManager.controls && ed.controlManager.controls.content_blockquote && ed.controlManager.controls.content_blockquote.remove();
                    }
                    $(dom.select('div[id*=textbox]')).each(function (index, val) {
                        var id = $(val).attr('id');
                        if (id.indexOf('-') > -1) {
                            id = id.substring(0, id.indexOf('-'));
                            $(val).attr('id', id);
                        }
                    });
                    $(dom.select('div[id*=annotation]')).each(function (index, val) {
                        var id = $(val).attr('id');
                        if (id.indexOf('-') > -1) {
                            id = id.substring(0, id.indexOf('-'));
                            $(val).attr('id', id);
                        }
                    });
                });
                ed.onClick.add(function () {
                    var $description = $('.description');
                    if ($('.expresscurate_widget').length > 0) {
                        $description.find('.descriptionWrap').addClass('textareaBorder');
                        $description.find('p').add($description.find('.hint')).addClass('expresscurate_displayNone');
                        $description.removeClass('active');
                    }
                });
                ed.onNodeChange.add(function (ed) {
                    ed.controlManager.setActive('noFollow', false);
                    var $elem = $(ed.selection.getNode()),
                        $node = $elem,
                        nodeIsWrapped = $node.parents('div[class*=expresscurate]').length > 0 || $node.parents('div[class*=annotat]').length > 0;

                    if (nodeIsWrapped) {
                        $node = $node.parents('div[class*=expresscurate]') || $node.parents('div[class*=annotat]');
                    }
                    if ($elem.is('a')) {
                        if ($elem.attr('rel') === 'nofollow') {
                            ed.controlManager.setActive('noFollow', false);
                        } else {
                            ed.controlManager.setActive('noFollow', true);
                        }
                    }
                    ed.controlManager.setDisabled('noFollow', ed.selection.getNode().nodeName !== 'A');

                    var cssClass = $node.attr('class'),
                        activeButton = ' ';

                    switch (cssClass) {
                        case 'expresscurate_fl_text_box':
                            activeButton = 'lefttextbox';
                            break;
                        case 'expresscurate_fr_text_box':
                            activeButton = 'righttextbox';
                            break;
                        case'expresscurate_justify_text_box':
                            activeButton = 'justifytextbox';
                            break;
                        case 'expresscurate_annotate':
                        case 'annotate':
                            activeButton = 'annotation';
                            break;
                    }

                    ed.controlManager.setActive('lefttextbox', false);
                    ed.controlManager.setActive('righttextbox', false);
                    ed.controlManager.setActive('justifytextbox', false);
                    ed.controlManager.setActive('annotation', false);
                    if (activeButton !== ' ') {
                        ed.controlManager.setActive(activeButton, true);
                    }
                });

                if (ed.id !== 'expresscurate_content_editor') {
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
                        ExpressCurateKeywords.markEditorKeywords();
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
             * @return {tinymce.ui.Control} New control instance or null if no control was created.
             */
            createControl: function () {
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
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                setupButtons();
                isSetup = true;
            }
        }
    }
})(window.jQuery);

ExpressCurateButtons.setup();