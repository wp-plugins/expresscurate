var SourceCollection = (function (jQuery) {
    var addNew = function () {
        var input = jQuery('.expresscurate_sources_coll_widget .addSource input'),
            link = input.val().trim(),
            post_id = jQuery('#post_ID').val(),
            items_count = jQuery('.expresscurate_sources_coll_widget ul>li').length,
            existedLinks=jQuery('.expresscurate_sources_coll_widget .tooltip a'),
            existed=false;
        existedLinks.each(function(index,val){
            if(jQuery(val).attr("href").replace(/\/\s*$/, "")==link.replace(/\/\s*$/, "")) {
                existed=true;
            }
        });
        if (!existed && link != '') {
            Utils.startLoading(input, jQuery('.addSourceActive div span span'));
            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_add_post_source', {
                url: link,
                post_id: post_id
            }, function (res) {
                data = jQuery.parseJSON(res);
                if (data.status == 'success') {
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
                    jQuery('.expresscurate_sources_coll_widget ul li.addSource').before(li_html);
                    jQuery('.expresscurate_sources_coll_widget .addSource input').val('');
                }else{
                    jQuery('.expresscurate_sources_coll_widget .addSource > div').append('<div class="errorM"><input class="errorInput" type="text">Invalid URL</div>');
                    var error=jQuery('.expresscurate_sources_coll_widget .errorM');
                    error.animate({width:'310px'}, 400);
                    error.find('input').focus();
                }
            }).always(function() {
                Utils.endLoading(input, jQuery('.addSourceActive div span span'));
            });
        }
        input.val('');
    };

    var deleteSource = function (el) {
        var item = jQuery(el).find('textarea').val(),
            post_id = jQuery('#post_ID').val();
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_delete_post_source', {item: item, post_id: post_id}, function (res) {
            data = jQuery.parseJSON(res);
        });
        el.remove();
    };

    var curate = function (el) {
        var permalinkPosition = jQuery('#edit-slug-box').offset().top;
        jQuery(document).scrollTop(permalinkPosition - 90);
        ExpresscurateDialog.openDialog(jQuery(el).find('.tooltip a').attr('href'));
    };

    var removeError = function (){
        var error=jQuery('.expresscurate_sources_coll_widget .errorM');
        error.animate({width: '0px'},
            {
                duration: 400,
                complete: function(){
                    error.remove();
                }
            });
    };

    var setupColl = function () {
        /*hover*/
        jQuery('.expresscurate_sources_coll_widget').on('hover', 'li.list', function (e) {
            var deleteButton = jQuery(this).find('.delete'),
                url = jQuery(this).find('.tooltip a').attr('href'),
                content = '';
            if (jQuery('#content').css("display") == "block") {
                content = jQuery('#content').val();
            } else {
                content = tinyMCE.get("content").getContent();
            }
            var myRegExp = new RegExp('((cite=)|(data-curated-url=))["\']' + url + '["\' ]', 'gmi');
            if (content.match(myRegExp) != null) {
                deleteButton.css('display', 'none');
            } else
                deleteButton.css('display', 'inline-block');
        });
        /*add*/
        jQuery('.expresscurate_sources_coll_widget .addSource .text').on('click', function () {
            var elem = jQuery(this).parent('.addSource');
            if (!elem.hasClass('addSourceActive')) {
                jQuery('.expresscurate_sources_coll_widget .errorM').remove();
                elem.addClass('addSourceActive');
                elem.find('input').focus();
            }
        });
        jQuery('html').on('click', function (e) {
            var elem = jQuery(e.target);
            if (!elem.hasClass('addSource') && !elem.parents().hasClass('addSource') && !elem.hasClass('errorM')) {
                jQuery('.expresscurate_sources_coll_widget .addSource').removeClass('addSourceActive');
            }
        });
        jQuery('.expresscurate_sources_coll_widget').keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
        jQuery('.expresscurate_sources_coll_widget .addSource input').on('keyup', function (e) {
            if (e.keyCode == 13) {
                addNew();
            }
        });
        var clickDisabled = false;
        jQuery('.expresscurate_sources_coll_widget').on('click', '.addSourceActive div span span', function (e) {
            if (clickDisabled)
                return;
            addNew();
            clickDisabled = true;
            setTimeout(function () {
                clickDisabled = false;
            }, 600);
        });
        /*error*/
        jQuery('.expresscurate_sources_coll_widget').on('click','.errorM',function(){
            removeError();
        });
        jQuery('.expresscurate_sources_coll_widget').on('keyup','.errorM input',function(e){
            removeError();
        });
        /*delete*/
        jQuery('.expresscurate_sources_coll_widget').on('click touchend', 'li .delete', function () {
            var elem=jQuery(this).parents('.expresscurate_sources_coll_widget ul>li');
            deleteSource(elem);
        });
        /*curate*/
        jQuery('.expresscurate_sources_coll_widget').on('click touchend', 'li .expresscurate_curate', function () {
            curate(jQuery(this).parents('li.list'));
        });

    };
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
        addNew:addNew
    }
})(window.jQuery);

SourceCollection.setup();
