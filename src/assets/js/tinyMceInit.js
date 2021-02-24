jQuery(document).ready(function() {
    jQuery('.fluentform-post-content').each((index, el) => {
        let $el = jQuery(el);
        let editorId = $el.attr('id');
        if(window.wp && window.wp.editor && editorId) {
            window.wp.editor.initialize(editorId, {
                    mediaButtons: false,
                    tinymce: {
                        height: 250,
                        toolbar1: 'formatselect,table,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo',
                        setup(ed) {
                            ed.on('change', (ed, l) => {
                                let content = wp.editor.getContent(editorId);
                                $el.val(content).trigger('change');
                            });
                        }
                    },
                    quicktags: false
                });
        }
    });
});
