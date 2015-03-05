    CKEDITOR.plugins.add( 'tokens',
    {   
       requires : ['richcombo'], //, 'styles' ],
       init : function( editor )
       {
          var config = editor.config,
             lang = editor.lang.format;

          // Gets the list of tags from the settings.
          var tags = []; //new Array();
          //this.add('value', 'drop_text', 'drop_label');
          
//          var i = 0;
//          jQuery(config.CustomTags).each( function(index, value) { 
//              tags[i]=[value.value, value.drop_text, value.drop_label];
//              i++;
//        	});
          tags[0]=["[%name%]", "Name and Last Name", "Name and Last Name"];
          tags[1]=["[%title%]", "Catalog Title", "Catalog Title"];
          tags[2]=["[%date_end%]", "Date End", "Date End"];
          
          // Create style objects for all defined styles.

          editor.ui.addRichCombo( 'tokens',
             {
                label : "Tokens",
                title :"Tokens",
                voiceLabel : "Tokens",
                className : 'cke_format',
                multiSelect : false,

                panel :
                {
                   css : [ config.contentsCss, CKEDITOR.getUrl( editor.skinPath + 'editor.css' ) ],
                   voiceLabel : lang.panelVoiceLabel
                },

                init : function()
                {
                   this.startGroup( "Tokens" );
                   //this.add('value', 'drop_text', 'drop_label');
                   for (var this_tag in tags){
                      this.add(tags[this_tag][0], tags[this_tag][1], tags[this_tag][2]);
                   }
                },

                onClick : function( value )
                {         
                   editor.focus();
                   editor.fire( 'saveSnapshot' );
                   editor.insertHtml(value);
                   editor.fire( 'saveSnapshot' );
                }
             });
       }
    });

