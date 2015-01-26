var KeywordUtils = (function (jQuery) {
    var autoCompleteRequest;

    function checkKeyword(text, listName) {
        if (text !== '') {
            jQuery('.expresscurate_errorMessage').remove();
            text = text.replace(/[,.;:?!]+/g, '').trim();
            var $defTags = jQuery('textarea[name=expresscurate_defined_tags]'),
                defVal = KeywordUtils.justText($defTags).replace(/\s{2,}/g, ' '),
                $widget = jQuery('#expresscurate_widget'),
                defValArr = defVal.split(', '),
                rslt;
            if (text.length < 3 && $widget.length) {
                jQuery('.addKeywords').after('<p class="expresscurate_errorMessage">This keyword is too short.  We recommend keywords with at least 3 characters.</p>');
            } else {
                rslt = null;
                for (var i = 0; i < defValArr.length; i++) {
                    if (defValArr[i].toLowerCase() === text.toLowerCase()) {
                        rslt = (i + 1);
                        if ($widget.length > 0) {
                            KeywordUtils.highlight(text, $widget.find(' .statisticsTitle'));
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
                    if (defVal === '') {
                        s = text;
                    } else {
                        s = defVal + ', ' + text;
                    }
                    $defTags.val(s);
                    $defTags.text(s);
                    if (listName) {
                        listName.find('.expresscurate_notDefined').addClass('expresscurate_displayNone');
                    }
                }
            }
        }
        return text;
    }

    function multipleKeywords(el, listName) {
        var keywords = '',
            arr,
            result = [];

        if (el.is('span')) {
            keywords = KeywordUtils.justText(el);
        } else {
            keywords = el.val();
            el.val('');
        }

        arr = keywords.split(/,|:|;|[\\.]/);
        for (var i = 0; i < arr.length; i++) {
            var checked_keyword = KeywordUtils.checkKeyword(arr[i], listName);
            if (checked_keyword.length > 0) {
                result.push(checked_keyword);
            }
        }

        return result;
    }

    function close(keyword, elemToRemove) {
        var $defTags = jQuery('textarea[name=expresscurate_defined_tags]'),
            newVal = KeywordUtils.justText($defTags).toLocaleLowerCase();
        var lastChar = '';

        newVal = newVal.replace(keyword.toLocaleLowerCase(), '');
        newVal = newVal.replace(', ,', ',');
        lastChar = newVal.slice(-2);
        if (lastChar === ', ') {
            newVal = newVal.slice(0, -2);
        }
        if (newVal.match(/^, /)) {
            newVal = newVal.slice(2);
        }
        $defTags.val(newVal);
        $defTags.html(newVal);
        elemToRemove.remove();
    }

    function highlight(text, li) {
        var keyword = text,
            $elem, i;
        li.each(function (index, value) {
            if (jQuery(value).is('#expresscurate_widget .statisticsTitle')) {
                if (jQuery(value).find('span').text().toLowerCase() === text.toLowerCase()) {
                    $elem = jQuery(this).closest('.expresscurate_background_wrap');
                    i = $elem.closest('.expresscurate_widget_wrapper').find('.expresscurate_background_wrap').index($elem);
                    jQuery('.expresscurate_widget_wrapper .expresscurate_background_wrap').eq(i).addClass('highlight');
                    setTimeout(function () {
                        $elem.css('opacity', '1.0');
                    }, 1000);
                }
            } else if (KeywordUtils.justText(jQuery(value).find('.word')).toLowerCase().trim() === keyword.toLowerCase()) {
                $elem = jQuery(value);
                $elem.addClass('expresscurate_highlight');
                setTimeout(function () {
                    $elem.removeClass('expresscurate_highlight');
                }, 1000);
            }
        });
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

    function keywordsSuggestions(input) {
        if (input.val().length > 1) {
            var li_html = '',
                text = input.val();
            if (autoCompleteRequest && autoCompleteRequest.readystate !== 4) {
                autoCompleteRequest.abort();
                jQuery('.addKeywords .suggestion').remove();
            }
            autoCompleteRequest = jQuery.ajax({
                type: 'GET',
                url: 'admin-ajax.php?action=expresscurate_keywords_get_suggestions',
                data: {
                    term: text
                }
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                jQuery.each(data.slice(0, 3), function (key, value) {
                    li_html += '<li>' + value + '</li>';
                });
                if (li_html.length > 0) {
                    jQuery('.addKeywords .suggestion').remove();
                    jQuery('.addKeywords').append('<ul class="suggestion">' + li_html + '</ul>');
                }
            });
        }
    }

    function suggestionsKeyboardNav(input) {
        // keyboard navigation start
        var $input = jQuery(input);

        var getKey = function (e) {
            if (window.event) {
                return e.keyCode;
            }  // IE
            else if (e.which) {
                return e.which;
            }    // Netscape/Firefox/Opera
        };

        var pressed = false;
        var curText = '';

        var moveUp = function (suggestions, e) {
            e.preventDefault();
            if (!pressed) {
                pressed = true;
                var $listItems = suggestions.children('li');
                var i = $listItems.index(suggestions.children('li.express_curate_selected_list_item')) - 1;
                if (i === -1) {
                    $listItems.removeClass('express_curate_selected_list_item');
                    $input.val(curText);
                } else if (i >= 0) {
                    $listItems.removeClass('express_curate_selected_list_item');
                    $listItems.eq(i).addClass('express_curate_selected_list_item');
                    $input.eq(0).val($listItems.eq(i).text());
                }
                pressed = false;
            }
        };

        var moveDown = function (suggestions, e) {
            e.preventDefault();
            if (!pressed) {
                pressed = true;
                var $listItems = suggestions.children('li');
                var i = $listItems.index(suggestions.children('li.express_curate_selected_list_item')) + 1;
                if (i > $listItems.length - 1) {
                    i = $listItems.length - 1;
                }
                $listItems.removeClass('express_curate_selected_list_item');
                $listItems.eq(i).addClass('express_curate_selected_list_item');
                $input.eq(0).val($listItems.eq(i).text());
                pressed = false;
            }
        };

        $input.on('keyup', function (e) {
            if (!(e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 27 || e.keyCode === 13)) {
                if (curText !== $input.eq(0).val()) {
                    curText = $input.eq(0).val();
                }
            }
            if ($input.siblings('.suggestion').eq(0).is(':visible')) {
                switch (getKey(e)) {
                    case 38:    // UP
                        moveUp($input.siblings('.suggestion').eq(0), e);
                        break;
                    case 40:    // DOWN
                        moveDown($input.siblings('.suggestion').eq(0), e);
                        break;
                    case 27:    // ESC
                        $input.siblings('.suggestion').hide();
                        break;
                    default:
                }
            }
        });

        $input.on('keydown', function (e) {
            if (e.keyCode === 38) {
                e.preventDefault();
            }
        });
        // keyboard navigation end
    }

    return {
        checkKeyword: checkKeyword,
        multipleKeywords: multipleKeywords,
        close: close,
        highlight: highlight,
        justText: justText,
        keywordsSuggestions: keywordsSuggestions,
        suggestionsKeyboardNav: suggestionsKeyboardNav
    }
})(window.jQuery);