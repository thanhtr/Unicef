define(
    'aino/UgcPreviewList',
    [
        'dojo/_base/declare',
        'aino/PreviewList',
        'dojo/io/iframe',
        'luxus/Error',
        'dojo/text!aino/templates/UgcPreviewList.html'
    ],

    function(
        declare,
        PreviewList,
        iframe,
        Error,
        template) {

        return declare(
            'aino.UgcPreviewList',
            [PreviewList],
            {
                templateString: template,

                postCreate: function() {
                    this.uploadFormNode.action = this.href.split("?")[0];
                    this.inherited(arguments);
                },

                _uploadFile: function() {
                    var self = this;
                    var url = this.href.split('?')[0];
                    var xhrArgs = {
                        form: this.uploadFormNode,
                        url: url,
                        handleAs: 'json',
                        content: {'format': 'dojo'},
                        load: function(data) {
                            if (data && !data['error']) {
                                self._addItem(data);
                                self.selectLastItem();
                                self.emit('uploaded', {});
                            } else {
                                new Error(data);
                            }
                            self.uploadFormNode.reset();
                        }
                    };

                    iframe.send(xhrArgs);
                }
            }
        );
    }
);
