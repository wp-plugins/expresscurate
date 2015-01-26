var ExpresscurateDialog = (function (jQuery) {
    var keywords,
        curatedParagraphs = '',
        shortestParagraphLength = 150,
        paragraphWidth = 93,
        html;

    function sendWPEditor(html, inserted_tags) {
        var editor = tinyMCE.get('content'),
            keywordsInput = jQuery('.addKeywords input');
        keywordsInput.val(inserted_tags);
        if (editor) {
            editor.execCommand("mceInsertContent", true, html);
        } else {
            editor = jQuery('#content');
            if (editor.length === 0) {
                if (tinyMCE.editors.length > 0) {
                    editor = tinyMCE.editors[0];
                    editor.execCommand("mceInsertContent", false, html);
                }
            } else {
                var oldValue = editor.val();
                var selectionStart = editor[0].selectionStart;
                var selectionEnd = editor[0].selectionEnd;

                var newValue = oldValue.substring(0, selectionStart) + html + oldValue.substring(selectionEnd);
                editor.val(newValue);

            }
        }
        setTimeout(SEOControl.insertKeywordInWidget(KeywordUtils.multipleKeywords(keywordsInput, undefined), jQuery('.addKeywords')), 500);
    }

    function displayCuratedImages(images) {
        var editor = jQuery('.expresscurate_dialog .editor');
        jQuery('.imgContainer').hide();
        editor.removeClass('small');
        //
        jQuery.each(images, function (index, value) {
            var img = new Image();
            img.onload = function () {
                var height = this.height,
                    width = this.width;
                if (width > 150 && height > 100) {
                    jQuery('<li id="tcurated_image_' + index + '" class="tcurated_image" data-id="' + index + '" style="background-image: url(' + value + ')"></li>').appendTo("#curated_images");
                    // show image container
                    editor.addClass('small');
                    jQuery('.imgContainer').show();
                }
            };
            img.src = value;
        });
        setTimeout(function () {
            var curatedImages = jQuery('ul#curated_images li');
            if (curatedImages.length > 0) {
                jQuery('.content .img').removeClass("noimage").css('background-image', curatedImages.first().css('background-image'));
                var numberOfImages = curatedImages.length;
                if (numberOfImages > 0) {
                    var counter = jQuery('.expresscurate_dialog .imageCount');
                    counter.text('1/' + numberOfImages).removeClass('expresscurate_displayNone');
                }
            } else {
                var error_html = '<div class="error">No image (of 120x100 or higher res) found in the original article.</div>';
                jQuery('#expresscurate_post_form').before(error_html);
            }
        }, 300);
    }

    function displayCuratedParagraphs(paragraphs, count, shortPar) {
        var paragraphsContainer = jQuery('.paragraphs_preview'),
            text_html = '',
            sorted = [];
        paragraphsContainer.width(paragraphs.length * paragraphWidth);
        jQuery.each(paragraphs, function (index, value) {
            if (value['value'].length > shortestParagraphLength) {
                sorted[index] = value['value'];
            }
        });
        jQuery.each(sorted, function (index, value) {
            if (value) {
                text_html += '<li id="tcurated_text_' + index + '" title="' + value + '" class="expresscurate_tag_' + paragraphs[index].tag + '" onclick="ExpresscurateDialog.insertText(\'tcurated_text_' + index + '\', \'p\')">' + value + '</li>';
                if (index < count && !shortPar) {
                    generateTags(value);
                    tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, "<p>" + value + "<p>");
                }
            }
        });
        var curatedParagraphs = jQuery('#curated_paragraphs');
        curatedParagraphs.find('li').remove();
        jQuery(text_html).appendTo('#curated_paragraphs');
        var liCount = curatedParagraphs.find('li').length;
        paragraphsContainer.width(liCount * paragraphWidth);
        buttonsStatus();
    }

    function searchInParagraphs(search) {
        search = search.toLowerCase().replace(/[,'.";:?!]+/g, '').trim().split(' ');
        search = jQuery.grep(search, function (a) {
            return a !== '';
        });
        var myRegEx = new RegExp('(' + search.join('|') + ')', 'g');

        var searchResult = [];

        jQuery.each(curatedParagraphs, function (index, val) {
            if (val.value.toLowerCase().match(myRegEx) && val.value.length > shortestParagraphLength) {
                searchResult.push(val);
            }
        });
        jQuery('#curated_paragraphs').find('li').remove();
        displayCuratedParagraphs(searchResult, searchResult.length, true);
    }

    function buttonsStatus() {
        var curatedParagraphs = jQuery('#curated_paragraphs');
        var l = parseInt(curatedParagraphs.css('left'));
        var listEnd = curatedParagraphs.width() + l;
        if (l >= 0) {
            jQuery('.prevSlide').addClass('inactiveButton');
        } else {
            jQuery('.prevSlide').removeClass('inactiveButton');
        }
        if (listEnd <= jQuery('.slider').width()) {
            jQuery('.nextSlide').addClass('inactiveButton');
        } else {
            jQuery('.nextSlide').removeClass('inactiveButton');
        }
    }

    function displayCuratedTags(keywords) {
        var keywords_html = '';
        jQuery.each(keywords, function (index, value) {
            keywords_html += '<li  id="curated_post_tag_' + index + '"><span class="tag">' + value + '</span><a href="#" class="remove" onclick="ExpresscurateDialog.delCuratedTag(' + index + '); return false;"></a></li>';
        });
        keywords_html += '<li class="markButton expresscurate_preventTextSelection" onclick="Keywords.markCuratedKeywords();return false;"><span>mark keywords</span></li>';
        jQuery("#curated_tags").html(keywords_html);
    }

    function generateTags(text) {
        var keywords_html = '';
        if (keywords && keywords > 0) {
            jQuery.each(keywords, function (index, value) {
                if (text.indexOf(value) !== -1) {
                    keywords_html += '<li id="curated_post_tag_' + index + '"><a href="#" onclick="ExpresscurateDialog.delCuratedTag(' + index + '); return false;">X</a><span>' + value + '</span></li>';
                    keywords.splice(index, 1);
                }
            });
        }
        jQuery(keywords_html).appendTo("#curated_tags");
    }

    function displaySpecials(data) {
        var specials_html = '';
        specials_html += displayCuratedHeadings(data.headings);
        specials_html += displayCuratedDescription(data.metas.description);
        specials_html += displayShortParagraphs();
        specials_html += displayCuratedParagraphsSearch();
        if (specials_html.length === 0) {
            specials_html += '<li>No specal data</li>';
        }
        jQuery(specials_html).appendTo('#expresscurate_special');
    }

    function displayCuratedHeadings(headings) {
        var headings_html = '';
        if (headings.h1 && headings.h1.length > 0) {

            headings_html += '<li id="curated_heading_h1" onclick="ExpresscurateDialog.insertText(\'curated_heading_h1\', \'p\');" data-tag="h1" title="' + headings.h1 + '">H1</li>';
        }
        if (headings.h2 && headings.h2.length > 0) {
            headings_html += '<li id="curated_heading_h2" onclick="ExpresscurateDialog.insertText(\'curated_heading_h2\', \'li\');" data-tag="h2" title="' + headings.h2 + '">H2</li>';
        }
        if (headings.h3 && headings.h3.length > 0) {
            headings_html += '<li id="curated_heading_h3" onclick="ExpresscurateDialog.insertText(\'curated_heading_h3\', \'li\');" data-tag="h3" title="' + headings.h3 + '">H3</li>';
        }
        return headings_html;
    }

    function displayShortParagraphs() {
        return '<li class="expresscurate_preventTextSelection expresscurate_dialog_shortPar expresscurate_shortParInactiveColor">\
            <label>Short Paragraphs</label>\
            <span class="shortPButton shortPButtonInactive"><span></span></span>\
        </li>';
    }

    function displayCuratedParagraphsSearch() {
        return '<li class="expresscurate_preventTextSelection expresscurate_dialog_search">\
            <input class="expresscurate_disableInputStyle expresscurate_displayNone"/>\
            <span class="close expresscurate_displayNone"></span>\
            <span class="icon"></span>\
        </li>';
    }

    function displayCuratedDescription(description) {
        var description_html = '';
        if (description && description.length > 0) {
            description_html += '<li id="curated_description" onclick="ExpresscurateDialog.insertText(\'curated_description\', \'p\')"; title="' + description + '">Description</li>';
        }
        return description_html;
    }

    function insertText(id, tag) {
        var paragraph = '';
        if (tag === 'li') {
            paragraph += "<ul>";
            var lis = jQuery("#" + id).attr('title');
            lis = lis.split(/\r?\n/);
            jQuery.each(lis, function (index, value) {
                if (value) {
                    paragraph += "<li>" + value + "</li>";
                }
            });
            paragraph += "</ul>";
        } else {
            paragraph += "<" + tag + ">" + jQuery("#" + id).attr('title').replace(/\r\n/g, "<br />").replace(/\n/g, "<br />") + "</" + tag + "> &nbsp;";
        }
        generateTags(paragraph);
        tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, paragraph);
    }

    function delCuratedTag(index) {
        jQuery("#curated_post_tag_" + index).fadeOut(7000).remove();
        return false;
    }

    function insertDeleteImage(index) {
        var selectedImages = jQuery("#curated_content_selected_img"),
            curatedImage = jQuery("#tcurated_image_" + index);
        if (curatedImage.parent().attr('id') === 'curated_images') {
            if (selectedImages.find("img").length === 0) {
                curatedImage.appendTo(selectedImages);
                jQuery('#curated_images').find('#tcurated_image_' + index).remove();
            } else {
                jQuery("#" + selectedImages.find("img").parent().attr('id')).appendTo('#curated_images');
                jQuery("#curated_content_selected_img #" + selectedImages.find("img").parent().attr('id')).remove();
                curatedImage.appendTo(selectedImages);
                jQuery('#curated_images').find('#tcurated_image_' + index).remove();
            }
        } else if (curatedImage.parent().attr('id') === 'curated_content_selected_img') {
            curatedImage.appendTo('#curated_images');
            selectedImages.find('#tcurated_image_' + index).remove();
        }
    }

    function clearExpresscurateForm() {
        var dialog = jQuery('#expresscurate_dialog');
        dialog.find('div.error').remove();
        dialog.find('div.updated').remove();
        dialog.find('ul').html('');
        jQuery("#expresscurate_content_editor").val('');
        jQuery('.content .img').attr('style', '').addClass("noimage");
        jQuery('.controls').hide();
        jQuery("#curated_paragraphs").empty();
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
            tinyMCE.get('expresscurate_content_editor').setContent('');
        }
        jQuery('#expresscurate_source').focus();
    }

    function closeSearch() {
        var input = jQuery('.expresscurate_dialog_search input'),
            close = jQuery('.expresscurate_dialog_search .close'),
            icon = jQuery('.expresscurate_dialog_search .icon');
        input.addClass('expresscurate_displayNone');
        close.addClass('expresscurate_displayNone');
        icon.removeClass('expresscurate_displayNone');
        jQuery('.expresscurate_dialog_search').removeClass('active');
        input.val('');
        displayCuratedParagraphs(curatedParagraphs, curatedParagraphs.length, true);
    }

    function submitExpresscurateForm() {
        var dialog = jQuery('#expresscurate_dialog');
        //remove error divs
        dialog.find('div.error').remove();
        dialog.find('div.updated').remove();
        dialog.fadeIn();
        var error_html = '',
            notif_html = '',
            url = jQuery('#expresscurate_post_form').find('input');
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_get_article&check=1',
            data: url.serialize()
        }).done(function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'notification') {
                notif_html = '<div class="error">' + data.msg + '</div>';
                jQuery('#expresscurate_post_form').before(notif_html);
            }
        });
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_get_article',
            data: url.serialize()
        }).done(function (res) {
            var data = jQuery.parseJSON(res);
            if (data) {
                if (data.status === 'error') {
                    error_html = '<div class="error">' + data.error + '</div>';
                    jQuery('#expresscurate_post_form').before(error_html);
                    jQuery("#expresscurate_loading").fadeOut('fast');
                } else if (data.status === 'success') {
                    clearExpresscurateForm();
                    jQuery(".controls").show();
                    if (data.result.title && data.result.title.length > 0) {
                        jQuery("#curated_title").val(data.result.title);
                    }
                    if (data.result.images.length > 0) {
                        jQuery.ajax({
                            type: 'POST',
                            url: 'admin-ajax.php?action=expresscurate_export_api_check_images',
                            data: {
                                img_url: data.result.images[data.result.images.length - 1],
                                img_url2: data.result.images[data.result.images.length - 2]
                            }
                        }).done(function (res) {
                            var data_check = jQuery.parseJSON(res);
                            if (data_check.status === 'success' && data_check.statusCode === 200) {
                                displayCuratedImages(data.result.images);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            } else if (data_check.status === 'fail' && data_check.statusCode === 200) {
                                jQuery('.content .img').css('background-image', jQuery('#expresscurate_loading').find('img').attr('src'));
                                jQuery.ajax({
                                    type: 'POST',
                                    url: 'admin-ajax.php?action=expresscurate_export_api_download_images',
                                    data: {
                                        images: data.result.images,
                                        post_id: jQuery('#post_ID').val()
                                    }
                                }).done(function (res) {
                                    var data_images = jQuery.parseJSON(res);
                                    if (data_images.status === 'error') {
                                        error_html = '<div class="error">' + data_images.error + '</div>';
                                        jQuery('#expresscurate_post_form').before(error_html);
                                    } else if (data_images.status === 'success') {
                                        displayCuratedImages(data_images.images);
                                    }
                                    jQuery("#expresscurate_loading").fadeOut('fast');
                                });
                            }
                            else if (data_check.status === 'error') {
                                error_html = '<div class="error">' + data_check.msg + '</div>';
                                jQuery('#expresscurate_post_form').before(error_html);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            }
                            else {
                                displayCuratedImages(data.result.images);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            }
                        });
                    } else {
                        jQuery("#expresscurate_loading").fadeOut('fast');
                    }
                    if (data.result.metas.keywords && data.result.metas.keywords.length > 0) {
                        displayCuratedTags(data.result.metas.keywords);
                    }
                    keywords = data.result.metas.keywords;
                    displaySpecials(data.result);

                    if (data.result.paragraphs.length > 0) {
                        curatedParagraphs = data.result.paragraphs;
                        displayCuratedParagraphs(data.result.paragraphs, jQuery("#expresscurate_autosummary").val(), false);
                    }
                    jQuery('#expresscurate_source').focus();
                }
            } else {
                error_html = '<div class="error">Can\'t curate from this page</div>';
                jQuery('#expresscurate_post_form').before(error_html);
                jQuery("#expresscurate_loading").fadeOut('fast');
            }

        });
    }

    function setupDialog() {
        buttonsStatus();
        jQuery().on('click','.tcurated_image',function(){
            var index=jQuery(this).data('id');
            ExpresscurateDialog.insertDeleteImage(index);
        });
        jQuery('.nextSlide').click(function () {
            if (!jQuery(this).hasClass('inactiveButton')) {
                var curatedParagraphs = jQuery('#curated_paragraphs'),
                    l = Math.floor((parseInt(curatedParagraphs.css('left')) - 3 * paragraphWidth) / paragraphWidth) * paragraphWidth,
                    slider = jQuery('.slider');
                if (curatedParagraphs.width() + l <= slider.width()) {
                    l = slider.width() - curatedParagraphs.width();
                    jQuery(this).addClass('inactiveButton');
                }
                curatedParagraphs.stop(true, true).animate({
                    'left': l + 'px'
                }, {
                    duration: 300,
                    always: function () {
                        buttonsStatus();
                    }
                });
            }
        });
        jQuery('.prevSlide').click(function () {
            if (!jQuery(this).hasClass('inactiveButton')) {
                var curatedParagraphs = jQuery('#curated_paragraphs'),
                    l = Math.floor((parseInt(curatedParagraphs.css('left')) + 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
                if (l >= 0) {
                    l = 0;
                    jQuery(this).addClass('inactiveButton');
                }
                curatedParagraphs.stop(true, true).animate({
                    'left': l + 'px'
                }, {
                    duration: 300,
                    always: function () {
                        buttonsStatus();
                    }
                });
            }
        });

        jQuery('.expresscurate_tabMenu a').hover(function () {
            var menuItemWidth = jQuery(this).width(),
                index = jQuery(this).index();
            jQuery('.expresscurate_tabMenu .arrow').css({'left': (index * menuItemWidth) - menuItemWidth / 2 + 30 + 'px'});
        });
        jQuery('.expresscurate_tabMenu').mouseleave(function () {
            Menu.moveMenuArrow();
        });

        //

        jQuery('textarea[name=expresscurate_add_tags]').val('');
        if (jQuery.ui) {
            var $dialog = jQuery("#expresscurate_dialog");
            if ($dialog.length) {
                $dialog.dialog({
                    'dialogClass': 'wp-dialog',
                    'modal': true,
                    'autoOpen': false,
                    'closeOnEscape': true,
                    'width': '829px',
                    'height': 'auto',
                    'resizable': false,
                    'close': clearExpresscurateForm
                });
            } else {
                $dialog = jQuery("#expresscurate_dialog_theme");
                $dialog.dialog({
                    'dialogClass': 'wp-dialog',
                    'modal': true,
                    'autoOpen': false,
                    'closeOnEscape': true,
                    'width': '829px',
                    'resizable': false,
                    'close': clearExpresscurateForm
                });
            }

            jQuery("#expresscurate_content_editor").addClass("mceEditor");

            var currentImage = 0;
            var numberOfImages = 0;

            jQuery('.prevImg, .nextImg, .expresscurate_dialog .img').click(function () {
                numberOfImages = jQuery('ul#curated_images li').length;
                if (jQuery(this).hasClass('next') || jQuery(this).hasClass('img')) {
                    currentImage = (++currentImage > numberOfImages - 1) ? 0 : currentImage;
                } else if (jQuery(this).hasClass('prev')) {
                    currentImage = (--currentImage < 0) ? numberOfImages - 1 : currentImage;
                }
                var img = jQuery('ul#curated_images li:eq(' + currentImage + ')').css('background-image');
                if (img) {
                    jQuery('.content .img').css('background-image', img);
                    if (numberOfImages > 0) {
                        jQuery('.expresscurate_dialog .imageCount').text((currentImage + 1) + '/' + numberOfImages).removeClass('expresscurate_displayNone');
                    }
                }
            });

            var alignImg = 'alignnone';
            jQuery('.alignL').click(function () {
                alignImg = 'alignleft';
            });
            jQuery('.alignNone').click(function () {
                alignImg = 'alignnone';
            });
            jQuery('.alignR').click(function () {
                alignImg = 'alignright';
            });

            var imgSize = 'sizeX';
            jQuery('.sizeX').click(function () {
                imgSize = 'sizeX';
            });
            jQuery('.sizeM').click(function () {
                imgSize = 'sizeM';
            });
            jQuery('.sizeS').click(function () {
                imgSize = 'sizeS';
            });

            jQuery('.sizeS, .sizeM, .sizeX').each(function () {
                jQuery(this).click(function () {
                    jQuery('.sizeS, .sizeM, .sizeX').removeClass('active');
                    jQuery(this).addClass('active');
                });
            });

            jQuery('.alignL , .alignR , .alignNone').click(function () {
                jQuery('.imgAlign').removeClass('active');
                jQuery(this).addClass('active');
            });
            jQuery("#expresscurate_open-modal").click(function (event) {
                event.preventDefault();
                openDialog();
            });
            jQuery("#expresscurate_insert").click(function () {
                var ed = tinyMCE.activeEditor;
                var highlightedElems = jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                if (highlightedElems.length > 0) {
                    highlightedElems.each(function (index, val) {
                        jQuery(val).replaceWith(this.childNodes);
                    });
                }
                var inserted_tags_textarea = "",
                    sourceVal = jQuery('#expresscurate_source').val(),
                    postTag = jQuery("#tax-input-post_tag");
                inserted_tags_textarea = postTag.val();
                jQuery('#curated_tags').find('li').each(function () {
                    inserted_tags_textarea += "," + jQuery(this).find('span.tag').text();
                });
                postTag.val(inserted_tags_textarea);
                jQuery(".tagadd").trigger('click');
                jQuery('.expresscurate_sources_coll_widget .addSource input').val(sourceVal);
                SourceCollection.addNew();
                var html = "";
                var insite_html = '';
                var bg = jQuery('.img').css('background-image');

                bg = bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                if (bg.indexOf('images/noimage.png') === -1 && bg.length > 5) {
                    ///html += jQuery("#curated_content_selected_img li").html();
                    html += '<img class="' + alignImg + ' ' + imgSize + '" src="' + bg + '" data-img-curated-from="' + sourceVal + '">'
                }
                if (tinyMCE.get('expresscurate_content_editor').getContent().length > 0) {
                    html += '<blockquote cite = "' + sourceVal + '">' + tinyMCE.get('expresscurate_content_editor').getContent() + '<br />';
                }
                html += insite_html;
                if (html.length > 0) {
                    if (sourceVal.length > 0) {
                        var domain = sourceVal;
                        if (domain.indexOf('http://') === -1 && domain.indexOf('https://') === -1) {
                            domain = 'http://' + domain;
                        }
                        var title = jQuery("#curated_title").val();
                        domain = domain.match(/^(http|https)/) ? domain : 'http://' + domain;
                        if (domain) {
                            html += '<cite><p class="expresscurate_source">' + jQuery("#expresscurate_from").val() + ' <cite><a class="expresscurated" rel="nofollow" data-curated-url="' + domain + '"  href = "' + domain + '">' + title + '</a></p></cite><br/>';
                        }
                    }
                    html += '</blockquote><br />';
                    var $title = jQuery('#titlewrap').find('#title');
                    if ($title.val().length === 0) {
                        $title.trigger('focus');
                        $title.val(jQuery("#curated_title").val());
                    }
                    sendWPEditor(html, inserted_tags_textarea);
                    $dialog.dialog('close');
                } else {
                    return false;
                }
            });
        }

        jQuery('#expresscurate_submit').click(function () {
            jQuery("#expresscurate_loading").show();
            submitExpresscurateForm();
            jQuery(document).ajaxComplete(function () {

            });
        });
        jQuery('#expresscurate_source').keypress(function (e) {
            if (e.keyCode === 13 || e.keyCode === 40) {
                submitExpresscurateForm();
                return false;
            }
        });

        html.on('click', '.expresscurate_dialog_search .icon', function () {
            var input = jQuery('.expresscurate_dialog_search input'),
                close = jQuery('.expresscurate_dialog_search .close'),
                icon = jQuery('.expresscurate_dialog_search .icon');
            if (input.hasClass('expresscurate_displayNone')) {
                input.removeClass('expresscurate_displayNone');
                close.removeClass('expresscurate_displayNone');
                icon.addClass('expresscurate_displayNone');
                jQuery('.expresscurate_dialog_search').addClass('active');
                input.focus();
            } else {
                searchInParagraphs(input.val());
            }
        });
        html.on('keyup', '.expresscurate_dialog_search input', function (e) {
            if (e.keyCode === 13) {
                searchInParagraphs(jQuery(this).val());
            }
        });

        html.on('click', '.expresscurate_dialog_search .close', function () {
            closeSearch();
        });

        html.on('click', '.expresscurate_dialog_shortPar .shortPButton', function () {
            var elem = jQuery(this);
            if (shortestParagraphLength === 150) {
                shortestParagraphLength = 0;
                elem.addClass('shortPButtonActive').removeClass('shortPButtonInactive');
            } else {
                elem.addClass('shortPButtonInactive').removeClass('shortPButtonActive');
                shortestParagraphLength = 150;
            }
            var searchInput = jQuery('.expresscurate_dialog_search input');
            if (!searchInput.hasClass('expresscurate_displayNone')) {
                searchInParagraphs(searchInput.val());
            } else {
                displayCuratedParagraphs(curatedParagraphs, curatedParagraphs.length, true);
            }
        });
    }

    function openDialog(source) {
        var $dialog = jQuery("#expresscurate_dialog");
        $dialog.dialog({
            'dialogClass': 'wp-dialog',
            'modal': true,
            'autoOpen': false,
            'closeOnEscape': true,
            'width': '829px',
            'height': 'auto',
            'resizable': false,
            'open': function () {
                if (source) {
                    jQuery('#expresscurate_source').val(source);
                    jQuery('#expresscurate_submit').trigger("click");
                }
            },
            'close': clearExpresscurateForm
        });
        $dialog.dialog('open');
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                html = jQuery('html');
                jQuery(document).ready(function () {
                    setupDialog();
                    isSetup = true;
                    setTimeout(function () {
                        if (window.expresscurate_load_url) {
                            openDialog(window.expresscurate_load_url);
                        }
                    }, 0);
                });
                html.on('keyup', '#expresscurate_source', function () {
                    var input = jQuery(this),
                        li_html = '',
                        list = jQuery('.expresscurate_dialog .autoComplete');
                    if (input.val().length > 1) {
                        jQuery.ajax({
                            type: 'POST',
                            url: 'admin-ajax.php?action=expresscurate_search_feed_bookmark',
                            data: {searchKeyword: input.val()}
                        }).done(function (res) {
                            var data = jQuery.parseJSON(res);
                            jQuery.each(data.slice(0, 5), function (key, value) {
                                li_html += '<li data-link="' + value.link + '">' + value.title + '</li>';
                            });
                            if (li_html.length > 0) {
                                input.after('<ul class="autoComplete">' + li_html + '</ul>');
                            } else {
                                list.remove();
                            }
                        });

                    } else {
                        list.remove();
                    }
                });
                html.on('click', '.expresscurate_dialog .autoComplete li', function () {
                    var li = jQuery(this);
                    jQuery('#expresscurate_source').val(li.data('link'));
                    jQuery('#curated_title').val(li.text());
                    jQuery('.expresscurate_dialog .autoComplete').remove();
                });
            }
        },
        insertText: insertText,
        openDialog: openDialog,
        delCuratedTag: delCuratedTag
    }
})(window.jQuery);

ExpresscurateDialog.setup();