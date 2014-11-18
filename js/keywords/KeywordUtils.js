var KeywordUtils = (function (jQuery) {
    return {
        checkKeyword: function (text,listName) {
            if(text!='') {
                jQuery('.expresscurate_errorMessage').remove();
                text = text.replace(/[,.;:?!]+/g, '').trim();
                var defTags = jQuery('textarea[name=expresscurate_defined_tags]');
                var defVal = KeywordUtils.justText(defTags);

                if (text.length < 3 && jQuery('#expresscurate_widget').length) {

                    jQuery('.addKeywords').after('<p class="expresscurate_errorMessage">This keyword is too short.  We recommend keywords with at least 3 characters.</p>');
                } else {
                    defVal = defVal.replace(/\s{2,}/g, ' ');
                    var defValArr = defVal.split(', ');
                    var rslt = null;
                    for (var i = 0; i < defValArr.length; i++) {
                        if (defValArr[i].toLowerCase() == text.toLowerCase()) {
                            rslt = (i + 1);
                            if (jQuery('#expresscurate_widget').length > 0) {
                                KeywordUtils.highlight(text, jQuery('#expresscurate_widget .statisticsTitle'));
                            } else if (listName !== undefined) {
                                KeywordUtils.highlight(text, listName.find('div > ul li'));
                            }
                            text = '';
                            break;
                        } else {
                            rslt = -1;
                        }
                    }
                    if (!/^\s+$/.test(text) && text.length > 2) {
                        var s;
                        if (defVal == '')
                            s = text;
                        else
                            s = defVal + ', ' + text;
                        defTags.val(s);
                        defTags.text(s);
                        if (listName !== undefined) {
                            listName.find('.expresscurate_notDefined').addClass('expresscurate_displayNone');
                        }
                    }
                }
            }
                return text;
        },

        multipleKeywords: function (el) {
            jQuery('.expresscurate_errorMessage').remove();
            var keywords = '';
            if (el.is('span')) {
                keywords = KeywordUtils.justText(el);
            } else {
                keywords = el.val();
                el.val('');
            }
            var arr = keywords.split(',');
            var result = new Array();
            for (var i = 0; i < arr.length; i++) {
                var checked_keyword = KeywordUtils.checkKeyword(arr[i]);
                if (checked_keyword.length > 1) {
                    result.push(checked_keyword);
                }
            }
            return result;
        },

        close: function (keyword, elemToRemove) {
            var defTags = jQuery('textarea[name=expresscurate_defined_tags]'),
                newVal = '';
            newVal = KeywordUtils.justText(defTags).toLowerCase().replace(keyword.toLowerCase(), '');
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
        },

        highlight: function (text,li) {
            var keyword=text;
            li.each(function (index, value) {
                if(jQuery(value).is('#expresscurate_widget .statisticsTitle')){
                    if (jQuery(value).find('span').text().toLowerCase() == text.toLowerCase()){
                        var elem = jQuery(this).closest('.expresscurate_background_wrap');
                        var i = elem.closest('.expresscurate_widget_wrapper').find('.expresscurate_background_wrap').index(elem);
                        jQuery('.expresscurate_widget_wrapper .expresscurate_background_wrap').eq(i).css('opacity','.7');
                        setTimeout(function () {
                            elem.css('opacity','1.0');
                        }, 1000);
                    }
                }else if (KeywordUtils.justText(jQuery(value).find('.word')).toLowerCase().trim() == keyword.toLowerCase()) {
                    var elem = jQuery(value);
                    elem.css({'background-color': '#FCFCFC'});
                    setTimeout(function () {
                        elem.css({'background-color': 'transparent'});
                    }, 1000);
                }
            });
        },

        justText: function (elem) {
            if (elem.clone().children().length > 0) {
                return elem.clone()
                    .children()
                    .remove()
                    .end()
                    .text();
            } else {
                return elem.text();
            }
        },
        startLoading: function (input, elemToRotate) {
            input.prop('disabled', true);
            var rotation = 360;
            elemToRotate.css({
                '-webkit-transition-duration': '1500ms',
                'transition-duration': '1500ms',
                '-webkit-transform': 'rotate(' + rotation + 'deg)',
                '-moz-transform': 'rotate(' + rotation + 'deg)',
                '-ms-transform': 'rotate(' + rotation + 'deg)',
                'transform': 'rotate(' + rotation + 'deg)'
            });
            interval = setInterval(function () {
                rotation += 360;
                elemToRotate.css({
                    '-webkit-transform': 'rotate(' + rotation + 'deg)',
                    '-moz-transform': 'rotate(' + rotation + 'deg)',
                    '-ms-transform': 'rotate(' + rotation + 'deg)',
                    'transform': 'rotate(' + rotation + 'deg)'
                });
            }, 1500);
        },
        endLoading: function (input, elemToRotate) {
            input.removeAttr('disabled');
            clearInterval(interval);
            elemToRotate.css({
                '-webkit-transition-duration': '0s',
                'transition-duration': '0s',
                '-webkit-transform': 'rotate(0deg)',
                '-moz-transform': 'rotate(0deg)',
                '-ms-transform': 'rotate(0deg)',
                'transform': 'rotate(0deg)'
            });
            input.focus();
        }
    }
})(window.jQuery);