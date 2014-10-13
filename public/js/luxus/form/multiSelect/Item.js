define(
    'luxus/form/multiSelect/Item',
    [
        'dojo',
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dojo/Evented',
        'dojo/_base/event',
        'dojo/text!luxus/templates/form/multiSelect/Item.html'
    ],

    function(
        dojo,
        declare,
        widget,
        templatedMixin,
        Evented,
        event,
        template) {

        return declare(
            'luxus.form.multiSelect.Item',
            [widget, templatedMixin, Evented],
            {
                templateString: template,

                label: '',

                value: null,

                _removeItem: function(e) {
                    event.stop(e);
                    this.emit('removed', {'value': this.value});
                    this.destroy(false);
                }
            }
        );
    }
);
