var KeywordUtils = (function(jQuery){
    return {
        checkKeyword: function (text) {
            text = text.replace(/[,.;:?!]+/g, '').trim();
            var defTags = jQuery('textarea[name=expresscurate_defined_tags]');
            var defVal = KeywordUtils.justText(defTags);

            defVal = defVal.replace(/\s{2,}/g, ' ');
            var defValArr = defVal.split(', ');
            var rslt = null;
            for (var i = 0; i < defValArr.length; i++) {
                if (defValArr[i].toLowerCase() == text.toLowerCase()) {
                    rslt = (i + 1);
                    KeywordUtils.highlight(text);
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
        },

        multipleKeywords: function (el) {
            var keywords = '';
            if (el.is('span')) {
                keywords = KeywordUtils.justText(el);
            } else {
                keywords = el.val();
                el.val('');
            }
            var arr = keywords.split(',');
            var result=new Array();
            for (var i = 0; i < arr.length; i++) {
                var checked_keyword = KeywordUtils.checkKeyword(arr[i]);
                if (checked_keyword.length > 0) {
                    result.push(checked_keyword);
                }
            }
            return result;
        },

        close: function (keyword, elemToRemove) {
            var defTags = jQuery('textarea[name=expresscurate_defined_tags]'),
                newVal = '';
            newVal = KeywordUtils.justText(defTags).replace(keyword, '');
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

        highlight: function (text) {
            jQuery('.keywordsPart ul li').each(function () {
                if (KeywordUtils.justText(jQuery(this).find('.word')).toLowerCase().trim() == text.toLowerCase()) {
                    var elem = jQuery(this);
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
        }
    }
})(window.jQuery);