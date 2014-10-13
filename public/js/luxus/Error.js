define(
    'luxus/Error',
    [
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dojo/_base/event',
        'dojo/_base/window',
        'dojo/json',
        'dojo/text!luxus/templates/Error.html'
    ],

    function(
        declare,
        widget,
        templatedMixin,
        event,
        win,
        JSON,
        template) {

        return declare(
            'luxus/Error',
            [widget, templatedMixin],
            {
                templateString: template,

                title: 'Virhe',

                message: 'Virhe suoritettaessa pyyntöä.',

                messages: {},

                error: null,

                postCreate: function() {
                    var fieldId, messageId, messages = [];
                    if (this.error['message']) {
                        this.message = this.error['message'];
                    }

                    if (this.error &&
                        this.error['response'] &&
                        this.error['response']['data']) {
                        try {
                            var data = JSON.parse(this.error['response']['data']);
                            if (data['message']) {
                                this.message = data['message'];
                            }
                        } catch(error) {
                            console.error(error);
                        }
                    }

                    if (this['messages']) {

                        for(fieldId in this['messages']) {
                            for(messageId in this['messages'][fieldId]) {
                                messages.push(
                                    this['messages'][fieldId][messageId]
                                );
                            }
                        }

                        this.message =
                            '<ul><li>' +
                            messages.join('</li><li>') +
                            '</li></ul>';
                    }

                    this.messageNode.innerHTML = this.message;
                    this.placeAt(win.body());
                },

                _close: function(e) {
                    event.stop(e);
                    this.destroy(false);
                }
            }
        );
    }
);