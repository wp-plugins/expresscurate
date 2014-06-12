/*add keywords*/

function checkKeyword(el) {
    var text = '';
    if (el.is('span')) {
        text = justText(el);
    } else {
        text = el.val();
        el.val('');
    }
    text = text.replace(/[,.;:?!]+/g, '').trim();
    var defTags = jQuery('textarea[name=expresscurate_defined_tags]');
    var defVal = justText(defTags);

    defVal = defVal.replace(/\s{2,}/g, ' ');
    var defValArr = defVal.split(', ');
    var rslt = null;
    for (var i = 0; i < defValArr.length; i++) {
        if (defValArr[i].toLowerCase() == text.toLowerCase()) {
            rslt = (i + 1);
            highlight(text);
            text = '';
            break;
        } else {
            rslt = -1;
        }
    }
    if (!/^\s+$/.test(text) && text.length > 1) {
        var s;
        if (defVal == '')
            s = text;
        else
            s = defVal + ', ' + text;
        defTags.val(s);
        defTags.text(s);
        jQuery('.keywordsPart .notDefined').addClass('expresscurate_displayNone');
    }
    return text;
}

function close(keyword, elemToRemove) {
    var defTags = jQuery('textarea[name=expresscurate_defined_tags]'),
        newVal = '';
    newVal = justText(defTags).replace(keyword, '');
    newVal = newVal.replace(', ,', ',');
    var lastChar = newVal.slice(-2);
    if (lastChar == ', ') {
        newVal = newVal.slice(0, -2);
    }
    if (newVal.match(/^, /))
        newVal = newVal.slice(2);
    defTags.val(newVal);
    defTags.html(newVal);
    elemToRemove.remove();
}

function highlight(text) {
    jQuery('.keywordsPart ul li').each(function () {
        if (justText(jQuery(this).find('.word')).toLowerCase().trim() == text.toLowerCase()) {
            var elem = jQuery(this);
            elem.css({'background-color': '#FCFCFC'});
            setTimeout(function () {
                elem.css({'background-color': 'transparent'});
            }, 1000);
        }
    });
}
/*add keywords on new post page*/
function insertKeyword_Widget(keyword, beforeElem) {
    if (keyword.length > 0) {
        var post_id = jQuery('#expresscurate_post_id').val();
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_get_post_keyword_stats', {keyword: keyword, post_id: post_id}, function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'success') {
                var title = (data.stats[keyword].title > 0 ? "yes" : "no");
                var keywordHtml = '<div class="expresscurate_background_wrap"> \
                                   <span class="close">&#215</span>\
                                   <div title="'+keyword+'" class="statisticsTitle expresscurate_' + data.stats[keyword].color + '"><span>' + keyword + '</span></div> \
                                   <div title="Occurance in Title: '+title+'" class="statistics borderRight">\
                                        <div class="center">title<img src="' + jQuery('#expresscurate_plugin_dir').val() + '../images/' + title + '.png">\</div> \
                                   </div> \
                                   <div title="Occurance in Content: ' + data.stats[keyword].percent + '%" class="statistics"> \
                                        <div class="center">content<span>' + data.stats[keyword].percent + '%</span></div>\
                                   </div> \
                                </div>';
                beforeElem.before(keywordHtml);
            }
        });
    }
}

/* keywords settings page*/
function add_keyword(keyword, beforeElem) {
    jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_add_keyword', {keyword: keyword, get_stats: true}, function (res) {
        var data = jQuery.parseJSON(res);
        if (data.status === 'success') {
            var keywordHtml = '<li>\
                            <span class="color expresscurate_' + data.stats[keyword].color + '"></span>\
                            <span class="word">' + keyword + '</span>\
                            <a class="expresscurate_displayNone addPost" href="' + jQuery('#expresscurate_admin_url').val() + 'post-new.php?post_title=' + encodeURIComponent("TODO: define post title using " + keyword) + '&content=' + encodeURIComponent("TODO: write content using " + keyword) + '&expresscurate_keyword=' + keyword + '">+ add post</a>\
                            <span class="expresscurate_floatRight postCount">' + data.stats[keyword].posts_count + '</span>\
                            <span class="remove hover expresscurate_floatRight expresscurate_displayNone">&#215</span>\
                            <span class="count expresscurate_floatRight">' + data.stats[keyword].percent + '%</span>\
                            <span class="inTitle expresscurate_floatRight">' + data.stats[keyword].title + ' %</span>\
                         </li>';
            beforeElem.append(keywordHtml);
            beforeElem.find('li').last().css({'background-color': '#FCFCFC'});
            setTimeout(function () {
                beforeElem.find('li').last().css({'background-color': 'transparent'});
            }, 1000);
        }
    });
}

function insertKeyword_KeywordsSettings(keyword, beforeElem) {
    if (keyword.length > 0) {
        add_keyword(keyword, beforeElem);
    }
}
function justText(elem) {
    if (elem.clone().children().length > 0) {
        return elem.clone()
            .children()
            .remove()
            .end()
            .text();
    } else {
        return elem.text();
    }
}

function updateKeywords(content) {
    if (!content) {
        if (jQuery('#content').css("display") == "block") {
            content = jQuery('#content').val();
        } else {
            content = tinyMCE.get("content").getContent();
        }
    }
    var keywordsVal = justText(jQuery('#expresscurate_defined_tags'));
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
}


jQuery(document).ready(function ($) {
    if(jQuery.trim(jQuery('textarea[name=expresscurate_description]').val())==''){
        jQuery('textarea[name=expresscurate_description]').empty();
    }
    if (jQuery('.keywordsPart ul li').length > 0) {
        jQuery('.keywordsPart .notDefined').addClass('expresscurate_displayNone');
    }
    if (jQuery('.usedWordsPart ul li').length > 0) {
        jQuery('.usedWordsPart .notDefined').addClass('expresscurate_displayNone');
    }
    if (jQuery('.expresscurate_dashboard .expresscurate_background_wrap').length < 1) {
        jQuery('.expresscurate_dashboard .dashboardMessage').removeClass('expresscurate_displayNone');
    }
    /* post widget*/
    jQuery('.expresscurate_widget_wrapper .addKeywords input').on("keyup", function (e) {
        if (e.keyCode == 188 || e.keyCode == 13) {
            insertKeyword_Widget(checkKeyword(jQuery('.addKeywords input')), jQuery('.addKeywords'));
        }
    });

    jQuery('.expresscurate_widget_wrapper').keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

    jQuery('.expresscurate_widget_wrapper .addKeywords span').on('click', function () {
        insertKeyword_Widget(checkKeyword(jQuery('.addKeywords input')), jQuery('.addKeywords'));
    });
    jQuery('.expresscurate_background_wrap .close').live('click', function () {
        close(jQuery(this).parent().find('.statisticsTitle').text(), jQuery(this).parent('.expresscurate_background_wrap'));
    });
    /**/
    jQuery('.expresscurate_keywords_settings .addKeywords input').on("keyup", function (e) {
        if (e.keyCode == 188 || e.keyCode == 13) {
            insertKeyword_KeywordsSettings(checkKeyword(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
        }
    });
    jQuery('.expresscurate_keywords_settings .addKeywords span').on('click', function () {
        insertKeyword_KeywordsSettings(checkKeyword(jQuery('.addKeywords input')), jQuery('.keywordsPart ul'));
    });

    jQuery('.usedWordsPart ul .add').live('click', function () {
        jQuery(this).parents('li').css({'background-color': '#FCFCFC'});
        add_keyword(checkKeyword(jQuery(this).parent().find('.word')), jQuery('.keywordsPart ul'));
        jQuery(this).parents('li').fadeOut(1000).remove();
        if (jQuery('.usedWordsPart ul li').length == 0) {
            jQuery('.usedWordsPart .notDefined').removeClass('expresscurate_displayNone');
        }
    });

    jQuery('.keywordsPart ul .remove').live('click', function () {
        var obj = jQuery(this);
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_keywords_delete_keyword', {keyword: justText(jQuery(this).parent().find('.word'))}, function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'success') {
                close(justText(obj.parent().find('.word')), obj.parent('li'));
            }
        });
        if (jQuery('.keywordsPart ul li').length == 1) {
            jQuery('.keywordsPart .notDefined').removeClass('expresscurate_displayNone');
        }
    });
    $('.description textarea').on('keyup focus', function () {
        var maxVal = 156,
            count = $('.description  textarea').val().length,
            val,
            textarea = $('.description textarea');
        /*keywords in meta description*/
        var keywords=jQuery('#expresscurate_defined_tags').val().split(', '),
            metaDesc=$('.description  textarea').val(),
            includedKeywordsCount=0;
        for(var i=0;i<keywords.length;i++){
            if(new RegExp(' '+keywords[i].toLowerCase()+' ').test(' '+metaDesc.toLowerCase())){
                includedKeywordsCount++;
            }
        }
        jQuery('.usedKeywordsCount').replaceWith('<p class="usedKeywordsCount"><span class="bold">'+includedKeywordsCount+'</span>'+' / '+keywords.length+'</p>');
         /**/
        if (count > maxVal) {
            textarea.val(textarea.val().substring(0, maxVal));
        } else {
            val = maxVal - count;
        }
        $('.description .lettersCount span').text(val);

    });

    $('.description, .description p').click(function () {

        $('.description  p , .description div').removeClass('expresscurate_displayNone');
        $('.description').css({'background-color': '#ffc67d'});
        $('.description  textarea').removeClass('textareaBorder');
        $('.description textarea').focus();
    });
    $('.expresscurate_widget_wrapper').click(function () {
        $('.description  textarea').addClass('textareaBorder');
        $('.description  p , .description div').addClass('expresscurate_displayNone');
        $('.description').css({'background-color': '#ffffff'});
    });
   $(document).click(function(e){
      if(jQuery('.expresscurate_widget').length>0 && !$(e.target).parents('#expresscurate').is('div')){
          $('.description  textarea').addClass('textareaBorder');
          $('.description  p , .description div').addClass('expresscurate_displayNone');
          $('.description').css({'background-color': '#ffffff'});
       }
   });
    $('.expresscurate_keywords_settings ul li').live('hover', function () {
        $(this).find('.hover').removeClass('expresscurate_displayNone');
        $(this).find('.addPost').removeClass('expresscurate_displayNone');
    });

    $('.expresscurate_keywords_settings ul li').live('mouseleave', function () {
        $(this).find('.hover').addClass('expresscurate_displayNone');
        $(this).find('.addPost').addClass('expresscurate_displayNone');
    });

    jQuery('.expresscurate_keywords_settings ul li').live('hover', function () {
        jQuery(this).find('.hover').removeClass('expresscurate_displayNone');
    });
    jQuery('.expresscurate_keywords_settings ul li').live('mouseleave', function () {

        jQuery(this).find('.hover').addClass('expresscurate_displayNone');

    });
    jQuery('.expresscurate_widget_wrapper label span').click(function(){
        var el = jQuery(this).addClass('rotated');
        setTimeout(function() {
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
});


