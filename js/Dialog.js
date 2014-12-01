var ExpresscurateDialog = (function(jQuery){
    var keywords;
    var curatedParagraphs = '';
    var shortestParagraphLength = 150;
    var plugin_folder = 'expresscurate';
    var paragraphWidth = 93;

    var sendWPEditor = function (html) {
        var editor = tinyMCE.get('content');
        if (editor) {
            editor.execCommand("mceInsertContent", true, html);
        } else {  
            editor = jQuery('#content');
            if (editor.length == 0) {
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
    };

    var displayCuratedImages = function (images) {
        var img_count = false;
        jQuery.each(images, function (index, value) {
            var img = new Image();
            img.onload = function () {
                var height = this.height,
                    width = this.width;
                if (width > 150 && height > 100) {
                    //images_html += '<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="ExpresscurateDialog.insertDeleteImage(' + index + ')"></li>';
                    jQuery('<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="ExpresscurateDialog.insertDeleteImage(' + index + ')"></li>').appendTo("#curated_images");
                }
            };
            img.src = value;
        });
        setTimeout(function () {
            if (jQuery('ul#curated_images li').length > 0) {
                jQuery('.content .img').removeClass("noimage");
                jQuery('.content .img').css('background-image', jQuery('ul#curated_images li').first().css('background-image'))
                var numberOfImages = jQuery('ul#curated_images li').length;
                if (numberOfImages > 0) {
                    var counter = jQuery('.expresscurate_dialog .imageCount');
                    counter.text('1/' + numberOfImages).removeClass('expresscurate_displayNone');
                }
            } else {
                error_html = '<div class="error">No image found for specified size</div>';
                jQuery('#expresscurate_post_form').before(error_html);
            }
        }, 300);
    };

    var displayCuratedParagraphs = function (paragraphs, count,shortPar) {

        var text_html = '';
        var sorted = [];
        jQuery.each(paragraphs, function(index, value) {
            if (value['value'].length > shortestParagraphLength) {
                sorted[index] = value['value'];
            }
        });
        jQuery.each(sorted, function(index, value) {
            if (typeof value !== 'undefined' && value !== null) {
                text_html += '<li id="tcurated_text_' + index + '" title="' + value + '" class="expresscurate_tag_' + paragraphs[index].tag + '" onclick="ExpresscurateDialog.insertText(\'tcurated_text_' + index + '\', \'p\')">' + value + '</li>';
                if (index < count && !shortPar) {
                    generateTags(value);
                    tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, "<p>" + value + "<p>");
                }
            }
        });
        jQuery('#curated_paragraphs li').remove();
        jQuery(text_html).appendTo('#curated_paragraphs');
        var liCount=jQuery('#curated_paragraphs li').length;
        jQuery('.paragraphs_preview').width(liCount * paragraphWidth);
        jQuery('#curated_paragraphs').css('left','0');
        buttonsStatus();
    };

    var searchInParagraphs = function (search){
        search=search.toLowerCase().replace(/[,'.";:?!]+/g, '').trim().split(' ');
        search = jQuery.grep(search, function( a ) {
            return a !== '';
        });
        var myRegEx =new RegExp('(' + search.join('|') + ')', 'g');

        var searchResult = new Array();

        jQuery.each(curatedParagraphs,function (index,val) {
            if(val.value.toLowerCase().match(myRegEx) && val.value.length>shortestParagraphLength){
                searchResult.push(val);
            }
        });
        jQuery('#curated_paragraphs li').remove();
        displayCuratedParagraphs(searchResult, searchResult.length,true);
    };

    var buttonsStatus = function () {
        var l = parseInt(jQuery('#curated_paragraphs').css('left'));
        var listEnd = jQuery('#curated_paragraphs').width() + l;
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
    };

    var displayCuratedTags = function (keywords) {
        var keywords_html = '';
        jQuery.each(keywords, function (index, value) {
            keywords_html += '<li  id="curated_post_tag_' + index + '"><span>' + value + '</span><a href="#" class="remove" onclick="ExpresscurateDialog.delCuratedTag(' + index + '); return false;">&times;</a></li>';
        });
        keywords_html+='<li class="expresscurate_preventTextSelection markButton" onclick="Keywords.markCuratedKeywords();return false;"><span>mark keywords</span></li>';
        jQuery("#curated_tags").html(keywords_html);
    };

    var generateTags = function (text) {
        var keywords_html = '';
        if (keywords !== null && keywords > 0) {
            jQuery.each(keywords, function (index, value) {
                if (text.indexOf(value) !== -1) {
                    keywords_html += '<li id="curated_post_tag_' + index + '"><a href="#" onclick="ExpresscurateDialog.delCuratedTag(' + index + '); return false;">X</a><span>' + value + '</span></li>';
                    keywords.splice(index, 1);
                }
            });
        }
        jQuery(keywords_html).appendTo("#curated_tags");
    };

    var displaySpecials = function (data) {
        var specials_html = '';
        specials_html += displayCuratedHeadings(data.headings);
        specials_html += displayCuratedDescription(data.metas.description);
        specials_html += displayShortParagraphs();
        specials_html += displayCuratedParagraphsSearch();
        if (specials_html.length === 0) {
            specials_html += '<li>No specal data</li>';
        }
        jQuery(specials_html).appendTo('#expresscurate_special');
    };

    var displayCuratedHeadings = function (headings) {
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
    };

    var displayShortParagraphs = function () {
        var shortParagraphs_html = '<li class="expresscurate_preventTextSelection expresscurate_dialog_shortPar expresscurate_shortParInactiveColor">\
            <label>Short Paragraphs</label>\
            <span class="shortPButton shortPButtonInactive">hide<span></span></span>\
        </li>';
        return shortParagraphs_html;
    };

    var displayCuratedParagraphsSearch = function () {
        var search_html = '<li class="expresscurate_preventTextSelection expresscurate_dialog_search">\
            <input class="disableInputStyle expresscurate_displayNone"/>\
            <span class="close expresscurate_displayNone">&#215</span>\
            <span class="icon"></span>\
        </li>';
        return search_html;
    };

    var displayCuratedDescription = function (description) {
        var description_html = '';
        if (description !== null && description.length > 0) {
            description_html += '<li id="curated_description" onclick="ExpresscurateDialog.insertText(\'curated_description\', \'p\')"; title="' + description + '">Description</li>';
        }
        return description_html;
    };

    var insertText = function (id, tag) {
        var paragraph = '';
        if (tag == 'li') {
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
            paragraph += "<" + tag + ">" + jQuery("#" + id).attr('title').replace(/\r\n/g, "<br />").replace(/\n/g, "<br />");
            +"</" + tag + "> &nbsp;";
        }
        generateTags(paragraph);
        tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, paragraph);
    };

    var delCuratedTag = function (index) {
        jQuery("#curated_post_tag_" + index).fadeOut(7000).remove();
        return false;
    };

    var insertDeleteImage = function (index) {
        if (jQuery("#tcurated_image_" + index).parent().attr('id') == 'curated_images') {
            if (jQuery("#curated_content_selected_img").find("img").length == 0) {
                jQuery("#tcurated_image_" + index).appendTo('#curated_content_selected_img');
                jQuery("#curated_images #tcurated_image_" + index).remove();
            } else {
                jQuery("#" + jQuery('#curated_content_selected_img').find("img").parent().attr('id')).appendTo('#curated_images');
                jQuery("#curated_content_selected_img #" + jQuery('#curated_content_selected_img').find("img").parent().attr('id')).remove();
                jQuery("#tcurated_image_" + index).appendTo('#curated_content_selected_img');
                jQuery("#curated_images #tcurated_image_" + index).remove();
            }
        } else if (jQuery("#tcurated_image_" + index).parent().attr('id') == 'curated_content_selected_img') {
            jQuery("#tcurated_image_" + index).appendTo('#curated_images');
            jQuery("#curated_content_selected_img #tcurated_image_" + index).remove();
        } else {
            //alert(jQuery("#tcurated_image_" + index).parent().attr('id'));
        }
    };

    var clearExpresscurateForm = function () {
        jQuery('#expresscurate_dialog div.error').remove();
        jQuery('#expresscurate_dialog div.updated').remove();
        jQuery("#expresscurate_dialog").find('ul').html('');
        //jQuery("#expresscurate_dialog").find('input[type=text]').val('');
        jQuery("#expresscurate_content_editor").val('');
        jQuery('.content .img').attr('style', '');
        jQuery('.content .img').addClass("noimage");
        jQuery('.controls').hide();
        //jQuery("#expresscurate_slider").html('').html('<ul class="preview left jcarousel-skin-tango" id="expresscurate_paragraphs"></ul>');
        jQuery("#curated_paragraphs").empty();
        if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
            tinyMCE.get('expresscurate_content_editor').setContent('');
        }
        jQuery('#expresscurate_source').focus();
    };

    var closeSearch = function (){
        var input = jQuery('.expresscurate_dialog_search input'),
            close = jQuery('.expresscurate_dialog_search .close'),
            icon=jQuery('.expresscurate_dialog_search .icon');
        input.addClass('expresscurate_displayNone');
        close.addClass('expresscurate_displayNone');
        icon.removeClass('expresscurate_displayNone');
        jQuery('.expresscurate_dialog_search').css('width', '35px');
        input.val('');
        displayCuratedParagraphs(curatedParagraphs, curatedParagraphs.length,true);
    };

    var submitExpresscurateForm = function(){
        var blog_domain = document.domain;
        //remove error divs
        jQuery('#expresscurate_dialog div.error').remove();
        jQuery('#expresscurate_dialog div.updated').remove();
        jQuery("#expresscurate_dialog").fadeIn();
        var error_html = '';
        var notif_html = '';
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article&check=1', jQuery('#expresscurate_post_form input').serialize(), function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status == 'notification') {
                notif_html = '<div class="updated">' + data.msg + '</div>';
                jQuery('#expresscurate_post_form').before(notif_html);
            }
        });
        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article', jQuery('#expresscurate_post_form input').serialize(), function (res) {
            var data = jQuery.parseJSON(res);
            if (data) {
                if (data.status == 'error') {
                    error_html = '<div class="error">' + data.error + '</div>';
                    jQuery('#expresscurate_post_form').before(error_html);
                    jQuery("#expresscurate_loading").fadeOut('fast');
                } else if (data.status == 'success') {
                    clearExpresscurateForm();
                    jQuery(".controls").show();
                    if (data.result.title !== null && data.result.title.length > 0) {
                        jQuery("#curated_title").val(data.result.title);
                    }
                    if (data.result.images.length > 0) {
                        jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_check_images', {img_url: data.result.images[data.result.images.length - 1], img_url2: data.result.images[data.result.images.length - 2]}, function (res) {
                            var data_check = jQuery.parseJSON(res);
                            if (data_check.status === 'success' && data_check.statusCode === 200) {
                                displayCuratedImages(data.result.images);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            } else if (data_check.status === 'fail' && data_check.statusCode === 200) {
                                jQuery('.content .img').css('background-image', jQuery("#expresscurate_loading img").attr('src'));
                                jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_download_images', {images: data.result.images, post_id: jQuery('#post_ID').val()}, function (res) {
                                    var data_images = jQuery.parseJSON(res);
                                    if (data_images.status == 'error') {
                                        error_html = '<div class="error">' + data_images.error + '</div>';
                                        jQuery('#expresscurate_post_form').before(error_html);
                                    } else if (data_images.status == 'success') {
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
                    if (data.result.metas.keywords !== null && data.result.metas.keywords.length > 0) {
                        displayCuratedTags(data.result.metas.keywords);
                    }
                    keywords = data.result.metas.keywords;
                    displaySpecials(data.result);

                    if (data.result.paragraphs.length > 0) {
                        curatedParagraphs=data.result.paragraphs;
                        displayCuratedParagraphs(data.result.paragraphs, jQuery("#expresscurate_autosummary").val(),false);
                    }
                    jQuery('#expresscurate_source').focus();
                }
            } else {
                error_html = '<div class="error">Can\'t curate from this page</div>';
                jQuery('#expresscurate_post_form').before(error_html);
                jQuery("#expresscurate_loading").fadeOut('fast');
            }

        });
    };

    var setupDialog = function(){
        buttonsStatus();
        jQuery('.nextSlide').click(function (e) {
            if (jQuery(this).hasClass('inactiveButton')) {
                return;
            } else {
                var l = Math.floor((parseInt(jQuery('#curated_paragraphs').css('left')) - 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
                if (jQuery('#curated_paragraphs').width() + l <= jQuery('.slider').width()) {
                    l = jQuery('.slider').width() - jQuery('#curated_paragraphs').width();
                    jQuery(this).addClass('inactiveButton');
                }
                jQuery('#curated_paragraphs').stop().animate({
                    'left': l + 'px'
                }, {
                    duration: 300,
                    always: function () {
                        buttonsStatus();
                    }
                });
            }
        });
        jQuery('.prevSlide').click(function (e) {
            if (jQuery(this).hasClass('inactiveButton')) {
                return;
            } else {
                var l = Math.floor((parseInt(jQuery('#curated_paragraphs').css('left')) + 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
                if (l >= 0) {
                    l = 0;
                    jQuery(this).addClass('inactiveButton');
                }
                jQuery('#curated_paragraphs').stop().animate({
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
            if (jQuery("#expresscurate_dialog").length) {
                var $dialog = jQuery("#expresscurate_dialog");
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
                var $dialog = jQuery("#expresscurate_dialog_theme");
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

            jQuery('.prevImg, .nextImg, .expresscurate_dialog .img').click(function (e) {
                numberOfImages = jQuery('ul#curated_images li').length;
                if (jQuery(this).hasClass('next') || jQuery(this).hasClass('img')) {
                    currentImage = (++currentImage > numberOfImages-1) ? 0 : currentImage;
                } else if (jQuery(this).hasClass('prev')) {
                    currentImage = (--currentImage <0) ? numberOfImages-1 : currentImage;
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
            jQuery('.alignL').click(function (e) {
                alignImg = 'alignleft';
            });
            jQuery('.alignNone').click(function (e) {
                alignImg = 'alignnone';
            });
            jQuery('.alignR').click(function (e) {
                alignImg = 'alignright';
            });

            var imgSize = 'sizeX';
            jQuery('.sizeX').click(function (e) {
                imgSize = 'sizeX';
            });
            jQuery('.sizeM').click(function (e) {
                imgSize = 'sizeM';
            });
            jQuery('.sizeS').click(function (e) {
                imgSize = 'sizeS';
            });

            jQuery('.sizeS, .sizeM, .sizeX').each(function (i, el) {
                jQuery(this).click(function (e) {
                    jQuery('.sizeS, .sizeM, .sizeX').css({
                        'background-color': '#777777'
                    });
                    jQuery(this).css({
                        'background-color': '#27cfae'
                    });
                });
            });

            jQuery('.alignL').click(function (e) {
                jQuery(this).css('background-position', 'right bottom');
                jQuery('.alignNone').css('background-position', 'center top');
                jQuery('.alignR').css('background-position', 'left top');
            });
            jQuery('.alignR').click(function (e) {
                jQuery('.alignL').css('background-position', 'right top');
                jQuery('.alignNone').css('background-position', 'center top');
                jQuery(this).css('background-position', 'left bottom');
            });
            jQuery('.alignNone').click(function (e) {
                jQuery('.alignL').css('background-position', 'right top');
                jQuery(this).css('background-position', 'center bottom');
                jQuery('.alignR').css('background-position', 'left top');
            });

            jQuery("#expresscurate_open-modal").click(function (event) {
                event.preventDefault();
                var editor = tinyMCE.get('content');
                if (!editor) {
                    editor = jQuery('#content');
                    if (editor.length == 0) {
                        if (tinyMCE.editors.length > 0) {
                            editor = tinyMCE.editors[0];
                        }
                    }
                }

                var body = tinyMCE.activeEditor.dom.select('body.mce-content-body');
                jQuery(body).append('<span class="cursourHolder"> </span>');
                var sp = tinyMCE.activeEditor.dom.select('span.cursourHolder');
                tinyMCE.activeEditor.selection.select(sp[sp.length - 1]);

                if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
                    if (!tinyMCE.execCommand("mceAddControl", true, "expresscurate_content_editor")) {
                        tinyMCE.execCommand("mceAddEditor", true, "expresscurate_content_editor");
                    }
                }
                $dialog.dialog('open');
            });
            jQuery("#expresscurate_insert").click(function () {
                var ed=tinyMCE.activeEditor;
                var highlightedElems=jQuery(ed.getBody()).find('span.expresscurate_keywordsHighlight');
                if(highlightedElems.length>0){
                    highlightedElems.each(function(index,val){
                        jQuery(val).replaceWith(this.childNodes);
                    });
                }
                var tags_html = '';
                var inserted_tags = jQuery("#post_tag .tagchecklist span").length;
                var inserted_tags_textarea = "";
                inserted_tags_textarea = jQuery("#tax-input-post_tag").val();
                jQuery('#curated_tags li').each(function (i) {
                    inserted_tags_textarea += "," + jQuery(this).find('span').text();
                });
                jQuery("#tax-input-post_tag").val(inserted_tags_textarea);
                jQuery(".tagadd").trigger('click');
                var html = "";
                var insite_html = '';
                var bg = jQuery('.img').css('background-image');

                bg = bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                if (bg.indexOf('images/noimage.png') === -1 && bg.length > 5) {

                    ///html += jQuery("#curated_content_selected_img li").html();
                    html += '<img class="' + alignImg + ' ' + imgSize + '" src="' + bg + '" data-img-curated-from="' + jQuery("#expresscurate_source").val() + '">';
                }
                if (tinyMCE.get('expresscurate_content_editor').getContent().length > 0) {
                    html += "<blockquote>" + tinyMCE.get('expresscurate_content_editor').getContent() + "</blockquote><br />";
                }
                html += insite_html;
                if (html.length > 0) {
                    if (jQuery("#expresscurate_source").val().length > 0) {
                        //var matches = jQuery("#expresscurate_source").val().match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
                        //var domain = matches && matches[1];
                        var domain = jQuery("#expresscurate_source").val();
                            domain =  domain.match(/^(http|https)/) ? domain : 'http://'+domain;
                        if (domain) {
                            html += '<div class="expresscurate_source"><p>' + jQuery("#expresscurate_from").val() + ' <a class="expresscurated" data-curated-url="' + jQuery("#expresscurate_source").val() + '"  href = "' + jQuery("#expresscurate_source").val() + '">' + domain + '</a></p></div><br/>';
                        }
                    }
                    if (jQuery("#titlewrap #title").val().length == 0) {
                        jQuery("#titlewrap #title").trigger('focus');
                        jQuery("#titlewrap #title").val(jQuery("#curated_title").val());
                    }
                    sendWPEditor(html);
                    $dialog.dialog('close');
                } else {
                    return false;
                }
                //tinyMCE.activeEditor.execCommand('annotation', undefined, true);
            });
        }

        jQuery('#expresscurate_submit').click(function () {
            jQuery("#expresscurate_loading").show();
            submitExpresscurateForm();
            //jQuery(document).ajaxComplete(function () {
            //
            //});
        });
        jQuery('#expresscurate_source').keypress(function (e) {
            if (e.keyCode == 13 || e.keyCode == 40) {
                submitExpresscurateForm();
                return false;
            }
        });

        jQuery('html').on('click', '.expresscurate_dialog_search .icon', function () {
            var input = jQuery('.expresscurate_dialog_search input'),
                close = jQuery('.expresscurate_dialog_search .close'),
                icon=jQuery('.expresscurate_dialog_search .icon');
            if (input.hasClass('expresscurate_displayNone')) {
                input.removeClass('expresscurate_displayNone');
                close.removeClass('expresscurate_displayNone');
                icon.addClass('expresscurate_displayNone');
                jQuery('.expresscurate_dialog_search').css('width', '190px');
                input.focus();
            } else {
                searchInParagraphs(input.val());
            }
        });
        jQuery('html').on('keyup', '.expresscurate_dialog_search input', function (e) {
            if(e.keyCode==13){
                searchInParagraphs(jQuery(this).val());
            }
        });

        jQuery('html').on('click', '.expresscurate_dialog_search .close', function () {
            closeSearch();
        });

        jQuery('html').on('click', '.expresscurate_dialog_shortPar .shortPButton', function () {
            var elem = jQuery(this);
            if (shortestParagraphLength == 150) {
                shortestParagraphLength = 0;
                elem.addClass('shortPButtonActive').removeClass('shortPButtonInactive').html('show<span></span>');
            } else {
                elem.addClass('shortPButtonInactive').removeClass('shortPButtonActive').html('hide<span></span>');
                shortestParagraphLength = 150;
            }
            var searchInput=jQuery('.expresscurate_dialog_search input');
            if(!searchInput.hasClass('expresscurate_displayNone')){
                searchInParagraphs(searchInput.val());
            }else{
                displayCuratedParagraphs(curatedParagraphs, curatedParagraphs.length,true);
            }
        });
    };

    var isSetup = false;

    return {
        setup: function(){
                if(!isSetup){
                    jQuery(document).ready(function(){
                        setupDialog();
                        isSetup = true;
                    });
                }
        },

        insertText: insertText
    }
})(window.jQuery);

ExpresscurateDialog.setup();