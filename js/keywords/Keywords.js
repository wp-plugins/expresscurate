var Keywords = (function (jQuery) {
    var colors = ['Red', 'Blue', 'Green', 'Orange', 'LightBlue', 'Yellow', 'LightGreen'];
    var addKeyword = function (keywords, beforeElem) {
        KeywordUtils.startLoading(jQuery('.addKeywords input'), jQuery('.addKeywords span span'));
        jQuery('.expresscurate_errorMessage').remove();
        keywords = keywords.join(',');
        var keywordHtml = '',
            errorMessage='';
        if (keywords.length > 2 ) {
            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_add_keyword', {
                keywords: keywords,
                get_stats: true
            }, function (res) {
                data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    console.log(beforeElem.html(''));
                    jQuery.each(data.stats, function (key, value) {
                        keywordHtml = '<li>\
                            <span class="color expresscurate_' + value.color + '"></span>\
                            <span class="word">' + key + '</span>\
                            <a class="expresscurate_displayNone addPost" href="' + jQuery('#expresscurate_admin_url').val() + 'post-new.php?post_title=' + encodeURIComponent("TODO: define post title using " + key) + '&content=' + encodeURIComponent("TODO: write content using " + key) + '&expresscurate_keyword=' + key + '">+ add post</a>\
                            <span class="expresscurate_floatRight postCount">' + value.posts_count + '</span>\
                            <span class="remove hover expresscurate_floatRight expresscurate_displayNone">&#215</span>\
                            <span class="count expresscurate_floatRight">' + value.percent + '%</span>\
                            <span class="inTitle expresscurate_floatRight">' + value.title + ' %</span>\
                         </li>';
                        beforeElem.append(keywordHtml);
                        KeywordUtils.highlight(key);
                    });
                }else if(data.status ==='warning' && data.msg==='Something went wrong'){
                    errorMessage='Something went wrong';
                }
            }).always(function(){
                KeywordUtils.endLoading(jQuery('.addKeywords input'), jQuery('.addKeywords span span'));
            });
        }else{
            errorMessage= 'Keyword is too short.'
        }
        if(errorMessage!==''){
            jQuery('.addKeywords').after('<p class="expresscurate_errorMessage">'+errorMessage+'</p>');
        }
    };

    var insertKeywordInKeywordsSettings = function (keyword, beforeElem) {
        if (keyword.length > 0) {
            addKeyword(keyword, beforeElem);
        }
    };

    //
    var markCuratedKeywords = function () {
        var ed = tinyMCE.activeEditor;
        var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        var keywords = [];
        jQuery('#curated_tags li').each(function (index, value) {
            keywords.push(jQuery(value).text().slice(0, -1).trim());
        });
        keywords.pop();
        if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0) {
            /*if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
                check_editor = setTimeout(function check() {
                    clearTimeout(check_editor);
                    highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                    if (highlightedElems.length > 0) {
                        markKeywords(ed, keywords);
                        setTimeout(check, 15000);
                    }
                }, 1);
            }*/
            markKeywords(ed, keywords);
        } else {
            highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
        }
    };
    var activeMarkButton = false;
    var markEditorKeywords = function () {
        var definedKeywords = jQuery('#expresscurate_defined_tags').val();
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
            var ed = tinyMCE.get('content'),
                keywords = ((definedKeywords !== '') ? definedKeywords.split(', ') : null),
                check_editor;
            if (keywords == null) {
                ed.controlManager.setActive('markKeywords', false);
                tinyMCE.activeEditor.windowManager.open({
                    title: 'Mark keywords',
                    id: 'expresscurate_keyword_dialog',
                    width: 450,
                    height: 80,
                    html: '<label class="expresscurate_keywordMessage">Currently you don&#39;t have any defined keywords.</label>  <a class="button-primary" href="#expresscurate">Start adding now</a> <a href="#" class="cancel">Cancel</a>'
                });
            } else if (ed) {
                var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                if (!activeMarkButton) {
                    ed.controlManager.setActive('markKeywords', true);
                    activeMarkButton = true;
                    if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0) {
                        /*if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
                            check_editor = setTimeout(function check() {
                                clearTimeout(check_editor);
                                if (activeMarkButton) {
                                    definedKeywords = jQuery('#expresscurate_defined_tags').val();
                                    keywords = ((definedKeywords !== '') ? definedKeywords.split(', ') : null);
                                    markKeywords(ed, keywords);
                                }
                                setTimeout(check, 15000);
                            }, 1);
                        }*/
                        markKeywords(ed, keywords);
                    }
                } else {
                    highlightedElems.each(function (index, val) {
                        jQuery(val).replaceWith(this.childNodes);
                    });
                    ed.controlManager.setActive('markKeywords', false);
                    activeMarkButton = false;
                }
            }
        }
    };

    var markKeywords = function (ed, keywords) {
        var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        if (keywords !== null) {
            var bookmark = ed.selection.getBookmark();
            highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
            var matches = ed.getBody(),
                i = 0;
            keywords.forEach(function (val) {
                jQuery(matches).html(function (index, oldHTML) {
                    return oldHTML.replace(new RegExp('((^|\\s|>|))(' + val + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi'), '$2<span class="expresscurate_keywordsHighlight expresscurate_highlight' + colors[i % 7] + '">$3</span>');
                });
                if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length > 0) {
                    ed.controlManager.setActive('markKeywords', true);
                }
                i++;
            });
            ed.selection.moveToBookmark(bookmark);
        } else {
            highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
        }
    };

    var setupKeywords = function () {
        if (jQuery('.keywordsPart ul li').length > 0) {
            jQuery('.keywordsPart .notDefined').addClass('expresscurate_displayNone');
        }
        if (jQuery('.usedWordsPart ul li').length > 0) {
            jQuery('.usedWordsPart .notDefined').addClass('expresscurate_displayNone');
        }
        jQuery('html').on('click', '#expresscurate_keyword_dialog a.button-primary', function () {
            tinymce.activeEditor.windowManager.close();
            jQuery('.expresscurate_widget_wrapper .addKeywords input').focus();
        });
        jQuery('html').on('click', '#expresscurate_keyword_dialog a.cancel', function () {
            tinymce.activeEditor.windowManager.close();
        });
        jQuery('.expresscurate_keywords_settings .addKeywords input').on("keyup", function (e) {
            if (e.keyCode === 13) {
                insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
            }
        });
        jQuery('.expresscurate_keywords_settings .addKeywords span').on('click', function () {
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
        });

        jQuery('.usedWordsPart ul').on('click', '.add', function () {
            jQuery(this).parents('li').css({'background-color': '#FCFCFC'});
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery(this).parent().find('.word')), jQuery('.keywordsPart ul'));
            jQuery(this).parents('li').fadeOut(1000).remove();
            if (jQuery('.usedWordsPart ul li').length === 0) {
                jQuery('.usedWordsPart .notDefined').removeClass('expresscurate_displayNone');
            }
        });
        jQuery('.keywordsPart ul .remove').live('click', function () {
            var obj = jQuery(this);
            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_delete_keyword', {keyword: KeywordUtils.justText(jQuery(this).parent().find('.word'))}, function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    KeywordUtils.close(KeywordUtils.justText(obj.parent().find('.word')), obj.parent('li'));
                }
            });
            if (jQuery('.keywordsPart ul li').length === 1) {
                jQuery('.keywordsPart .notDefined').removeClass('expresscurate_displayNone');
            }
        });
        jQuery('.expresscurate_keywords_settings ul li').live('hover', function () {
            jQuery(this).find('.hover').removeClass('expresscurate_displayNone');
            jQuery(this).find('.addPost').removeClass('expresscurate_displayNone');
            jQuery(this).css('background-color', '#FCFCFC');
        });

        jQuery('.expresscurate_keywords_settings ul li').live('mouseleave', function () {
            jQuery(this).find('.hover').addClass('expresscurate_displayNone');
            jQuery(this).find('.addPost').addClass('expresscurate_displayNone');
            jQuery(this).css('background-color', 'transparent');
        });

        jQuery('.expresscurate_keywords_settings ul li').live('hover', function () {
            jQuery(this).find('.hover').removeClass('expresscurate_displayNone');
        });
        jQuery('.expresscurate_keywords_settings ul li').live('mouseleave', function () {
            jQuery(this).find('.hover').addClass('expresscurate_displayNone');
        });
    };

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
    };
})(window.jQuery);

Keywords.setup();
// load KeywordUtils.js and then call setup (ajax request)
//jQuery.getScript("./KeywordUtils.js", function(){
//    Keywords.setup();
//});