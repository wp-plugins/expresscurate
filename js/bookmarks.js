var ExpressCurateBookmarks = (function ($) {
    var $input, $elemToRotate, $bookmarkBoxes, $notDefMessage, $controls, controlsTop;

    function addBookmark() {
        //validate url
        var myRegExp = new RegExp(/(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi),
            link = $input.val(),
            liHTML = '',
            message = '',
            $errorMessage = $(".addNewBookmark .expresscurate_errorMessage");

        //$errorMessage.text('');

        if (!link.match(myRegExp)) {
            message = 'Invalid URL';
            ExpressCurateUtils.validationMessages(message,$errorMessage,$input);
        } else {
            ExpressCurateUtils.startLoading($input, $elemToRotate);
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_bookmark_set',
                data: {url: link}
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    /*if (data.result === null) {
                        liHTML = '';
                        message = 'Article does not exists.';
                    } else {*/
                        $.extend(data.result, {
                            'id': $bookmarkBoxes.find('> li').length
                        });
                        liHTML = ExpressCurateUtils.getTemplate('bookmarksItem', data.result);

                        $bookmarkBoxes.append(liHTML);
                        var $lastLi = $bookmarkBoxes.find('> li').last();

                        $bookmarkBoxes.find('.addNewBookmark').after($lastLi);
                        $bookmarkBoxes.masonry('destroy').masonry({
                            itemSelector: '.expresscurate_masonryItem',
                            isResizable: true,
                            isAnimated: true,
                            columnWidth: '.expresscurate_masonryItem',
                            gutter: 10
                        });

                        ExpressCurateUtils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
                        $lastLi.addClass('expresscurate_transparent');
                        $input.val('');
                        setTimeout(function () {
                            $lastLi.removeClass('expresscurate_transparent');
                        }, 700);
                    //}
                } else if (data.status === 'error' && data.msg !== null) {
                    message = data.msg;
                }
                if (message !== '') {
                    ExpressCurateUtils.validationMessages(message,$errorMessage,$input);
                }
                ExpressCurateUtils.endLoading($input, $elemToRotate);
            });
        }

        ExpressCurateUtils.track('/bookmarks/add');
    }

    function bookmarkDelete(els) {
        var items = [],
            item = {};
        $.each(els, function (index, el) {
            item['link'] = $(el).find('.url').attr('href');
            items.push(item);
            item = {};
        });

        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_bookmarks_delete',
            data: {items: JSON.stringify(items)}
        }).done(function (res) {
            var data = $.parseJSON(res);
            if (data.status === 'success') {
                $(els).addClass('expresscurate_transparent');
                setTimeout(function () {
                    $(els).remove();
                    $bookmarkBoxes.masonry();
                    ExpressCurateUtils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
                    ExpressCurateUtils.checkControls($controls);
                }, 700);
            }
        });

        ExpressCurateUtils.track('/bookmarks/delete');
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

        $.ajax({
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
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $label.text($input.val()).addClass('active');
                    $bookmarkBoxes.masonry();
                }
            }
        });

        ExpressCurateUtils.track('/bookmarks/comment');
    }

    function fixedMenu() {
        var top = $(document).scrollTop(),
            $controlsWrap = $('.controlsWrap'),
            $masonryWrap = $('.expresscurate_masonryWrap');
        if ($controlsWrap.length) {
            if (top > controlsTop && !$controlsWrap.hasClass('fixedControls') && $controlsWrap.offset().top > 0) {
                controlsTop = $controlsWrap.offset().top - 30;
                $controlsWrap.addClass('fixedControls');
                $masonryWrap.addClass('expresscurate_marginTop50');
            } else if ($controlsWrap.hasClass('fixedControls') && top <= controlsTop) {
                $controlsWrap.removeClass('fixedControls');
                $masonryWrap.removeClass('expresscurate_marginTop50');
            }
            $('.expresscurate_controls').width($masonryWrap.width());
        }
    }

    function setupBookmarks() {
        $input = $('.addBookmark input');
        $elemToRotate = $('.addBookmark span span');
        $bookmarkBoxes = $('.expresscurate_bookmarkBoxes');
        $notDefMessage = $('.expresscurate_bookmarks .expresscurate_notDefined');
        $controls = $('.bookmarkListControls li');
        var $controlsWrap = $('.expresscurate_controls');

        if ($('.expresscurate_bookmarks').length) {
            ExpressCurateUtils.notDefinedMessage($notDefMessage, $bookmarkBoxes.find(' > li'));
        }
        /*scroll*/
        if ($controlsWrap.length) {
            $(window).on('load', function () {
                if (!$('#wpadminbar').length) {
                    $controlsWrap.css('top', 0);
                }
                controlsTop = $controlsWrap.offset().top - 30;
                fixedMenu();
            });
            $(window).on('resize', function () {
                fixedMenu();
            });
            $(window).on('scroll', function () {
                fixedMenu();
            });
        }
        /*copy URL*/
        $bookmarkBoxes.on('click', 'li .copyURL', function () {
            var text = $(this).parents('.expresscurate_bookmarkBoxes > li').find('.url').attr('href');
            window.prompt("Copy to clipboard: Ctrl+C, Enter", text);

            ExpressCurateUtils.track('/bookmarks/copy-url');
        });

        /*comment*/
        $bookmarkBoxes.on('click', '.comment label', function () {
            var $label = $(this),
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
                addComment($(this));
            }
        });
        $bookmarkBoxes.on('blur', '.comment input', function () {
            addComment($(this));
        });
        $bookmarkBoxes.on('click touchend', '.comment span', function () {
            var $close = $(this),
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
            ExpressCurateUtils.checkControls($controls);
        });
        $('.expresscurate_bookmarks .check').on('click', function () {
            var checked = $bookmarkBoxes.find('li input:checkbox:checked').length,
                liCount = $bookmarkBoxes.find(' > li').length,
                $checkboxes = $bookmarkBoxes.find('li input:checkbox');
            if (checked === liCount) {
                $checkboxes.prop('checked', false);
            } else {
                $checkboxes.prop('checked', true);
            }
            ExpressCurateUtils.checkControls($controls);
        });

        /*curate*/
        $('.expresscurate_bookmarks .quotes').on('click', function () {
            ExpressCurateUtils.track('/bookmarks/curate');

            var $checked = $(".expresscurate_bookmarkBoxes li input:checkbox:checked");
            if ($checked.length === 1) {
                var $elem = $($checked[0]).parent().find('a'),
                    title = $elem.html(),
                    url = window.btoa(encodeURIComponent($elem.attr('href')));
                window.location.href = $('#adminUrl').val() + 'post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
            } else if ($checked.length > 1) {
                ExpressCurateUtils.addSources($checked.parents('.expresscurate_bookmarkBoxes > li'), '.expresscurate_bookmarkData');
            }
            return false;
        });

        /*delete*/
        $('.expresscurate_bookmarks .remove').on('click', function () {
            var $checked = $bookmarkBoxes.find('li input:checkbox:checked');
            bookmarkDelete($checked.parents('.expresscurate_bookmarkBoxes > li'));
        });
        $bookmarkBoxes.on('click', '.controls .hide', function () {
            var $elem = $(this).parents('.expresscurate_bookmarkBoxes > li');
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
                $(document).ready(function () {
                    setupBookmarks();
                    isSetup = true;
                });
            }
        },
        fixedMenu: fixedMenu
    }
})
(window.jQuery);

ExpressCurateBookmarks.setup();
