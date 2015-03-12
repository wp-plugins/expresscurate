var ExpresscurateDialog = (function ($) {
    var keywords,
        $curatedParagraphs = '',
        shortestParagraphLength = 150,
        paragraphWidth = 93,
        html;

    function sendWPEditor(html, insertedTags) {
        var $editor = tinyMCE.get('content'),
            $keywordsInput = $('.addKeywords input');
        $keywordsInput.val(insertedTags);
        if ($editor) {
            $editor.execCommand("mceInsertContent", true, html);
        } else {
            $editor = $('#content');
            if ($editor.length === 0) {
                if (tinyMCE.editors.length > 0) {
                    $editor = tinyMCE.editors[0];
                    $editor.execCommand("mceInsertContent", false, html);
                }
            } else {
                var oldValue = $editor.val(),
                    selectionStart = $editor[0].selectionStart,
                    selectionEnd = $editor[0].selectionEnd,
                    newValue = oldValue.substring(0, selectionStart) + html + oldValue.substring(selectionEnd);
                $editor.val(newValue);
            }
        }
        setTimeout(ExpressCurateSEOControl.insertKeywordInWidget(ExpressCurateKeywordUtils.multipleKeywords($keywordsInput, undefined), $('.addKeywords')), 500);
    }

    function displayCuratedImages(images) {
        var $editor = $('.expresscurate_dialog .editor'),
            count = images.length,
            validImgCount = 0,
            $counter = $('.expresscurate_dialog .imageCount');
        $('.imgContainer').hide();
        $editor.removeClass('small');

        $.each(images, function (index, value) {
            var img = new Image(),
                height,
                width;
            img.onload = function () {
                height = this.height;
                width = this.width;
                if (width > 150 && height > 100) {
                    validImgCount++;
                    var data = {
                        index: index,
                        url: value
                    };
                    $('#curated_images').append(ExpressCurateUtils.getTemplate('dialogCuratedImage', data));
                    $counter.text('1/' + validImgCount).removeClass('expresscurate_displayNone');
                    // show image container
                    $editor.addClass('small');
                    $('.imgContainer').show();
                }
            };
            img.src = value;
            if (index === images.length - 1) {
                setTimeout(function () {
                    var $curatedImages = $('ul#curated_images li'),
                        numberOfImages = $curatedImages.length,
                        errorHTML = '';
                    if (numberOfImages > 0) {
                        $('.content .img').removeClass("noimage").css('background-image', $curatedImages.first().css('background-image'));
                    } else {
                        errorHTML = '<div class="error">No image (of 120x100 or higher res) found in the original article.</div>';
                        $('#expresscurate_post_form').before(errorHTML);
                    }
                }, 300);
            }
        });
    }
    function displayCuratedParagraphs(paragraphs, count, shortPar) {
        var $paragraphsContainer = $('.paragraphs_preview'),
            textHTML = '',
            $sorted = [],
            $curatedParagraphs,
            liCount;
        $paragraphsContainer.width(paragraphs.length * paragraphWidth);
        $.each(paragraphs, function (index, value) {
            if (value['value'].length > shortestParagraphLength) {
                $sorted.push(value['value']);
            }
        });
        $.each($sorted, function (index, value) {
            if (value) {
                var data = {
                    index: index,
                    title: value,
                    tag: paragraphs[index].tag
                };
                textHTML += ExpressCurateUtils.getTemplate('dialogCuratedParagraphs', data);
                if (index < count && !shortPar) {
                    generateTags(value);
                    tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, "<p>" + value + "<p>");
                }
            }
        });

        $curatedParagraphs = $('#curated_paragraphs');
        $curatedParagraphs.find('li').remove();
        $(textHTML).appendTo('#curated_paragraphs');
        liCount = $curatedParagraphs.find('li').length;
        $paragraphsContainer.width(liCount * paragraphWidth);
        buttonsStatus();
    }

    function searchInParagraphs(search) {
        search = search.toLowerCase().replace(/[,'.";:?!]+/g, '').trim().split(' ');
        search = $.grep(search, function (a) {
            return a !== '';
        });
        var myRegEx = new RegExp('(' + search.join('|') + ')', 'g'),
            searchResult = [];
        $.each($curatedParagraphs, function (index, val) {
            if (val.value.toLowerCase().match(myRegEx) && val.value.length > shortestParagraphLength) {
                searchResult.push(val);
            }
        });
        $('#curated_paragraphs').find('li').remove();
        displayCuratedParagraphs(searchResult, searchResult.length, true);
    }

    function buttonsStatus() {
        var $curatedParagraphs = $('#curated_paragraphs'),
            l = parseInt($curatedParagraphs.css('left')),
            listEnd = $curatedParagraphs.width() + l,
            $prevButton = $('.prevSlide'),
            $nextButton = $('.nextSlide');
        if (l >= 0) {
            $prevButton.addClass('inactiveButton');
        } else {
            $prevButton.removeClass('inactiveButton');
        }
        if (listEnd <= $('.slider').width()) {
            $nextButton.addClass('inactiveButton');
        } else {
            $nextButton.removeClass('inactiveButton');
        }
    }

    function displayCuratedTags(keywords) {
        var keywordsHTML = '';
        $.each(keywords, function (index, value) {
            var data = {
                index: index,
                tag: value
            };
            keywordsHTML += ExpressCurateUtils.getTemplate('dialogCuratedtags', data);
        });
        keywordsHTML += ExpressCurateUtils.getTemplate('dialogMarkButton', null);
        $("#curated_tags").append(keywordsHTML);
    }

    function generateTags(text) {
        var keywordsHTML = '';
        if (keywords && keywords > 0) {
            $.each(keywords, function (index, value) {
                if (text.indexOf(value) !== -1) {
                    keywordsHTML += ExpressCurateUtils.getTemplate('dialogInsertTags', data);
                    keywords.splice(index, 1);
                }
            });
        }
        $(keywordsHTML).appendTo("#curated_tags");
    }

    function displaySpecials(data) {
        var specialsHTML = '';
        specialsHTML += displayCuratedHeadings(data.headings);
        specialsHTML += displayCuratedDescription(data.metas.description);
        specialsHTML += ExpressCurateUtils.getTemplate('dialogSearchParagraphs', null);
        if (specialsHTML.length === 0) {
            specialsHTML += '<li>No specal data</li>';
        }
        $(specialsHTML).appendTo('#expresscurate_special');
    }

    function displayCuratedHeadings(headings) {
        var headingsHTML = '';
        $.each(headings, function (index, value) {
            if (index && value.length > 0) {
                var data = {
                    index: index,
                    content: value
                };
                headingsHTML += ExpressCurateUtils.getTemplate('dialogCuratedHeadings', data);
            }
        });
        return headingsHTML;
    }

    function displayCuratedDescription(description) {
        var descriptionHTML = '';
        if (description && description.length > 0) {
            descriptionHTML += ExpressCurateUtils.getTemplate('dialogCuratedDescription', description);
        }
        return descriptionHTML;
    }

    function insertText(id, tag) {
        var paragraph = '',
            lis;
        if (tag === 'li') {
            paragraph += "<ul>";
            lis = $("#" + id).attr('title');
            lis = lis.split(/\r?\n/);
            $.each(lis, function (index, value) {
                if (value) {
                    paragraph += "<li>" + value + "</li>";
                }
            });
            paragraph += "</ul>";
        } else {
            paragraph += "<" + tag + ">" + $('#' + id).attr('title').replace(/\r\n/g, "<br />").replace(/\n/g, "<br />") + "</" + tag + "> &nbsp;";
        }
        generateTags(paragraph);
        tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, paragraph);
    }

    function delCuratedTag(index) {
        $("#curated_post_tag_" + index).fadeOut(7000).remove();
        return false;
    }

    function clearExpresscurateForm() {
        var $dialog = $('#expresscurate_dialog');
        $dialog.find('div.error').remove();
        $dialog.find('div.updated').remove();
        $dialog.find('ul').html('');
        $("#expresscurate_content_editor").val('');
        $('.content .img').attr('style', '').addClass("noimage");
        $('.controls').hide();
        $("#curated_paragraphs").empty();
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function" && tinyMCE.get('expresscurate_content_editor')) {
            tinyMCE.get('expresscurate_content_editor').setContent('');
        }
        $('#expresscurate_source').focus();
    }

    function closeSearch() {
        var $search = $('#expresscurate_dialog .expresscurate_dialog_search'),
            $input = $search.find('input'),
            $close = $search.find('.close'),
            $icon = $search.find('.icon');
        $input.add($close).addClass('expresscurate_displayNone');
        $icon.removeClass('expresscurate_displayNone');
        $search.removeClass('active');
        $input.val('');
        displayCuratedParagraphs($curatedParagraphs, $curatedParagraphs.length, true);
    }

    function exportAPICheckImages(images) {
        var errorHTML;
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_export_api_check_images',
            data: {
                img_url: images[images.length - 1],
                img_url2: images[images.length - 2]
            }
        }).done(function (res) {
            var dataCheck = $.parseJSON(res);
            if (dataCheck.status === 'success' && dataCheck.statusCode === 200) {
                displayCuratedImages(images);
                $("#expresscurate_loading").fadeOut('fast');
            } else if (dataCheck.status === 'fail' && dataCheck.statusCode === 200) {
                $('.content .img').css('background-image', $('#expresscurate_loading').find('img').attr('src'));
                errorHTML = exportAPIDownloadImages(images, $('#post_ID').val());
            } else if (dataCheck.status === 'error') {
                errorHTML = '<div class="error">' + dataCheck.msg + '</div>';
                $('#expresscurate_post_form').before(errorHTML);
                $("#expresscurate_loading").fadeOut('fast');
            } else {
                displayCuratedImages(images);
                $("#expresscurate_loading").fadeOut('fast');
            }
        });
        return errorHTML;
    }

    function exportAPIDownloadImages(images, postID) {
        var errorHTML;
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_export_api_download_images',
            data: {
                images: images,
                post_id: postID
            }
        }).done(function (res) {
            var dataImages = $.parseJSON(res);
            if (dataImages.status === 'error') {
                errorHTML = '<div class="error">' + dataImages.error + '</div>';
                $('#expresscurate_post_form').before(errorHTML);
            } else if (dataImages.status === 'success') {
                displayCuratedImages(dataImages.images);
            }
            $("#expresscurate_loading").fadeOut('fast');
        });
        return errorHTML;
    }

    function submitExpresscurateForm() {
        ExpressCurateUtils.track('/post/content-dialog/loadpage');

        var $dialog = $('#expresscurate_dialog');
        //remove autoComplete
        $dialog.find('.autoComplete').remove();
        //remove error divs
        $dialog.find('div.error').remove();
        $dialog.find('div.updated').remove();
        $dialog.fadeIn();
        var errorHTML = '',
            notifHTML = '',
            $url = $('#expresscurate_post_form').find('input');
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_get_article&check=1',
            data: $url.serialize()
        }).done(function (res) {
            var data = $.parseJSON(res);
            if (data.status === 'notification') {
                notifHTML = '<div class="error">' + data.msg + '</div>';
                $('#expresscurate_post_form').before(notifHTML);
            }
        });
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_get_article',
            data: $url.serialize()
        }).done(function (res) {
            var data = $.parseJSON(res);
            if (data) {
                if (data.status === 'error') {
                    errorHTML = '<div class="error">' + data.error + '</div>';
                    $('#expresscurate_post_form').before(errorHTML);
                    $("#expresscurate_loading").fadeOut('fast');
                } else if (data.status === 'success') {
                    clearExpresscurateForm();
                    $(".controls").show();
                    if (data.result.title && data.result.title.length > 0) {
                        $("#curated_title").val(data.result.title);
                    }
                    if (data.result.images.length > 0) {
                        errorHTML = exportAPICheckImages(data.result.images);
                    } else {
                        $("#expresscurate_loading").fadeOut('fast');
                    }
                    if (data.result.metas.keywords && data.result.metas.keywords.length > 0) {
                        displayCuratedTags(data.result.metas.keywords);
                    }
                    keywords = data.result.metas.keywords;
                    displaySpecials(data.result);

                    if (data.result.paragraphs.length > 0) {
                        $curatedParagraphs = data.result.paragraphs;
                        displayCuratedParagraphs(data.result.paragraphs, $("#expresscurate_autosummary").val(), false);
                    }
                    $('#expresscurate_source').focus();
                }
            } else {
                errorHTML = '<div class="error">Can\'t curate from this page</div>';
                $('#expresscurate_post_form').before(errorHTML);
                $("#expresscurate_loading").fadeOut('fast');
            }

        });
    }

    function setupDialog() {
        var $dialog = $('#expresscurate_dialog');
        buttonsStatus();
        $dialog.on('click', '.tcurated_text', function () {
            var index = $(this).data('id');
            insertText('tcurated_text_' + index, 'p');
        });
        $dialog.on('click', '.curated_post_tag .remove', function () {
            var index = $(this).data('id');
            delCuratedTag(index);
            return false;
        });
        $dialog.on('click', '.markButton', function () {
            ExpressCurateKeywords.markCuratedKeywords();
            return false;
        });
        $dialog.on('click', '.curated_heading', function () {
            insertText('curated_heading_' + $(this).data('tag'), 'p');
        });
        $dialog.on('click', '#curated_description', function () {
            insertText('curated_description', 'p');
        });
        $('.nextSlide').click(function () {
            if (!$(this).hasClass('inactiveButton')) {
                var $curatedParagraphs = $('#curated_paragraphs'),
                    l = Math.floor((parseInt($curatedParagraphs.css('left')) - 3 * paragraphWidth) / paragraphWidth) * paragraphWidth,
                    slider = $('.slider');
                if ($curatedParagraphs.width() + l <= slider.width()) {
                    l = slider.width() - $curatedParagraphs.width();
                    $(this).addClass('inactiveButton');
                }
                $curatedParagraphs.stop(true, true).animate({
                    'left': l + 'px'
                }, {
                    duration: 300,
                    always: function () {
                        buttonsStatus();
                    }
                });
            }
        });
        $('.prevSlide').click(function () {
            if (!$(this).hasClass('inactiveButton')) {
                var $curatedParagraphs = $('#curated_paragraphs'),
                    l = Math.floor((parseInt($curatedParagraphs.css('left')) + 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
                if (l >= 0) {
                    l = 0;
                    $(this).addClass('inactiveButton');
                }
                $curatedParagraphs.stop(true, true).animate({
                    'left': l + 'px'
                }, {
                    duration: 300,
                    always: function () {
                        buttonsStatus();
                    }
                });
            }
        });

        $('.expresscurate_tabMenu a').hover(function () {
            var menuItemWidth = $(this).width(),
                index = $(this).index();
            $('.expresscurate_tabMenu .arrow').css({'left': (index * menuItemWidth) - menuItemWidth / 2 + 30 + 'px'});
        });
        $('.expresscurate_tabMenu').mouseleave(function () {
            Menu.moveMenuArrow();
        });

        //

        $('textarea[name=expresscurate_add_tags]').val('');
        if ($.ui) {
            var $dialog = $("#expresscurate_dialog");
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
                $dialog = $("#expresscurate_dialog_theme");
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

            $("#expresscurate_content_editor").addClass("mceEditor");

            var currentImage = 0;
            var numberOfImages = 0;

            $('.prevImg, .nextImg, .expresscurate_dialog .img').click(function () {
                numberOfImages = $('ul#curated_images li').length;
                if ($(this).hasClass('next') || $(this).hasClass('img')) {
                    currentImage = (++currentImage > numberOfImages - 1) ? 0 : currentImage;
                } else if ($(this).hasClass('prev')) {
                    currentImage = (--currentImage < 0) ? numberOfImages - 1 : currentImage;
                }
                var img = $('ul#curated_images li:eq(' + currentImage + ')').css('background-image');
                if (img) {
                    $('.content .img').css('background-image', img);
                    if (numberOfImages > 0) {
                        $('.expresscurate_dialog .imageCount').text((currentImage + 1) + '/' + numberOfImages).removeClass('expresscurate_displayNone');
                    }
                }
            });


            var imgSize = 'sizeX';
            $('.sizeS, .sizeM, .sizeX').on('click', function () {
                var $this = $(this);
                $('.sizeS, .sizeM, .sizeX').removeClass('active');
                imgSize = $this.attr('class');
                $this.addClass('active');
            });

            var alignImg = 'alignnone';
            $('.alignleft , .alignright , .alignnone').on('click', function () {
                var $this = $(this);
                $('.imgAlign').removeClass('active');
                alignImg = $this.attr('class');
                $this.addClass('active');
            });
            $("#expresscurate_open-modal").click(function (event) {
                event.preventDefault();
                openDialog();
            });
            $("#expresscurate_insert").click(function () {
                var ed = tinyMCE.activeEditor,
                    highlightedElems = $(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                if (highlightedElems.length > 0) {
                    highlightedElems.each(function (index, val) {
                        $(val).replaceWith(this.childNodes);
                    });
                }
                var insertedTagsTextarea = "",
                    sourceVal = $('#expresscurate_source').val(),
                    postTag = $("#tax-input-post_tag");
                insertedTagsTextarea = postTag.val();
                $('#curated_tags').find('li').each(function () {
                    insertedTagsTextarea += "," + $(this).find('span.tag').text();
                });
                postTag.val(insertedTagsTextarea);
                $(".tagadd").trigger('click');
                $('.expresscurate_sources_coll_widget .addSource input').val(sourceVal);
                ExpressCurateSourceCollection.addNew();
                var html = "",
                    insiteHTML = '',
                    bg = $('.img').css('background-image');

                bg = bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                if (bg.indexOf('images/noimage.png') === -1 && bg.length > 5) {
                    html += '<img class="' + alignImg + ' ' + imgSize + '" src="' + bg + '" data-img-curated-from="' + sourceVal + '">'
                }
                if (tinyMCE.get('expresscurate_content_editor').getContent().length > 0) {
                    html += '<blockquote cite = "' + sourceVal + '">' + tinyMCE.get('expresscurate_content_editor').getContent() + '<br />';
                }
                html += insiteHTML;
                if (html.length > 0) {
                    if (sourceVal.length > 0) {
                        var domain = sourceVal;
                        if (domain.indexOf('http://') === -1 && domain.indexOf('https://') === -1) {
                            domain = 'http://' + domain;
                        }
                        var title = $("#curated_title").val();
                        domain = domain.match(/^(http|https)/) ? domain : 'http://' + domain;
                        if (domain) {
                            html += '<cite><p class="expresscurate_source">' + $("#expresscurate_from").val() + ' <cite><a class="expresscurated" rel="nofollow" data-curated-url="' + domain + '"  href = "' + domain + '">' + title + '</a></p></cite><br/>';
                        }
                    }
                    html += '</blockquote><br />';
                    var $title = $('#titlewrap').find('#title');
                    if ($title.val().length === 0) {
                        $title.trigger('focus');
                        $title.val($("#curated_title").val());
                    }
                    sendWPEditor(html, insertedTagsTextarea);
                    $dialog.dialog('close');
                } else {
                    return false;
                }
                ExpressCurateUtils.track('/post/content-dialog/curateintopost', true);
            });
        }

        $('#expresscurate_submit').click(function () {
            $("#expresscurate_loading").show();
            submitExpresscurateForm();
        });
        $('#expresscurate_source').keypress(function (e) {
            if (e.keyCode === 13 || e.keyCode === 40) {
                $("#expresscurate_loading").show();
                submitExpresscurateForm();
                return false;
            }
        });

        html.on('click', '.expresscurate_dialog_search .icon', function () {
            var $search = $('.expresscurate_dialog_search'),
                $input = $search.find('input'),
                $close = $search.find('.close'),
                $icon = $search.find('.icon');
            if ($input.hasClass('expresscurate_displayNone')) {
                $input.add($close).removeClass('expresscurate_displayNone');
                $icon.addClass('expresscurate_displayNone');
                $('.expresscurate_dialog_search').addClass('active');
                $input.focus();
            } else {
                searchInParagraphs(input.val());
            }
        });
        html.on('keyup', '.expresscurate_dialog_search input', function (e) {
            if (e.keyCode === 13) {
                searchInParagraphs($(this).val());
            }
        });

        html.on('click', '.expresscurate_dialog_search .close', function () {
            closeSearch();
        });

        html.on('click', '.expresscurate_dialog_shortPar .shortPButton', function () {
            var $elem = $(this),
                $searchInput = $('.expresscurate_dialog_search input');
            if (shortestParagraphLength === 150) {
                shortestParagraphLength = 0;
                $elem.addClass('shortPButtonActive').removeClass('shortPButtonInactive');
            } else {
                $elem.addClass('shortPButtonInactive').removeClass('shortPButtonActive');
                shortestParagraphLength = 150;
            }
            if (!$searchInput.hasClass('expresscurate_displayNone')) {
                searchInParagraphs($searchInput.val());
            } else {
                displayCuratedParagraphs($curatedParagraphs, $curatedParagraphs.length, true);
            }
        });
    }

    function openDialog(source) {
        var $dialog = $("#expresscurate_dialog");
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
                    $('#expresscurate_source').val(source);
                    $('#expresscurate_submit').trigger("click");
                }
            },
            'close': clearExpresscurateForm
        });
        $dialog.dialog('open');

        ExpressCurateUtils.track('/post/content-dialog/startcurate');
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                html = $('html');
                $(document).ready(function () {
                    setupDialog();
                    isSetup = true;
                    setTimeout(function () {
                        if (window.expresscurate_load_url) {
                            openDialog(window.expresscurate_load_url);
                        }
                    }, 0);
                });
                html.on('keyup', '#expresscurate_source', function () {
                    var input = $(this),
                        liHTML = '',
                        list = $('.expresscurate_dialog .autoComplete');
                    if (input.val().length > 1) {
                        $.ajax({
                            type: 'POST',
                            url: 'admin-ajax.php?action=expresscurate_search_feed_bookmark',
                            data: {searchKeyword: input.val()}
                        }).done(function (res) {
                            var data = $.parseJSON(res);
                            $.each(data.slice(0, 5), function (key, value) {
                                liHTML += '<li data-link="' + value.link + '">' + value.title + '</li>';
                            });
                            if (liHTML.length > 0) {
                                input.after('<ul class="autoComplete">' + liHTML + '</ul>');
                            } else {
                                list.remove();
                            }
                        });

                    } else {
                        list.remove();
                    }
                });
                html.on('click', '.expresscurate_dialog .autoComplete li', function () {
                    var li = $(this);
                    $('#expresscurate_source').val(li.data('link'));
                    $('#curated_title').val(li.text());
                    $('.expresscurate_dialog .autoComplete').remove();
                });
            }
        },
        insertText: insertText,
        openDialog: openDialog,
        delCuratedTag: delCuratedTag
    }
})(window.jQuery);

ExpresscurateDialog.setup();