var SEOControl = (function (jQuery) {
    var insertKeywordInWidget = function (keywords, beforeElem) {
        /*Utils.startLoading(jQuery('.addKeywords input'), jQuery('.addKeywords span span'));
         Utils.endLoading(jQuery('.addKeywords input'), jQuery('.addKeywords span span'));
         jQuery.when(updateKeywords()).done(function () {
         });*/
        if (keywords.length > 0) {
            var titleText = jQuery('#titlediv input[name=post_title]').val(),
                content = jQuery('#content').val(),
                titleWords = words_in_text(titleText.toLowerCase()),
                contentWords = words_in_text(content.toLowerCase()),
                keywordHtml = '';

            jQuery.each(jQuery(keywords), function (index, value) {
                if (value.length > 2) {
                    var myRegExp = new RegExp('((^|\\s|>|))(' + value + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi');

                    var numOccurencesContent = (content.match(myRegExp)) ? content.match(myRegExp).length : 0,
                        numOccurencesTitle = titleText.match(myRegExp) ? titleText.match(myRegExp).length : 0;

                    var title = (numOccurencesTitle > 0 ? "yes" : "no"),
                        inContent = (contentWords.length > 0) ? ((numOccurencesContent / contentWords.length * 100).toFixed(2)) : 0,
                        color = 'blue';
                    if (inContent < 3) {
                        color = 'blue';
                    } else if (inContent >= 3 && inContent <= 5) {
                        color = 'green';
                    } else if (inContent > 5) {
                        color = 'red';
                    }
                    if (inContent == 0) inContent = Math.round(inContent);
                    keywordHtml += '<div class="expresscurate_background_wrap expresscurate_preventTextSelection">\
                            <span class="close">&#215</span>\
                            <div class="statisticsTitle expresscurate_' + color + '"><span>' + value + '</span></div>\
                            <div class="statistics ' + title + ' borderRight">\
                            <div class="center">title</div>\
                            </div>\
                            <div class="statistics">\
                            <div class="center">content<span>' + inContent + '%</span></div>\
                            </div>\
                            </div>';
                }
            });
            if (keywordHtml != '') {
                jQuery('.addKeywords').before(keywordHtml);
            }
        }

    };

    var updateKeywords = function () {
        var content = jQuery('#content').val(),
            titleText = jQuery('#titlediv input[name=post_title]').val(),
            titleWords = words_in_text(titleText.toLowerCase()),
            contentWords = words_in_text(content.toLowerCase()),
            keywords = KeywordUtils.justText(jQuery('#expresscurate_defined_tags')).toLowerCase().split(', '),
            keywordHtml = '';
        if (keywords.length > 0 && keywords[0] !== '') {
            jQuery.each(jQuery(keywords), function (index, value) {
                /* var numOccurencesContent = jQuery.grep(contentWords, function (elem) {
                 return elem === value;
                 }).length,
                 numOccurencesTitle = jQuery.grep(titleWords, function (elem) {
                 return elem === value;
                 }).length;*/
                var myRegExp = new RegExp('((^|\\s|>|))(' + value + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi');

                var numOccurencesContent = (content.match(myRegExp)) ? content.match(myRegExp).length : 0,
                    numOccurencesTitle = titleText.match(myRegExp) ? titleText.match(myRegExp).length : 0;


                var title = (numOccurencesTitle > 0 ? "yes" : "no"),
                    inContent = (contentWords.length > 0) ? ((numOccurencesContent / contentWords.length * 100).toFixed(2)) : 0,
                    color = 'blue';
                if (inContent < 3) {
                    color = 'blue';
                } else if (inContent >= 3 && inContent <= 5) {
                    color = 'green';
                } else if (inContent > 5) {
                    color = 'red';
                }
                if (inContent == 0) inContent = Math.round(inContent);
                keywordHtml += '<div class="expresscurate_background_wrap expresscurate_preventTextSelection">\
                            <span class="close">&#215</span>\
                            <div class="statisticsTitle expresscurate_' + color + '"><span>' + value + '</span></div>\
                            <div class="statistics ' + title + ' borderRight">\
                            <div class="center">title</div>\
                            </div>\
                            <div class="statistics">\
                            <div class="center">content<span>' + inContent + '%</span></div>\
                            </div>\
                            </div>';
            });
            jQuery('.expresscurate_background_wrap').remove();
            jQuery('.addKeywords').before(keywordHtml);
        }
    };
    var words_in_text = function (text) {
        var words = [];
        var r = /(?=[^>]*(<|$))[\u0041-\u005A\u0061-\u007A\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0\u08A2-\u08AC\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097F\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191C\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA697\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA80-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC\u0030-\u0039]+/gi;
        while ((m = r.exec(text))) {
            words.push(m[0]);
        }
        return words;
    };

    /*reload widget keywords , post length suggestions*/
    var setupKeywords = function () {
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && jQuery('.expresscurate_widget').length > 0) {
            var check_editor = setTimeout(function check() {
                if (jQuery('#content').length) {
                    clearTimeout(check_editor);
                    updateKeywords();
                    setTimeout(check, 15000);
                }
            }, 1);
        }
    };
    /*insert Keyword in post*/
    function insertKeywordInPost(keyword) {
        var bookmark = tinyMCE.activeEditor.selection.getBookmark(),
            selRng = tinymce.activeEditor.selection.getRng();
        selRng.expand('word');
        tinymce.activeEditor.selection.setRng(selRng);
        var text = tinymce.activeEditor.selection.getContent();
        if (text == '' | text == ' ') {
            keyword = ' ' + keyword;
        } else if (jQuery.trim(text).length > 0) {
            keyword = ' ' + keyword + ' ';
        }
        tinyMCE.activeEditor.selection.moveToBookmark(bookmark);
        tinyMCE.execCommand('mceInsertContent', false, keyword);
    }

    var setupSEOControl = function () {
        setupKeywords();

        if (jQuery.trim(jQuery('textarea[name=expresscurate_description]').val()) == '') {
            jQuery('textarea[name=expresscurate_description]').empty();
        }
        /*add keywords*/
        jQuery('.expresscurate_widget_wrapper .addKeywords input').on("keyup", function (e) {
            if (e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 27) {
                e.preventDefault();
                return;
            }
            var list = jQuery('.addKeywords .suggestion');
            if (e.keyCode == 13) {
                list.remove();
                insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input'), jQuery('.statisticsTitle span')), jQuery('.addKeywords'));
                jQuery('.addKeywords .suggestion').hide();
            } else {
                KeywordUtils.keywordsSuggestions(jQuery(this));
            }
        });
        jQuery('html').on('click', function (e) {
            if (jQuery('.expresscurate_widget_wrapper').length) {
                if (jQuery(e.target).is('.suggestion li')) {
                    var newKeyword = jQuery(e.target).text(),
                        input = jQuery('.expresscurate_widget_wrapper .addKeywords input'),
                        text = input.val();
                    // var lastIndex = text.lastIndexOf(" ");
                    // if(lastIndex > 0){
                    //text = text.substring(0, lastIndex);
                    // text+=' '+newKeyword;
                    // }else {
                    text = newKeyword;
                    // }
                    input.val(text);
                    jQuery('.expresscurate_widget_wrapper .suggestion').remove();
                } else {
                    jQuery('.expresscurate_widget_wrapper .suggestion').remove();
                }
            }
        });

        jQuery('.expresscurate_widget').on("click", '.suggestion li', function (e) {
            var newKeyword = jQuery(this).text(),
                input = jQuery('.expresscurate_widget .addKeywords input'),
                text = input.val();
            text = newKeyword;
            input.val(text);
            insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input'), jQuery('.statisticsTitle span')), jQuery('.addKeywords'));
            jQuery('.suggestion').remove();
        });

        jQuery('#title').on("keyup", function (e) {
            if (e.altKey && e.keyCode == 75) {
                var keyword = "";
                if (window.getSelection) {
                    keyword = window.getSelection().toString();
                } else if (document.selection && document.selection.type != "Control") {
                    keyword = document.selection.createRange().text;
                }
                if (jQuery('#expresscurate_widget').length && keyword.length > 3) {
                    keyword = keyword.replace(/<[^>]+>[^<]*<[^>]+>|<[^\/]+\/>/ig, "");
                    jQuery('.addKeywords input').val(keyword);
                    SEOControl.insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input'), jQuery('.statisticsTitle span')), jQuery('.addKeywords'));
                }
            }
        });
        jQuery('.expresscurate_widget_wrapper').keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        jQuery('.expresscurate_widget_wrapper .addKeywords span span').on('click', function () {
            insertKeywordInWidget(KeywordUtils.multipleKeywords(jQuery('.addKeywords input'), jQuery('.statisticsTitle span')), jQuery('.addKeywords'));
        });
        /*delete keywords*/
        jQuery('.expresscurate_widget_wrapper').on('click touchend', '.expresscurate_background_wrap .close', function () {
            KeywordUtils.close(jQuery(this).parent().find('.statisticsTitle').text(), jQuery(this).parent('.expresscurate_background_wrap'));
        });
        /*meta description*/
        jQuery('.description textarea').on('keyup focus', function () {
            if (jQuery('.expresscurate_widget_wrapper').length) {
                var maxVal = 156,
                    count = jQuery('.description  textarea').val().length,
                    val,
                    textarea = jQuery('.description textarea');
                /*keywords in meta description*/

                var keywords = [],
                    metaDesc = jQuery('.description  textarea').val(),
                    includedKeywordsCount = 0,
                    keywordsCount = 0;
                var defKeywords = jQuery('#expresscurate_defined_tags').val();
                if (defKeywords.length > 0) {
                    keywords = defKeywords.split(', ');
                    for (var i = 0; i < keywords.length; i++) {
                        var myRegExp = new RegExp('((^|\\s|>|))(' + keywords[i] + ')(?=[^>]*(<|$))(?=(&nbsp;|\\s|,|\\.|:|!|\\?|\'|\"|\\;|.?<|$))', 'gmi');
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
            }
        });

        jQuery('.description, .description p').click(function () {
            if (jQuery('.expresscurate_widget_wrapper').length) {
                jQuery('.description  p , .description .hint').removeClass('expresscurate_displayNone');
                jQuery('.description').css({'background-color': '#ffc67d'});
                jQuery('.description  .descriptionWrap').removeClass('textareaBorder');
                jQuery('.description textarea').focus();
            }
        });

        jQuery('.expresscurate_widget_wrapper').click(function () {
            if (jQuery('.expresscurate_widget_wrapper').length) {
                jQuery('.description  .descriptionWrap').addClass('textareaBorder');
                jQuery('.description  p , .description .hint').addClass('expresscurate_displayNone');
                jQuery('.description').css({'background-color': '#ffffff'});
            }
        });

        jQuery(document).click(function (e) {
            if (jQuery('.expresscurate_widget').length > 0 && !jQuery(e.target).parents('#expresscurate').is('div')) {
                jQuery('.description  .descriptionWrap').addClass('textareaBorder');
                jQuery('.description  p , .description .hint').addClass('expresscurate_displayNone');
                jQuery('.description').css({'background-color': '#ffffff'});
            }
            // hide suggestion list if clicked outside
            if (jQuery(e.target).is('.addKeywords .suggestion') || jQuery(e.target).closest('.addKeywords .suggestion').length) {
            } else {
                jQuery('.addKeywords .suggestion').hide();
            }
        });

        // keyboard navigation
        KeywordUtils.suggestionsKeyboardNav(jQuery('#expresscurate_widget .addKeywords input'));
        //

        /*refresh keywords*/
        jQuery('.expresscurate_widget_wrapper label .rotate').click(function () {
            var el = jQuery(this).addClass('rotated');
            setTimeout(function () {
                el.removeClass('rotated');
            }, 1000);
            updateKeywords();
        });
        /*mark keyword*/
        jQuery('.expresscurate_widget_wrapper .mark').on('touchend', function (e) {
            if (e.target == jQuery('.expresscurate_widget_wrapper .mark'))
                Keywords.markEditorKeywords();
        });
        /*insert keyword in content*/
        jQuery('.expresscurate_widget_wrapper').on('dblclick', '.statisticsTitle', function () {/*doubletap*/
            var keyword = jQuery(this).find('span').text();
            insertKeywordInPost(keyword);
        });
        // jQuery('.expresscurate_widget_wrapper').on('mousedown', '.statisticsTitle', function () {
        /*       var keyword = jQuery(this).find('span').text();
         jQuery(this).find('span').draggable({helper: 'clone'});
         tinyMCE.triggerSave();
         jQuery('.mce-edit-area').find('iframe#content_ifr').droppable({
         drop: function (event, ui) {
         alert('dropped'); //NOW FIRES!
         //Dynamically add content
         tinyMCE.activeEditor.execCommand('mceInsertContent', false, 'New content.');
         }
         });*/
        //});
    };

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupSEOControl();
                    isSetup = true;
                });
            }
        },
        insertKeywordInWidget: insertKeywordInWidget,
        updateKeywords: updateKeywords,
        words_in_text: words_in_text
    }
})
(window.jQuery);

SEOControl.setup();
// load KeywordUtils.js and then call setup (ajax request)
//jQuery.getScript("./KeywordUtils.js", function(){
//    SEOControl.setup();
//});