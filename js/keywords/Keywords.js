var Keywords = (function (jQuery) {
    var colors = ['Red', 'Blue', 'Green', 'Orange', 'LightBlue', 'Yellow', 'LightGreen'],
        $input,
        $elemToRotate,
        $notDefKeywordsMessage,
        $notDefWordsMessage,
        $keywordsPart,
        $settingsPage;

    function addKeyword(keywords, $beforeElem) {
        Utils.startLoading($input, $elemToRotate);
        var keywordsToHighlight = keywords;
        keywords = keywords.join(',');
        var keywordHtml = '',
            errorMessage = '';
        jQuery('.expresscurate_errorMessage').remove();
        if (keywords.length > 2) {
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_keywords_add_keyword',
                data: {
                    keywords: keywords,
                    get_stats: true
                }
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $beforeElem.html('');
                    jQuery.each(data.stats, function (key, value) {
                        keywordHtml = '<li>\
                            <span class="color expresscurate_' + value.color + '"></span>\
                            <span class="word">' + key + '</span>\
                            <a class="addPost" href="post-new.php?post_title=' + encodeURIComponent("TODO: define post title using " + key) + '&content=' + encodeURIComponent("TODO: write content using " + key) + '&expresscurate_keyword=' + key + '">+ add post</a>\
                            <span class="expresscurate_floatRight postCount">' + value.posts_count + '</span>\
                            <span class="remove hover expresscurate_floatRight">&#215</span>\
                            <span class="count expresscurate_floatRight">' + value.percent + '%</span>\
                            <span class="inTitle expresscurate_floatRight">' + value.title + ' %</span>\
                         </li>';
                        $beforeElem.append(keywordHtml);
                        notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
                    });
                } else if (data.msg === 'Something went wrong') {
                    errorMessage = 'Calculation Error. Please try refreshing this web page.  If the problem persists, <a href="admin.php?page=expresscurate&type=keywords">contact us</a> at support@expresscurate.com'
                }
            }).always(function () {
                Utils.endLoading($input, $elemToRotate);
                jQuery(keywordsToHighlight).each(function (index, value) {
                    KeywordUtils.highlight(value, $beforeElem.find('li'));
                });

            });
        } else {
            errorMessage = 'This keyword is too short.  We recommend keywords with at least 3 characters.';
            Utils.endLoading($input, $elemToRotate);
        }
        if (errorMessage !== '') {
            jQuery('.addKeywords').after('<p class="expresscurate_errorMessage">' + errorMessage + '</p>');
        }
    }

    function insertKeywordInKeywordsSettings(keyword, $beforeElem) {
        if (keyword.length > 0) {
            $settingsPage.find('.suggestion').remove();
            addKeyword(keyword, $beforeElem);
            notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
        }
    }

    function markCuratedKeywords() {
        var ed = tinyMCE.activeEditor;
        var $highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        var keywords = [];
        jQuery('#curated_tags').find('li').each(function (index, value) {
            keywords.push(jQuery(value).text().slice(0, -1).trim());
        });
        keywords.pop();
        if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0) {
            markKeywords(ed, keywords);
        } else {
            $highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
        }
    }

    var activeMarkButton = false;

    function markEditorKeywords() {
        var definedKeywords = jQuery('#expresscurate_defined_tags').val();
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
            var ed = tinyMCE.get('content'),
                keywords = ((definedKeywords !== '') ? definedKeywords.split(', ') : null),
                $highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
            if (!keywords) {
                ed.controlManager.setActive('markKeywords', false);
                $highlightedElems.each(function (index, val) {
                    jQuery(val).replaceWith(this.childNodes);
                });
                tinyMCE.activeEditor.windowManager.open({
                    title: 'Mark keywords',
                    id: 'expresscurate_keyword_dialog',
                    width: 450,
                    height: 80,
                    html: '<label class="expresscurate_keywordMessage">Currently you don&#39;t have any defined keywords.</label>  <a class="button-primary" href="#expresscurate">Start adding now</a> <a href="#" class="cancel">Cancel</a>'
                });
            } else if (ed) {
                if (!activeMarkButton) {
                    ed.controlManager.setActive('markKeywords', true);
                    activeMarkButton = true;
                    markKeywords(ed, keywords);
                } else {
                    $highlightedElems.each(function (index, val) {
                        jQuery(val).replaceWith(this.childNodes);
                    });
                    ed.controlManager.setActive('markKeywords', false);
                    activeMarkButton = false;
                }
            }
        }
    }

    function markKeywords(ed, keywords) {
        var $highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        if (keywords) {
            var bookmark = ed.selection.getBookmark();
            $highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
            var matches = ed.getBody(),
                i = 0;

            keywords = keywords.sort(function (a, b) {
                return b > a
            });
            keywords.forEach(function (val) {
                jQuery(matches).html(function (index, oldHTML) {
                    return oldHTML.replace(new RegExp('((^|\\s|>|))(' + val + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi'), '$2<span class="expresscurate_keywordsHighlight expresscurate_highlight' + colors[i % 7] + '">$3</span>');
                });
                i++;
            });
            jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').each(function (index, val) {
                if (jQuery(val).parent().hasClass('expresscurate_keywordsHighlight')) {
                    jQuery(val).replaceWith(this.childNodes);
                }
            });
            if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length > 0) {
                ed.controlManager.setActive('markKeywords', true);
            }
            ed.selection.moveToBookmark(bookmark);
        }
    }

    function notDefinedMessage(message, list) {
        if (list.length > 0) {
            message.addClass('expresscurate_displayNone');
            message.parent().removeClass('expresscurate_notDefinedWrap');
        } else {
            message.removeClass('expresscurate_displayNone');
            message.parent().addClass('expresscurate_notDefinedWrap');
        }
    }

    function setupKeywords() {
        $settingsPage = jQuery('.expresscurate_keywords_settings');
        $keywordsPart = jQuery('.keywordsPart');
        $input = jQuery('.addKeywords input');
        $elemToRotate = jQuery('.addKeywords span span');
        $notDefKeywordsMessage = $keywordsPart.find('.expresscurate_notDefined');
        $notDefWordsMessage = jQuery('.usedWordsPart .expresscurate_notDefined');
        var $addKeywordInput = $settingsPage.find('.addKeywords input');
        notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find('ul li'));
        notDefinedMessage($notDefWordsMessage, jQuery('.usedWordsPart ul li'));
        jQuery('html').on('click', '#expresscurate_keyword_dialog a.button-primary, #expresscurate_keyword_dialog a.cancel', function () {
            tinymce.activeEditor.windowManager.close();
            $addKeywordInput.focus();
        });
        /*autoComplete*/

        $addKeywordInput.on("keyup", function (e) {
            if (e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 27) {
                e.preventDefault();
                return;
            }
            var list = jQuery('.addKeywords .suggestion');
            list.remove();
            if (e.keyCode === 13) {
                insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery('.addKeywords input'), $keywordsPart), $keywordsPart.find('div > ul'));
            } else {
                KeywordUtils.keywordsSuggestions(jQuery(this));
            }

        });

        KeywordUtils.suggestionsKeyboardNav($addKeywordInput.eq(0));

        $settingsPage.on('click', function (e) {
            var $this = jQuery(this);
            if (jQuery(e.target).is('.suggestion li')) {
                var newKeyword = jQuery(e.target).text();
                $addKeywordInput.val(newKeyword);
                insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords($addKeywordInput, $keywordsPart), $keywordsPart.find(' div > ul'));
                $this.find('.suggestion').remove();
            } else {
                $this.find('.suggestion').remove();
            }
        });
        /**/
        $settingsPage.find('.addKeywords span').on('click', function () {
            $settingsPage.find('.suggestion').remove();
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords($addKeywordInput, $keywordsPart), $keywordsPart.find(' div > ul'));
        });

        jQuery('.usedWordsPart ul').on('click', '.add', function () {
            jQuery(this).parents('li').addClass('expresscurate_highlight');
            $settingsPage.find('.suggestion').remove();
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery(this).parent().find('.word'), $keywordsPart), $keywordsPart.find(' div> ul'));
            jQuery(this).parents('li').fadeOut(1000).remove();
            notDefinedMessage($notDefWordsMessage, jQuery('.usedWordsPart ul li'));
        });
        $keywordsPart.find(' ul').on('click', '.remove', function () {
            var obj = jQuery(this);
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_keywords_delete_keyword',
                data: {keyword: KeywordUtils.justText(jQuery(this).parent().find('.word'))}
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    KeywordUtils.close(KeywordUtils.justText(obj.parent().find('.word')), obj.parent('li'));
                    notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
                }
            });
        });
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupKeywords();
                    isSetup = true;
                });
            }
        },

        markCuratedKeywords: markCuratedKeywords,
        markEditorKeywords: markEditorKeywords
    }
})(window.jQuery);
Keywords.setup();
