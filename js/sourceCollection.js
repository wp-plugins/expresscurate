var SourceCollection = (function (jQuery) {
    var $widget;

    function addNew() {
        var $elemToRotate = jQuery('.addSourceActive div span span'),
            $input = $widget.find('.addSource input'),
            link = $input.val().trim(),
            post_id = jQuery('#post_ID').val(),
            items_count = $widget.find('ul>li').length,
            $existedLinks = $widget.find('.tooltip a'),
            existed = false;
        $existedLinks.each(function (index, val) {
            if (jQuery(val).attr("href").replace(/\/\s*$/, "") === link.replace(/\/\s*$/, "")) {
                existed = true;
            }
        });
        if (!existed && link !== '') {
            Utils.startLoading($input, $elemToRotate);
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_add_post_source',
                data: {
                    url: link,
                    post_id: post_id
                }
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    var li_html = '<li class="list">\
                  <textarea name="expresscurate_sources[' + (items_count + 1) + ']" class="expresscurate_displayNone"> ' + JSON.stringify(data.result) + '</textarea>\
                            <span class="title"><span>' + data.result.title + '</span></span>\
                        <div class="hover">\
                            <a class="curate expresscurate_curate">Curate</a><a class="delete">Delete</a>\
                            <span class="tooltip">\
                                <p>Collected from</p>\
                                <a href="' + data.result.link + '" target="_newtab">' + data.result.domain + '</a>\
                            </span>\
                        </div>\
                    </li>';
                    $widget.find('ul li.addSource').before(li_html);
                    $input.val('');
                } else {
                    $widget.find('.addSource > div').append('<div class="errorM"><input class="errorInput" type="text">Invalid URL</div>');
                    $widget.find('.errorM').stop(true, true).animate({width: '310px'}, 400).find('input').focus();
                }
            }).always(function () {
                Utils.endLoading($input, $elemToRotate);
            });
        }
        $input.val('');
    }

    function deleteSource(el) {
        var item = jQuery(el).find('textarea').val(),
            post_id = jQuery('#post_ID').val();
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_delete_post_source',
            data: {item: item, post_id: post_id}
        }).success(function () {
            el.remove();
        });
    }

    function curate(el) {
        var permalinkPosition = jQuery('#edit-slug-box').offset().top;
        jQuery(document).scrollTop(permalinkPosition - 90);
        ExpresscurateDialog.openDialog(jQuery(el).find('.tooltip a').attr('href'));
    }

    function removeError() {
        var $error = $widget.find('.errorM');
        $error.stop(true, true).animate({width: '0px'},
            {
                duration: 400,
                complete: function () {
                    $error.remove();
                }
            });
    }

    function setupColl() {
        var clickDisabled = false;
        $widget = jQuery('.expresscurate_sources_coll_widget');
        /*hover*/
        $widget.on('hover', 'li.list', function () {
            var $this = jQuery(this),
                $deleteButton = $this.find('.delete'),
                url = $this.find('.tooltip a').attr('href'),
                $contentWrap = jQuery('#content'),
                content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
                myRegExp = new RegExp('((cite=)|(data-curated-url=))["\']' + url + '["\' ]', 'gmi');

            if (content.match(myRegExp)) {
                $deleteButton.addClass('expresscurate_displayNone');
            } else {
                $deleteButton.removeClass('expresscurate_displayNone').addClass('expresscurate_displayInlineBlock');
            }
        });
        /*add*/
        jQuery('.expresscurate_sources_coll_widget .addSource .text').on('click', function () {
            var $elem = jQuery(this).parents('.addSource');
            if (!$elem.hasClass('addSourceActive')) {
                $widget.find('.errorM').remove();
                $elem.addClass('addSourceActive').find('input').focus();
            }
        });
        jQuery('html').on('click', function (e) {
            var $elem = jQuery(e.target);
            if (!$elem.hasClass('addSource') && !$elem.parents().hasClass('addSource') && !$elem.hasClass('errorM')) {
                $widget.find('.addSource').removeClass('addSourceActive');
            }
        });
        $widget.keydown(function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                return false;
            }
        });
        jQuery('.expresscurate_sources_coll_widget .addSource input').on('keyup', function (e) {
            if (e.keyCode === 13) {
                addNew();
            }
        });
        $widget.on('click', '.addSourceActive div span span', function () {
            if (clickDisabled) {
                return;
            }
            addNew();
            clickDisabled = true;
            setTimeout(function () {
                clickDisabled = false;
            }, 600);
        });
        /*error*/
        $widget.on('click', '.errorM, .errorM input', function () {
            removeError();
        });
        /*delete*/
        $widget.on('click touchend', 'li .delete', function () {
            var elem = jQuery(this).parents('.expresscurate_sources_coll_widget ul>li');
            deleteSource(elem);
        });
        /*curate*/
        $widget.on('click touchend', 'li .expresscurate_curate', function () {
            curate(jQuery(this).parents('li.list'));
        });

    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupColl();
                    isSetup = true;
                });
            }
        },
        addNew: addNew
    }
})(window.jQuery);

SourceCollection.setup();
