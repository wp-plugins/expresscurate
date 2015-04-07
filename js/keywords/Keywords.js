var ExpressCurateKeywords = (function ($) {
    var colors = ['Red', 'Blue', 'Green', 'Orange', 'LightBlue', 'Yellow', 'LightGreen'],
        $input,
        $elemToRotate,
        $notDefKeywordsMessage,
        $notDefWordsMessage,
        $keywordsPart,
        $settingsPage,
        $autoComplete;

    function addKeyword(keywords, $beforeElem) {
        var keywordsToHighlight = keywords,
            $errorMessage = $('.addNewKeyword .expresscurate_errorMessage');
        keywords = keywords.join(',');
        var keywordHtml = '',
            errorMessage = '';

        ExpressCurateUtils.startLoading($input, $elemToRotate);
        $errorMessage.text('');

        if (keywords.length > 2) {
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_keywords_add_keyword',
                data: {
                    keywords: keywords,
                    get_stats: true
                }
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $beforeElem.html('');
                    $.each(data.stats, function (key, value) {
                        $.extend(value, {
                            'keyword': key,
                            'url': 'post-new.php?post_title=' + encodeURIComponent("TODO: define post title using " + key) + '&content=' + encodeURIComponent("TODO: write content using " + key) + '&expresscurate_keyword=' + key + '""post-new.php?post_title=' + encodeURIComponent("TODO: define post title using " + key) + '&content=' + encodeURIComponent("TODO: write content using " + key) + '&expresscurate_keyword=' + key
                        });
                        keywordHtml = ExpressCurateUtils.getTemplate('keywordsSettings', value);
                        $beforeElem.append(keywordHtml);
                        $autoComplete.find('li').remove();
                        notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
                    });
                } else if (data.msg === 'Something went wrong') {
                    errorMessage = 'Calculation Error. Please try refreshing this web page.  If the problem persists, <a href="admin.php?page=expresscurate&type=keywords">contact us</a> at support@expresscurate.com'
                }
            }).always(function () {
                ExpressCurateUtils.endLoading($input, $elemToRotate);
                $(keywordsToHighlight).each(function (index, value) {
                    ExpressCurateKeywordUtils.highlight(value, $beforeElem.find('li'));
                });

            });
        } else {
            errorMessage = 'This keyword is too short.  We recommend keywords with at least 3 characters.';
            ExpressCurateUtils.endLoading($input, $elemToRotate);
        }
        if (errorMessage !== '') {
            $errorMessage.text(errorMessage);
        }

        ExpressCurateUtils.track('/keywords/add');
    }

    function insertKeywordInKeywordsSettings(keyword, $beforeElem) {
        if (keyword.length > 0) {
            $autoComplete.find('li').remove();
            addKeyword(keyword, $beforeElem);
            notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
        }
    }

    function markCuratedKeywords() {
        var ed = tinyMCE.activeEditor,
            $highlightedElems = $(ed.getBody()).find('span.expresscurate_keywordsHighlight'),
            keywords = [];

        $('#curated_tags').find('li').each(function (index, value) {
            keywords.push($(value).text().trim());
        });
        keywords.pop();

        if ($(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0) {
            markKeywords(ed, keywords);
        } else {
            $highlightedElems.each(function (index, val) {
                $(val).replaceWith(this.childNodes);
            });
        }

        ExpressCurateUtils.track('/post/content-dialog/content/keywords/mark');
    }

    var activeMarkButton = false;

    function markEditorKeywords() {
        var definedKeywords = $('#expresscurate_defined_tags').val();

        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && $('.expresscurate_widget').length > 0) {
            var ed = tinyMCE.get('content'),
                keywords = ((definedKeywords !== '') ? definedKeywords.split(', ') : null),
                $highlightedElems = $(ed.getBody()).find('span.expresscurate_keywordsHighlight');

            if (!keywords) {
                ed.controlManager.setActive('markKeywords', false);
                $highlightedElems.each(function (index, val) {
                    $(val).replaceWith(this.childNodes);
                });
                var dialog=tinyMCE.activeEditor.windowManager.open({
                    title: 'Mark keywords',
                    id: 'expresscurate_keyword_dialog',
                    width: 450,
                    height: 80,
                    html: '<label class="expresscurate_keywordMessage">Currently you don&#39;t have any defined keywords.</label> <!-- <a class="expresscurate_keywordMessageButton button-primary " href="#expresscurate">Start adding now</a> <a href="#" class="cancel">Cancel</a>-->',
                    buttons: [{
                        text: 'Start adding now',
                        classes: 'expresscurate_socialInsertButton',
                        disabled: false,
                        onclick: function(){
                            $('html, body').animate({
                                scrollTop: $("#expresscurate").offset().top - 40
                            }, 400);
                            dialog.close();
                            $('.expresscurate_widget .addKeywords input').focus();
                        }
                    }]
                });
            } else if (ed) {
                if (!activeMarkButton) {
                    ed.controlManager.setActive('markKeywords', true);
                    activeMarkButton = true;
                    markKeywords(ed, keywords);
                } else {
                    $highlightedElems.each(function (index, val) {
                        $(val).replaceWith(this.childNodes);
                    });
                    ed.controlManager.setActive('markKeywords', false);
                    activeMarkButton = false;
                }
            }
        }

        ExpressCurateUtils.track('/post/content/keywords/mark');
    }

    function markKeywords(ed, keywords) {
        var $highlightedElems = $(ed.getBody()).find('span.expresscurate_keywordsHighlight');

        if (keywords) {
            var bookmark = ed.selection.getBookmark();
            $highlightedElems.each(function (index, val) {
                $(val).replaceWith(this.childNodes);
            });
            var matches = ed.getBody(),
                i = 0;
            keywords = keywords.sort(function (a, b) {
                return b > a
            });
            keywords.forEach(function (val) {
                $(matches).html(function (index, oldHTML) {
                    return oldHTML.replace(new RegExp('((^|\\s|>|))(' + val + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi'), '$2<span class="expresscurate_keywordsHighlight expresscurate_highlight' + colors[i % 7] + '">$3</span>');
                });
                i++;
            });

            $(ed.getBody()).find('span.expresscurate_keywordsHighlight').each(function (index, val) {
                if ($(val).parent().hasClass('expresscurate_keywordsHighlight')) {
                    $(val).replaceWith(this.childNodes);
                }
            });
            if ($(ed.getBody()).find('span.expresscurate_keywordsHighlight').length > 0) {
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
        $settingsPage = $('.expresscurate_keywords_settings');
        $keywordsPart = $('.keywordsPart');
        $autoComplete = $keywordsPart.find('.suggestion');
        $input = $('.addKeywords input');
        $elemToRotate = $('.addKeywords span span');
        $notDefKeywordsMessage = $keywordsPart.find('.expresscurate_notDefined');
        $notDefWordsMessage = $('.usedWordsPart .expresscurate_notDefined');
        var $addKeywordInput = $settingsPage.find('.addKeywords input');

        notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find('ul li'));
        notDefinedMessage($notDefWordsMessage, $('.usedWordsPart ul li'));

        /*keywords alert*/
        $('html').on('click', '#expresscurate_keyword_dialog a.button-primary, #expresscurate_keyword_dialog a.cancel', function () {
            tinymce.activeEditor.windowManager.close();
            $addKeywordInput.focus();
        });

        /*autoComplete*/
        $addKeywordInput.on("keyup", function (e) {
            if (e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 27) {
                e.preventDefault();
                return;
            }
            var list = $autoComplete.find('li');
            list.remove();

            if (e.keyCode === 13) {
                insertKeywordInKeywordsSettings(ExpressCurateKeywordUtils.multipleKeywords($('.addKeywords input'), $keywordsPart), $keywordsPart.find('div > ul'));
            } else {
                ExpressCurateKeywordUtils.keywordsSuggestions($(this));
            }

        });

        ExpressCurateKeywordUtils.suggestionsKeyboardNav($addKeywordInput.eq(0));

        $settingsPage.on('click', function (e) {
            var $this = $(this);
            if ($(e.target).is('.suggestion li')) {
                var newKeyword = $(e.target).text();
                $addKeywordInput.val(newKeyword);
                insertKeywordInKeywordsSettings(ExpressCurateKeywordUtils.multipleKeywords($addKeywordInput, $keywordsPart), $keywordsPart.find(' div > ul'));
            }
        });

        /*add keywords*/
        $settingsPage.find('.addKeywords span').on('click', function () {
            $autoComplete.find('li').remove();
            insertKeywordInKeywordsSettings(ExpressCurateKeywordUtils.multipleKeywords($addKeywordInput, $keywordsPart), $keywordsPart.find(' div > ul'));
        });

        $('.usedWordsPart ul').on('click', '.add', function () {
            $(this).parents('li').addClass('expresscurate_highlight');
            $autoComplete.find('li').remove();
            insertKeywordInKeywordsSettings(ExpressCurateKeywordUtils.multipleKeywords($(this).parent().find('.word'), $keywordsPart), $keywordsPart.find(' div> ul'));
            $(this).parents('li').fadeOut(1000).remove();
            notDefinedMessage($notDefWordsMessage, $('.usedWordsPart ul li'));
        });

        /*delete keywords*/
        $keywordsPart.find(' ul').on('click', '.remove', function () {
            var $obj = $(this),
                keyword = ExpressCurateKeywordUtils.justText($(this).parent().find('.word'));
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_keywords_delete_keyword',
                data: {keyword: keyword}
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $obj.parent('li').addClass('expresscurate_highlight');
                    setTimeout(function () {
                        ExpressCurateKeywordUtils.close(ExpressCurateKeywordUtils.justText($obj.parent().find('.word')), $obj.parent('li'));
                        notDefinedMessage($notDefKeywordsMessage, $keywordsPart.find(' ul li'));
                    }, 700);
                }
            });

            ExpressCurateUtils.track('/keywords/delete');
        });
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
                    setupKeywords();
                    isSetup = true;
                });
            }
        },

        markCuratedKeywords: markCuratedKeywords,
        markEditorKeywords: markEditorKeywords
    }
})(window.jQuery);
ExpressCurateKeywords.setup();
