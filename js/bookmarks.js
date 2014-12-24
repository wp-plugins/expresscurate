var Bookmarks = (function (jQuery) {
    var addBookmark = function () {
        jQuery('.errorMessage').remove();
        Utils.startLoading(jQuery('.addBookmark input'), jQuery('.addBookmark span span'));
        //validate url
        var myRegExp = new RegExp(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/);

        var link = jQuery('.addBookmark input').val(),
            li_html = '',
            message = '';
        if (link.match(myRegExp)) {
            var existedLi = jQuery('.expresscurate_bookmarkBoxes li'),
                existed = false;
            if (existedLi.length > 0) {
                existedLi.each(function (index, value) {
                    var existedURL = jQuery(value).find('.postTitle').attr('href');
                    if (link == existedURL) {
                        existed = true;
                        li_html = '';
                        var addedLi = jQuery(value);
                        addedLi.css('background-color', 'transparent');
                        setTimeout(function () {
                            addedLi.css('background-color', '#fff');
                        }, 700);
                        return;
                    }
                });
            }
            if (!existed) {
                jQuery.post(
                    'admin-ajax.php?action=expresscurate_bookmark_set', {url: link}, function (res) {

                        data = jQuery.parseJSON(res);
                        if (data.status == 'success') {
                            li_html = '<li>\
                    <input id="uniqueId" class="checkInput" type="checkbox"/>\
                    <label for="uniqueId" class="expresscurate_preventTextSelection"></label>\
                    <a class="postTitle" href="' + link + '" target="_newtab">' + data.result.title + '</a><br />\
                    <a class="url"  href="' + link + '" target="_newtab">' + data.result.domain + '</a>\
                <span class="curatedBy">/ by <span>' + data.result.user + '</span> /</span>\
                    <span class="time">Just now</span>\
                    <div class="comment">\
                        <label class="" for="uniqueId">add comment</label>\
                        <input type="text" class="expresscurate_disableInputStyle" id="uniqueId">\
                        <span>&#215</span>\
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
                            if (li_html != '') {
                                jQuery('.expresscurate_bookmarkBoxes').append(li_html);
                                Utils.notDefinedMessage(jQuery('.expresscurate_bookmarks .expresscurate_notDefined'), jQuery('.expresscurate_bookmarkBoxes > li'));
                                var lastLi = jQuery('.expresscurate_bookmarkBoxes > li').last();
                                lastLi.css('background-color', 'transparent');
                                jQuery('.addBookmark input').val('');
                                setTimeout(function () {
                                    lastLi.css('background-color', '#fff');
                                }, 700);
                            }

                        } else if (data.status == 'error') {
                            message = data.msg;
                            jQuery(".addBookmark").after('<span class="errorMessage">' + message + '</span>');
                            Utils.endLoading(jQuery('.addBookmark input'), jQuery('.addBookmark span span'));
                        }
                    }).always(function () {
                        Utils.endLoading(jQuery('.addBookmark input'), jQuery('.addBookmark span span'));
                    });
            } else {
                message = 'This page is already bookmarked.';
            }
        } else {
            message = 'Invalid URL';
        }
        if (message != '') {
            jQuery(".addBookmark").after('<span class="errorMessage">' + message + '</span>');
            Utils.endLoading(jQuery('.addBookmark input'), jQuery('.addBookmark span span'));
        }

    };

    var bookmarkDelete = function (els) {
        var items = [];
        jQuery.each(els, function (index, el) {
            var item = {};
            item['link'] = jQuery(el).find('.url').attr('href');
            items.push(item);
        });
        jQuery.post('admin-ajax.php?action=expresscurate_bookmarks_delete', {items: JSON.stringify(items)}, function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status == 'success') {
                jQuery(els).css('background-color', 'transparent');
                setTimeout(function(){
                    jQuery(els).remove();
                },700);
                Utils.notDefinedMessage(jQuery('.expresscurate_bookmarks .expresscurate_notDefined'), jQuery('.expresscurate_bookmarkBoxes > li'));
                Utils.checkControls(jQuery('.bookmarkListControls .quotes,.bookmarkListControls .remove'));
            } else {
            }
        });
    };

    var addComment = function (elem) {
        var label = elem.parent('.comment').find('label'),
            close = elem.parent('.comment').find('span'),
            input = elem,
            link = elem.parents('.expresscurate_bookmarkBoxes > li').find('a.url').attr('href'),
            comment = input.val();
        label.css('display', 'block');
        input.add(close).css('display', 'none');
        if (!input.val().match(/\S/)) {
            label.text('add comment').removeClass('active');
        } else {
            jQuery.post('admin-ajax.php?action=expresscurate_bookmark_set', {
                url: link,
                comment: comment
            }, function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status == 'success') {
                    label.text(input.val()).addClass('active');
                }
            });

        }
    };
    var setupBookmarks = function () {
        if(jQuery('.expresscurate_bookmarks').length){
            Utils.notDefinedMessage(jQuery('.expresscurate_bookmarks .expresscurate_notDefined'), jQuery('.expresscurate_bookmarkBoxes > li'));
        }
        /*copy URL*/
        jQuery('.expresscurate_bookmarks .expresscurate_bookmarkBoxes').on('click', 'li .copyURL', function () {
            var text = jQuery(this).parents('.expresscurate_bookmarkBoxes > li').find('.url').attr('href');
            window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
        });
        /*comment*/
        jQuery('.expresscurate_bookmarkBoxes').on('click', '.comment label', function () {
            var input = jQuery(this).parent('.comment').find('input'),
                close = jQuery(this).parent('.comment').find('span'),
                label = jQuery(this);
            label.css('display', 'none');
            input.add(close).css('display', 'inline-block');
            if (label.text() !== 'add comment')
                input.val(label.text());
            else
                input.val('');
        });
        jQuery('.expresscurate_bookmarkBoxes').on('keyup', '.comment input', function (e) {
            if (e.keyCode == 13) {
                addComment(jQuery(this));
            }
        });
        jQuery('.expresscurate_bookmarkBoxes').on('blur', '.comment input', function (e) {
            addComment(jQuery(this));
        });
        jQuery('.expresscurate_bookmarkBoxes').on('click touchend', '.comment span', function () {
            var label = jQuery(this).parent('.comment').find('label'),
                close = jQuery(this),
                input = jQuery(this).parent('.comment').find('input');
            label.css('display', 'block');
            input.add(close).css('display', 'none');
            label.text('add comment').removeClass('active');
        });
        /*checkboxes*/
        jQuery('.expresscurate_bookmarkBoxes li input:checkbox').prop('checked', false);
        jQuery(".expresscurate_bookmarkBoxes").on('change', '.checkInput', function () {
            Utils.checkControls(jQuery('.bookmarkListControls .quotes,.bookmarkListControls .remove'));
        });
        jQuery('.expresscurate_bookmarkBoxes').on('click', '> li', function (e) {
            if (e.target !== this)
                return;
            var checkbox = jQuery(this).find('.checkInput');
            if (checkbox.is(':checked')) {
                checkbox.attr('checked', false);
            }
            else {
                checkbox.attr('checked', true);
            }
            Utils.checkControls(jQuery('.bookmarkListControls .quotes,.bookmarkListControls .remove'));
        });
        jQuery('.expresscurate_bookmarks .check').on('click', function () {
            var checked = jQuery(".expresscurate_bookmarkBoxes li input:checkbox:checked").length,
                liCount = jQuery(".expresscurate_bookmarkBoxes > li").length;
            if (checked === liCount) {
                jQuery('.expresscurate_bookmarkBoxes li input:checkbox').prop('checked', false);
            } else {
                jQuery('.expresscurate_bookmarkBoxes li input:checkbox').prop('checked', true);
            }
            Utils.checkControls(jQuery('.bookmarkListControls .quotes,.bookmarkListControls .remove'));
        });
        /*curate*/
        jQuery('.expresscurate_bookmarks .quotes').on('click', function () {
            var checked = jQuery(".expresscurate_bookmarkBoxes li input:checkbox:checked");
            if (checked.length == 1) {
                var title = jQuery(checked[0]).parent().find('a').html();
                var url = jQuery(checked[0]).parent().find('a').attr('href');
                window.location.href = '/wp-admin/post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
                Utils.addSources(checked.parents('.expresscurate_bookmarkBoxes > li'), '.expresscurate_bookmarkData');
                return false;
            }
        });
        /*delete*/
        jQuery('.expresscurate_bookmarks .remove').on('click', function () {
            var checked = jQuery(".expresscurate_bookmarkBoxes li input:checkbox:checked");
            bookmarkDelete(checked.parents('.expresscurate_bookmarkBoxes > li'));
        });
        jQuery('.expresscurate_bookmarkBoxes').on('click', '.controls .hide', function () {
            var elem = jQuery(this).parents('.expresscurate_bookmarkBoxes > li');
            bookmarkDelete(elem);
        });
        /*add*/
        jQuery('.addBookmark input').on("keyup", function (e) {
            if (e.keyCode == 13) {
                addBookmark();
            }
        });
        jQuery('.addBookmark span span').on('click', function () {
            addBookmark();
        });
    };

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
})(window.jQuery);

Bookmarks.setup();
