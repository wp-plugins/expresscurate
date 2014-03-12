(function() { 
    var getId = function(prefix) {
        var uniqueId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });

        return (prefix || '') + '-' + uniqueId;
    };

    function getAnnotation(node) {
        if (node && node.className && !! ~node.className.indexOf('annotate')) {
            return node;
        }

        if (node.parentNode) {
            return getAnnotation(node.parentNode);
        }

        return false;
    };

    function findAnnotation(node) {
        var annotation = getAnnotation(node);

        if (!annotation) {
            annotation = !! node.getElementsByClassName('annotate')[0];
        }

        return annotation;
    };

    function removeAnnotations(node) {
        var containsAnnotation = findAnnotation(node);

        if (containsAnnotation) {
            var annotations = node.getElementsByClassName('annotate');

            if (annotations) {
                var length = annotations.length;

                while (length--) {
                    annotations[length].remove();
                }
            }
        }

        return node;
    };
    tinymce.create('tinymce.plugins.expresscurate', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init: function(ed, url) {
          
            ed.addButton('annotate', {
                title: 'Add annotation',
                cmd: 'annotate',
                image: url + '/../images/annotate.png'
            });

            ed.addCommand('annotate', function(force) {
                //debugger;
                var isActiveAnnotate = ed.controlManager.get('annotate').active;

                if (isActiveAnnotate && !force) {
                    var annotation = getAnnotation(ed.selection.getNode());

                    if (annotation) {
                        ed.execCommand('mceRemoveNode', 0, annotation.id);
                        ed.controlManager.get('annotate').setActive(false);
                    }
                } else {
                    var id = 'annotation' + getId(),
                        annotationElem = ed.getDoc().createElement('DIV'),
                        content = ed.selection.getContent({
                            'format': 'text'
                        }),
                        htmlContent = '&nbsp;';

                    annotationElem.id = id;
                    annotationElem.className = 'annotate';

                    if (content) {
                        var selectionNode = removeAnnotations(ed.selection.getNode());
                        htmlContent = selectionNode.innerHTML ? selectionNode.innerHTML : '&nbsp;';
                    }

                    annotationElem.innerHTML = htmlContent;

                    ed.execCommand('mceInsertRawHTML', true, annotationElem.outerHTML);
                    // ed.execCommand('mceInsertContent', true, annotationElem.outerHTML);
                    // ed.selection.setContent(annotationElem.outerHTML);
                    // ed.selection.setCursorLocation(ed., 0);

                    ed.controlManager.get('annotate').setActive(true);
                    // ed.selection.setNode(annotationElem);
                }
            });

            ed.onClick.add(function(ed) {
                var annotation = getAnnotation(ed.selection.getNode());

                if (annotation) {
                    ed.controlManager.get('annotate').setActive(true);
                } else {
                    ed.controlManager.get('annotate').setActive(false);
                }
            });

            ed.onKeyDown.add(function(ed, e) {
                var isInAnnotation = getAnnotation(ed.selection.getNode());

                if (isInAnnotation && e.keyCode == 13 && !e.shiftKey) {
                    e.preventDefault();

                    var current = ed.selection.getNode();

                    if (current && current.parentNode) {
                        var temp = document.createDocumentFragment();
                        current.parentNode.insertBefore(temp);

                        var range = ed.dom.createRng();
                        range.setStart(temp, 0);
                        range.setEnd(temp, 0);
                        ed.selection.setRng(range);

                        ed.execCommand('annotate', true);
                        ed.selection.setRng(range);
                    }
                }

                setTimeout(function() {
                    var containsAnnotation = findAnnotation(ed.dom.getRoot());

                    if (!containsAnnotation) {
                        ed.controlManager.get('annotate').setActive(false);
                    }
                }, 0);
            });

            ed.onChange.add(function(ed, e) {
                var containsAnnotation = findAnnotation(ed.dom.getRoot());

                if (!containsAnnotation) {
                    ed.controlManager.get('annotate').setActive(false);
                }
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl: function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo: function() {
            return {
                longname: 'Express curate Buttons',
                author: 'Karlen',
                version: "0.1"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
})();