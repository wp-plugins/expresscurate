var Keywords = (function(jQuery){
    var addKeyword = function (keywords, beforeElem) {
        keywords = keywords.join(',');
        var keywordHtml = '';
        if (keywords.length > 0) {
            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_add_keyword', {keywords: keywords, get_stats: true}, function (res) {
                data = jQuery.parseJSON(res);
                if (data.status === 'success') {
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
                }
            });
        }
    };

    var insertKeywordInKeywordsSettings = function (keyword, beforeElem) {
        if (keyword.length > 0) {
            addKeyword(keyword, beforeElem);
        }
    };

    //
    var markCuratedKeywords = function(){
        var ed = tinyMCE.activeEditor;
        var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        if (jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight').length <= 0) {
            if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
                check_editor = setTimeout(function check() {
                    clearTimeout(check_editor);
                    highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                    if (highlightedElems.length > 0) {
                        markDialogKeywords(ed);
                        setTimeout(check, 15000);
                    }
                }, 1);
            }
            markDialogKeywords(ed);
        } else {
            highlightedElems.each(function (index, val) {
                jQuery(val).replaceWith(this.childNodes);
            });
        }
    };

    var markDialogKeywords = function(ed){
        var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
        highlightedElems.each(function (index, val) {
            jQuery(val).replaceWith(this.childNodes);
        });
        var keywords = [];
        jQuery('#curated_tags li').each(function (index, value) {
            keywords.push(jQuery(value).text().slice(0, -1).trim());
        });
        keywords.pop();
        var matches;
        keywords.forEach(function (val) {
            matches = ed.getBody();
            jQuery(matches).html(function (index, oldHTML) {
                return oldHTML.replace(new RegExp('(\\b' + val + '\\b)(?=[^>]*(<|$))', 'gi'), '<span class="expresscurate_keywordsHighlight">$&</span>');
            });
        });
    };

    var markEditorKeywords = function(){
        var ed = tinyMCE.get('content');
        if (!ed) {
            ed = jQuery('#content');
        }
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
    };

    var markKeywords = function(ed){
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
    //

    var setupKeywords = function(){
        if (jQuery('.keywordsPart ul li').length > 0) {
            jQuery('.keywordsPart .notDefined').addClass('expresscurate_displayNone');
        }
        if (jQuery('.usedWordsPart ul li').length > 0) {
            jQuery('.usedWordsPart .notDefined').addClass('expresscurate_displayNone');
        }

        jQuery('.expresscurate_keywords_settings .addKeywords input').on("keyup", function (e) {
            if (e.keyCode == 13) {
                insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
            }
        });
        jQuery('.expresscurate_keywords_settings .addKeywords span').on('click', function () {
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
        });

        jQuery('.usedWordsPart ul .add').live('click', function () {
            jQuery(this).parents('li').css({'background-color': '#FCFCFC'});
            insertKeywordInKeywordsSettings(KeywordUtils.multipleKeywords(jQuery(this).parent().find('.word')), jQuery('.keywordsPart ul'));
            jQuery(this).parents('li').fadeOut(1000).remove();
            if (jQuery('.usedWordsPart ul li').length == 0) {
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
            if (jQuery('.keywordsPart ul li').length == 1) {
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
        setup: function(){
            if(!isSetup){
                jQuery(document).ready(function(){
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
// load KeywordUtils.js and then call setup (ajax request)
//jQuery.getScript("./KeywordUtils.js", function(){
//    Keywords.setup();
//});