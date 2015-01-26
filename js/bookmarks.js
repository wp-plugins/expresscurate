var Bookmarks = (function (jQuery) {
    var $input, $elemToRotate, $bookmarkBoxes, $notDefMessage, $controls;

    function addBookmark() {
        //validate url
        var myRegExp = new RegExp(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/),
            link = $input.val(),
            li_html = '',
            message = '',
            $existedLi = $bookmarkBoxes.find('li'),
            existed = false,
            existedURL,
            $addedLi;
        jQuery('.errorMessage').remove();
        Utils.startLoading($input, $elemToRotate);

        if (link.match(myRegExp)) {
            if ($existedLi.length > 0) {
                $existedLi.each(function (index, value) {
                    existedURL = jQuery(value).find('.postTitle').attr('href');
                    if (link === existedURL) {
                        existed = true;
                        li_html = '';
                        $addedLi = jQuery(value);
                        $addedLi.addClass('expresscurate_transparent');
                        setTimeout(function () {
                            $addedLi.removeClass('expresscurate_transparent');
                        }, 700);
                    }
                });
            }
            if (!existed) {
                jQuery.ajax({
                    type: 'POST',
                    url: 'admin-ajax.php?action=expresscurate_bookmark_set',
                    data: {url: link}
                }).done(function (res) {
                        var data = jQuery.parseJSON(res);
                        if (data.status === 'success') {
                            li_html = '<li class="expresscurate_preventTextSelection expresscurate_masonryItem">\
                    <input id="uniqueId" class="checkInput" type="checkbox"/>\
                    <label for="uniqueId" class="expresscurate_preventTextSelection"></label>\
                    <a class="postTitle" href="' + link + '" target="_newtab">' + data.result.title + '</a><br />\
                    <a class="url"  href="' + link + '" target="_newtab">' + data.result.domain + '</a>\
                <span class="curatedBy">/ by <span>' + data.result.user + '</span> /</span>\
                    <span class="time">Just now</span>\
                    <div class="comment">\
                        <label class="" for="uniqueId">add comment</label>\
                        <input type="text" class="expresscurate_disableInputStyle expresscurate_displayNone" id="uniqueId">\
                        <span class="expresscurate_displayNone">&#215</span>\
                    </div>\
                    <ul class="controls expresscurate_preventTextSelection">\
                        <li><a class="curate" href="post-new.php?expresscurate_load_source=' + link + '">Curate</a></li>\
                        <li class="separator" >-</li>\
                        <li class="copyURL">Copy URL</li>\
                        <li class="separator">-</li>\
                        <li class="hide">Delete</li>\
                    </ul>\
                <div class="expresscurate_clear"></div>\
                    <span class="label label_' + data.result.type + '">' + data.result.type + '</span>\
                </li>';
                            if (li_html !== '') {
                                $bookmarkBoxes.append(li_html);
                                var $lastLi = $bookmarkBoxes.find('> li').last();

                                $bookmarkBoxes.find('.addNewBookmark').after($lastLi);
                                $bookmarkBoxes.masonry('destroy').masonry({
                                    itemSelector: '.expresscurate_masonryItem',
                                    isResizable: true,
                                    isAnimated: true,
                                    columnWidth: '.expresscurate_masonryItem',
                                    gutter: 10
                                });

                                Utils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
                                $lastLi.addClass('expresscurate_transparent');
                                $input.val('');
                                setTimeout(function () {
                                    $lastLi.removeClass('expresscurate_transparent');
                                }, 700);
                            }

                        } else if (data.status === 'error') {
                            message = data.msg;
                            jQuery(".addBookmark").after('<span class="errorMessage">' + message + '</span>');
                            Utils.endLoading($input, $elemToRotate);
                        }
                    }
                ).always(function () {
                        Utils.endLoading($input, $elemToRotate);
                    });
            } else {
                message = 'This page is already bookmarked.';
            }
        }
        else {
            message = 'Invalid URL';
        }
        if (message !== '') {
            jQuery(".addBookmark").after('<span class="errorMessage">' + message + '</span>');
            Utils.endLoading($input, $elemToRotate);
        }
    }

    function bookmarkDelete(els) {
        var items = [],
            item = {};
        jQuery.each(els, function (index, el) {
            item['link'] = jQuery(el).find('.url').attr('href');
            items.push(item);
        });
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_bookmarks_delete',
            data: {items: JSON.stringify(items)}
        }).done(function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'success') {
                jQuery(els).addClass('expresscurate_transparent');
                setTimeout(function () {
                    jQuery(els).remove();
                    $bookmarkBoxes.masonry();
                    Utils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
                    Utils.checkControls($controls);
                }, 700);
            }
        });
    }

    function addComment(elem) {
        var $commentWrap = elem.parent('.comment'),
            $label = $commentWrap.find('label'),
            $close = $commentWrap.find('span'),
            $input = elem,
            link = elem.parents('.expresscurate_bookmarkBoxes > li').find('a.url').attr('href'),
            comment = $input.val().trim();
        $label.removeClass('expresscurate_displayNone');
        $input.add($close).addClass('expresscurate_displayNone');

        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_bookmark_set',
            data: {
                url: link,
                comment: comment
            }
        }).done(function (res) {
            if (!comment.match(/\S/)) {
                $label.text('add comment').removeClass('active');
            } else {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $label.text($input.val()).addClass('active');
                    $bookmarkBoxes.masonry();
                }
            }
        });
    }

    function setupBookmarks() {
        $input = jQuery('.addBookmark input');
        $elemToRotate = jQuery('.addBookmark span span');
        $bookmarkBoxes = jQuery('.expresscurate_bookmarkBoxes');
        $notDefMessage = jQuery('.expresscurate_bookmarks .expresscurate_notDefined');
        $controls = jQuery('.bookmarkListControls li');

        if (jQuery('.expresscurate_bookmarks').length) {
            Utils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
        }
        /*copy URL*/
        $bookmarkBoxes.on('click', 'li .copyURL', function () {
            var text = jQuery(this).parents('.expresscurate_bookmarkBoxes > li').find('.url').attr('href');
            window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
        });
        /*comment*/
        $bookmarkBoxes.on('click', '.comment label', function () {
            var $label = jQuery(this),
                $commentWrap = $label.parent('.comment'),
                $input = $commentWrap.find('input'),
                $close = $commentWrap.find('span');
            $label.addClass('expresscurate_displayNone');
            $input.add($close).removeClass('expresscurate_displayNone').addClass('expresscurate_displayInlineBlock');
            $bookmarkBoxes.masonry();
            if ($label.text() !== 'add comment') {
                $input.val($label.text());
            } else {
                $input.val('');
            }
        });
        $bookmarkBoxes.on('keyup', '.comment input', function (e) {
            if (e.keyCode === 13) {
                addComment(jQuery(this));
            }
        });
        $bookmarkBoxes.on('blur', '.comment input', function () {
            addComment(jQuery(this));
        });
        $bookmarkBoxes.on('click touchend', '.comment span', function () {
            var $close = jQuery(this),
                $commentWrap = $close.parent('.comment'),
                $label = $commentWrap.find('label'),
                $input = $commentWrap.find('input');
            $label.removeClass('expresscurate_displayNone');
            $input.add($close).addClass('expresscurate_displayNone');
            $label.text('add comment').removeClass('active');
            $bookmarkBoxes.masonry();
        });
        /*checkboxes*/
        $bookmarkBoxes.find('li input:checkbox').prop('checked', false);
        $bookmarkBoxes.on('change', '.checkInput', function () {
            Utils.checkControls($controls);
        });
        $bookmarkBoxes.on('click', '> li', function (e) {
            if (e.target !== this) {
                return;
            }
            var checkbox = jQuery(this).find('.checkInput');
            if (checkbox.is(':checked')) {
                checkbox.attr('checked', false);
            }
            else {
                checkbox.attr('checked', true);
            }
            Utils.checkControls($controls);
        });
        jQuery('.expresscurate_bookmarks .check').on('click', function () {
            var checked = $bookmarkBoxes.find('li input:checkbox:checked').length,
                liCount = $bookmarkBoxes.find(' > li').length,
                $checkboxes = $bookmarkBoxes.find('li input:checkbox');
            if (checked === liCount) {
                $checkboxes.prop('checked', false);
            } else {
                $checkboxes.prop('checked', true);
            }
            Utils.checkControls($controls);
        });
        /*curate*/
        jQuery('.expresscurate_bookmarks .quotes').on('click', function () {
            var $checked = jQuery(".expresscurate_bookmarkBoxes li input:checkbox:checked");
            if (checked.length === 1) {
                var $elem = jQuery($checked[0]).parent().find('a'),
                    title = $elem.html(),
                    url = $elem.attr('href');
                window.location.href = '/wp-admin/post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
                Utils.addSources($checked.parents('.expresscurate_bookmarkBoxes > li'), '.expresscurate_bookmarkData');
                return false;
            }
        });
        /*delete*/
        jQuery('.expresscurate_bookmarks .remove').on('click', function () {
            var $checked = $bookmarkBoxes.find('li input:checkbox:checked');
            bookmarkDelete($checked.parents('.expresscurate_bookmarkBoxes > li'));
        });
        $bookmarkBoxes.on('click', '.controls .hide', function () {
            var $elem = jQuery(this).parents('.expresscurate_bookmarkBoxes > li');
            bookmarkDelete($elem);
        });
        /*add*/
        $input.on("keyup", function (e) {
            if (e.keyCode === 13) {
                addBookmark();
            }
        });
        $elemToRotate.on('click', function () {
            addBookmark();
        });
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupBookmarks();
                    isSetup = true;
                });
            }
        }
    }
})
(window.jQuery);

Bookmarks.setup();
