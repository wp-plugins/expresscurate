var SEOControl = (function (jQuery){
    var insertKeywordInWidget = function (keywords, beforeElem) {
        keywords = keywords.join(',');
        var keywordHtml = '';
        if (keywords.length > 0) {
            var post_id = jQuery('#expresscurate_post_id').val();
            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_get_post_keyword_stats', {keyword: keywords, post_id: post_id}, function (res) {
                data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    jQuery.each(data.stats, function (key, value) {
                        var title = (value.title > 0 ? "yes" : "no");
                        keywordHtml = '<div class="expresscurate_background_wrap"> \
                                   <span class="close">&#215</span>\
                                   <div title="' + key + '" class="statisticsTitle expresscurate_' + value.color + '"><span>' + key + '</span></div> \
                                   <div title="Occurance in Title: ' + title + '" class="statistics borderRight">\
                                        <div class="center">title<img src="' + jQuery('#expresscurate_plugin_dir').val() + '../images/' + title + '.png">\</div> \
                                   </div> \
                                   <div title="Occurance in Content: ' + value.percent + '%" class="statistics"> \
                                        <div class="center">content<span>' + value.percent + '%</span></div>\
                                   </div> \
                                </div>';
                        beforeElem.before(keywordHtml);
                    });
                }
            });
        }
    };

    var updateKeywords = function (content) {
        if (!content) {
            if (jQuery('#content').css("display") == "block") {
                content = jQuery('#content').val();
            } else {
                content = tinyMCE.get("content").getContent();
            }
        }
        var keywordsVal = KeywordUtils.justText(jQuery('#expresscurate_defined_tags'));
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_get_stats', {keywords: keywordsVal, post_title: jQuery('#title').val(), post_content: content}, function (res) {
            var data = jQuery.parseJSON(res);

            if (data.status === 'success') {
                jQuery('.expresscurate_background_wrap').remove();
                var keywordHtml = '';
                jQuery.each(data.stats, function (keyword, stat) {
                    if (keyword != '') {
                        var title = (stat.title > 0 ? "yes" : "no");
                        keywordHtml += '<div class="expresscurate_background_wrap"> \
                                <span class="close">&#215</span>\
                                <div class="statisticsTitle expresscurate_' + stat.color + '"><span>' + keyword + '</span></div> \
                                <div class="statistics borderRight">\
                                <div class="center">title<img src="' + jQuery('#expresscurate_plugin_dir').val() + '../images/' + title + '.png"></div> \
                                </div> \
                                <div class="statistics"> \
                                <div class="center">content<span>' + stat.percent + '%</span></div>\
                                </div> \
                                </div>';
                    }
                });
                jQuery('.addKeywords').before(keywordHtml);
            }
        });
    };

    var setupSEOControl = function(){
        if (jQuery.trim(jQuery('textarea[name=expresscurate_description]').val()) == '') {
            jQuery('textarea[name=expresscurate_description]').empty();
        }

        jQuery('.expresscurate_widget_wrapper .addKeywords input').on("keyup", function (e) {
            if (e.keyCode == 13) {
                insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.addKeywords'));
            }
        });

        jQuery('.expresscurate_widget_wrapper').keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        jQuery('.expresscurate_widget_wrapper .addKeywords span').on('click', function () {
            insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input')), jQuery('.addKeywords'));
        });
        jQuery('.expresscurate_background_wrap .close').live('click', function () {
            KeywordUtils.close(jQuery(this).parent().find('.statisticsTitle').text(), jQuery(this).parent('.expresscurate_background_wrap'));
        });

        jQuery('.description textarea').on('keyup focus', function () {
            var maxVal = 156,
                count = jQuery('.description  textarea').val().length,
                val,
                textarea = jQuery('.description textarea');
            /*keywords in meta description*/

            var keywords = new Array(),
                metaDesc = jQuery('.description  textarea').val(),
                includedKeywordsCount = 0,
                keywordsCount = 0;
            var defKeywords = jQuery('#expresscurate_defined_tags').val();
            if (defKeywords.length > 0) {
                keywords = defKeywords.split(', ');
                for (var i = 0; i < keywords.length; i++) {
                    var myRegExp = new RegExp('\\b' + keywords[i] + '\\b', 'gi');
                    if (metaDesc.match(myRegExp)) {
                        includedKeywordsCount++;
                    }
                }
                var keywordsCount = keywords.length;
            }
            jQuery('.usedKeywordsCount').replaceWith('<p class="usedKeywordsCount"><span class="bold">' + includedKeywordsCount + '</span>' + ' / ' + keywords.length + '</p>');
            /**/
            if (count > maxVal) {
                textarea.val(textarea.val().substring(0, maxVal));
            } else {
                val = maxVal - count;
            }
            jQuery('.description .lettersCount span').text(val);
        });

        jQuery('.description, .description p').click(function () {
            jQuery('.description  p , .description div').removeClass('expresscurate_displayNone');
            jQuery('.description').css({'background-color': '#ffc67d'});
            jQuery('.description  textarea').removeClass('textareaBorder');
            jQuery('.description textarea').focus();
        });

        jQuery('.expresscurate_widget_wrapper').click(function () {
            jQuery('.description  textarea').addClass('textareaBorder');
            jQuery('.description  p , .description div').addClass('expresscurate_displayNone');
            jQuery('.description').css({'background-color': '#ffffff'});
        });

        jQuery(document).click(function (e) {
            if (jQuery('.expresscurate_widget').length > 0 && !jQuery(e.target).parents('#expresscurate').is('div')) {
                jQuery('.description  textarea').addClass('textareaBorder');
                jQuery('.description  p , .description div').addClass('expresscurate_displayNone');
                jQuery('.description').css({'background-color': '#ffffff'});
            }
        });

        jQuery('.expresscurate_widget_wrapper label .rotate').click(function () {
            var el = jQuery(this).addClass('rotated');
            setTimeout(function () {
                el.removeClass('rotated');
            }, 1000);
        });

        /*reload widget keywords*/
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
            check_editor = setTimeout(function check() {
                var content;
                if (jQuery('#content').css("display") == "block") {
                    content = jQuery('#content').val();
                } else {
                    content = tinyMCE.get("content").getContent();
                }
                clearTimeout(check_editor);
                updateKeywords(content);
                setTimeout(check, 15000);
            }, 1);
        }
    };

    var isSetup = false;

    return{
        setup: function(){
            if(!isSetup){
                jQuery(document).ready(function(){
                    setupSEOControl();
                    isSetup = true;
                });
            }
        },

        updateKeywords: updateKeywords
    }
})(window.jQuery);

SEOControl.setup();
// load KeywordUtils.js and then call setup (ajax request)
//jQuery.getScript("./KeywordUtils.js", function(){
//    SEOControl.setup();
//});