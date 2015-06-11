var ExpressCurateKeywordUtils = (function ($) {
    var autoCompleteRequest;

    function checkKeyword(text, listName,defElem) {
        if (text !== '') {
            text = text.replace(/[,.;:?!]+/g, '').trim();
            var $defTags =defElem ? defElem : $('textarea[name=expresscurate_defined_tags]'),
                defVal = justText($defTags).replace(/\s{2,}/g, ' '),
                $widget = $('#expresscurate_widget'),
                $errorMessage = $widget.find('.expresscurate_errorMessage'),
                defValArr = defVal.split(', ');
            $errorMessage.remove();
            if (text.length < 3 && $widget.length) {
                $errorMessage.text('This keyword is too short.  We recommend keywords with at least 3 characters.');
            } else {
                for (var i = 0; i < defValArr.length; i++) {
                    if (defValArr[i].toLowerCase() === text.toLowerCase()) {
                        if ($widget.length > 0) {
                            highlight(text, $widget.find(' .statisticsTitle'));
                        } else if (listName !== undefined) {
                            highlight(text, listName.find('div > ul li'));
                        }
                        text = '';
                        break;
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

    function multipleKeywords(el, listName,defElem) {
        var keywords = '',
            arr,
            result = [];

        if (el.is('span')) {
            keywords = justText(el);
        } else {
            keywords = el.val();
            el.val('');
        }

        arr = keywords.split(/,|:|;|[\\.]/);
        for (var i = 0; i < arr.length; i++) {
            var checkedKeyword = checkKeyword(arr[i], listName,defElem);
            if (checkedKeyword.length > 0) {
                result.push(checkedKeyword);
            }
        }
        return result;
    }

    function close(keyword, elemToRemove, defElem) {

        var $defTags =defElem ? defElem : $('textarea[name=expresscurate_defined_tags]'),
            newVal = justText($defTags).toLocaleLowerCase().trim(),
            lastChar = '',
            myRegExp = new RegExp('(, |^)' + keyword.toLocaleLowerCase() + '(,|$)', 'gmi');

        newVal = newVal.replace(myRegExp, '');
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
            if ($(value).is('#expresscurate_widget .statisticsTitle')) {
                if ($(value).find('span').text().toLowerCase() === text.toLowerCase()) {
                    $elem = $(this).closest('.expresscurate_background_wrap');
                    i = $elem.closest('.expresscurate_widget_wrapper').find('.expresscurate_background_wrap').index($elem);
                    $('.expresscurate_widget_wrapper .expresscurate_background_wrap').eq(i).addClass('highlight');
                    setTimeout(function () {
                        $elem.css('opacity', '1.0');
                    }, 1000);
                }
            } else if (justText($(value).find('.word')).toLowerCase().trim() === keyword.toLowerCase()) {
                $elem = $(value);
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
            var liHTML = '',
                text = input.val(),
                $autoComplete = $('.suggestion');
            if (autoCompleteRequest && autoCompleteRequest.readystate !== 4) {
                autoCompleteRequest.abort();
                $autoComplete.find('li').remove();
            }

            autoCompleteRequest = $.ajax({
                type: 'GET',
                url: 'admin-ajax.php?action=expresscurate_keywords_get_suggestions',
                data: {
                    term: text
                }
            }).done(function (res) {
                var data = $.parseJSON(res);
                $.each(data.slice(0, 3), function (key, value) {
                    liHTML += '<li>' + value + '</li>';
                });
                if (liHTML.length > 0) {
                    $autoComplete.find('li').remove();
                    $autoComplete.append(liHTML);
                }
            });
        }
    }

    function suggestionsKeyboardNav(input) {
        // keyboard navigation start
        var $input = $(input);

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