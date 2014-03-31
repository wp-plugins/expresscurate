(function() { 
    var getId = function(prefix) {
        var uniqueId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });

        return (prefix || '') + '-' + uniqueId;
    };


	
	 function getElem(node,cssClass) {
        if (node && node.className && !! ~node.className.indexOf(cssClass)) {
            return node;
        }

        if (node.parentNode) {
            return getElem(node.parentNode,cssClass);
        }

        return false;
    };

	
	function textboxCommand(ed,elem,cssClass){
		var isActiveTextbox = ed.controlManager.get(elem).active;

		if (isActiveTextbox ) {
			var texbox = getElem(ed.selection.getNode(),cssClass);

			if (texbox) {
				ed.execCommand('mceRemoveNode', texbox.id);
				ed.controlManager.get(elem).setActive(false);
			}
		} else {
			var node=ed.selection.getNode(),
				selectedClass=jQuery(node).attr('class');
			if(selectedClass!=cssClass){
			
				var content='';
				if (node.nodeName!='DIV') {
					content=ed.selection.getContent()
				}else {
					content=ed.selection.getNode().innerHTML;
					//node.innerHTML ='';
					jQuery(node).remove();
				}
				if(selectedClass && node.nodeName=='DIV'){
					ed.execCommand('mceRemoveNode', 0, node.id);
				}
				if(node.nodeName=='P' && jQuery(node).html()=='&nbsp;'){
					jQuery(node).remove();
				}
				var id = elem + getId(),
				    texboxElem = ed.getDoc().createElement('DIV');
				
				texboxElem.id = id;
				texboxElem.className =cssClass;
				texboxElem.innerHTML = content;
				ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
				ed.controlManager.get(elem).setActive(true);
			}
		}
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
		
            ed.addButton('annotation', {
                title: 'Add Annotation (Ctrl + Up)',
                cmd: 'annotation',
                image: url + '/../images/annotate.png'
            });
			ed.addButton('righttextbox', {
                title: 'Add Right-Box (Ctrl + R | Ctrl + Right)',
                cmd: 'righttextbox',
                image: url + '/../images/rightBox.png',
            });
			ed.addButton('justifytextbox', {
                title: 'Add Center-Box (Ctrl + J | Ctrl + Down)',
                cmd: 'justifytextbox',
                image: url + '/../images/justifyBox.png'
            });
			ed.addButton('lefttextbox', {
                title: 'Add Left-Box (Ctrl + L | Ctrl + Left)',
                cmd: 'lefttextbox',
                image: url + '/../images/leftBox.png'
            });
			
			
          

           
            ed.onKeyDown.add(function(ed, e) {
				//console.log('Key down event: ' + e.keyCode);
				if (e.ctrlKey && (e.keyCode == 76 || e.keyCode == 37)) {     // ctrl+l|ctrl+left
					 e.returnValue = false;
					 textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
					 e.preventDefault();
					 return false;
					}else if (e.ctrlKey && (e.keyCode == 82 || e.keyCode == 39)) {     // ctrl+r|ctrl+right
						 e.returnValue = false;
						 textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
						 e.preventDefault();
						 return false;
						}else if (e.ctrlKey && (e.keyCode == 74 || e.keyCode == 40)) {     // ctrl+j|ctrl+down
							 e.returnValue = false;
							 textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
							 e.preventDefault();
							 return false;
							}else if (e.ctrlKey && e.keyCode == 38) {     // ctrl+up
								 e.returnValue = false;
								 textboxCommand(ed,'annotation','annotate');
								 e.preventDefault();
								 return false;
							}
				
               
            });

           
			ed.onNodeChange.add(function(ed) {
				var node = ed.selection.getNode(),
					cssClass=jQuery(node).attr('class'),
					activeButton=' ';
					  if (cssClass == 'expresscurate_fl_text_box')
							activeButton='lefttextbox';
						else if(cssClass == 'expresscurate_fr_text_box')
								activeButton='righttextbox';
							else if(cssClass == 'expresscurate_justify_text_box')
									activeButton='justifytextbox';
								else if(cssClass == 'annotate')
										activeButton='annotation';
				ed.controlManager.get('lefttextbox').setActive(false);
				ed.controlManager.get('righttextbox').setActive(false);
				ed.controlManager.get('justifytextbox').setActive(false);
				ed.controlManager.get('annotation').setActive(false);
				if(activeButton!=' ')
					ed.controlManager.get(activeButton).setActive(true);
			});
			
			//lefttextbox
			 ed.addCommand('lefttextbox',  function() {
				textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
			});
			//righttextbox
			 ed.addCommand('righttextbox', function() {
				textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
            });
			//justifytextbox
			 ed.addCommand('justifytextbox', function() {
				textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
            });
			//annotation
			 ed.addCommand('annotation', function() {
				textboxCommand(ed,'annotation','annotate');
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
                author: 'ExpressCurate',
                version: "0.1"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
})();