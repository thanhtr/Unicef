define(
    'luxus/form/MultiSelect',
    [
        'dojo',
        'dojo/query',
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dojo/_base/array',
        'dojo/_base/lang',
        'luxus/form/multiSelect/Item',
        'dojo/on',
        'dojo/Evented',
        'dojo/text!luxus/templates/form/MultiSelect.html'
    ],

    function(
        dojo,
        query,
        declare,
        widget,
        templatedMixin,
        widgetsInTemplateMixin,
        array,
        lang,
        selectedItem,
        on,
        Evented,
        template) {

        return declare(
            'luxus.form.MultiSelect',
            [widget, templatedMixin, widgetsInTemplateMixin, Evented],
            {
                templateString: template,

                postCreate: function() {
                    this.valueNode.name = this.srcNodeRef.name;
                    query('option', this.srcNodeRef).forEach(
                        function(option) {
                            this._addOption(option);
                        },
                        this
                    );
                    this.optionsSelect.value = null;
                },

                _addOption: function(option) {
                    var selectedValues = [];

                    if (option.selected) {
                        selectedValues.push(option.value);
                        option.removeAttribute('selected');
                    }

                    this.optionsSelect.options.add(lang.clone(option));
                    this.valueNode.options.add(option);

                    selectedValues.forEach(
                        function(selectedValue) {
                            this._selectItem(selectedValue);
                        },
                        this
                    );
                },

                _itemSelectorChanged: function() {
                    var value = this.optionsSelect.value;

                    this._selectItem(value);

                    this.optionsSelect.value = null;
                },

                _findOption: function(value) {
                    var foundOption;
                    array.some(
                        this.valueNode.options,
                        function(option) {
                            if (option.value == value) {
                                foundOption = option;
                                return true;
                            }

                            return false;
                        }
                    );

                    return foundOption;
                },

                _selectItem: function(value) {
                    var self = this;
                    var option = this._findOption(value);

                    if (option && !option.selected) {
                        var item = new selectedItem({
                            'value': option.value,
                            'label': option.innerHTML
                        });

                        item.placeAt(this.selectedItemsCont);
                        item.on(
                            'removed',
                            function(e) {
                                self._unselectValue(e.value);
                            }
                        );

                        option.setAttribute('selected', 'selected');
                    }


                    return this;
                },

                _unselectValue: function(value) {
                    var option = this._findOption(value);
                    if (option) {
                        option.removeAttribute('selected');
                    }

                    return this;
                }
            }
        );
    }
);
