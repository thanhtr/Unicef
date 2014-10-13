define(
    'aino/customize/Text',
    [
        'dojo',
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dojo/Evented',
        'dojo/dom-construct',
        'dojo/dom-class',
        'dojo/_base/array',
        'dojo/text!aino/templates/customize/Text.html',
    ],

    function(
        dojo,
        declare,
        Widget,
        Evented,
        TemplatedMixin,
        domConstruct,
        domClass,
        array,
        template
    ) {

        return declare(
            'aino.customize.Text',
            [Widget, TemplatedMixin, Evented],
            {
                templateString: template,

                itemValueProp: '',

                baseUrl: '',

                name: '',

                parameter: {},

                _setTimeoutHandle: null,

                updateGroups: function(groups)
                {
                    console.log('groups: ' + groups);
                },

                updateDependOptions: function(parentParamName, value) {
                    if (this.parameter.dependencies.length) {
                        this.parameter.dependencies.forEach(
                            function(dep) {
                                if (dep.id == parentParamName &&
                                    dep.dependencyMap[value] &&
                                    dep.dependencyMap[value].options.length) {
                                    this._setupSelectOptions(
                                        dep.dependencyMap[value].options
                                    );
                                } else {
                                    this._resetOptions();
                                }
                            },
                            this
                        );
                    }
                },

                _resetOptions: function() {
                    this._setupSelectOptions(this.parameter.selectOptions);
                    return this;
                },

                selectFirstItem: function() {
                    if (this.selectNode.options[0]) {
                        this.selectNode.value =
                            this.selectNode.options[0].value;
                        this._selectValueChanged();
                    }

                    return this;
                },

                postCreate: function() {
                    this._setupSelectOptions(this.parameter.selectOptions);

                    this._enableEditor();
                },

                _enableEditor: function() {
                    if (this.parameter.editable) {
                        this.editorNode.removeAttribute('disabled');
                    } else {
                        this._disableEditor();
                    }

                    return this;
                },

                _disableEditor: function() {
                    this.editorNode.setAttribute('disabled', 'disabled');
                },

                _clearOptions: function() {
                    var oldOptions;

                    oldOptions = this.selectNode.options;

                    for(var i = oldOptions.length; i > 0; i--) {
                        this.selectNode.options.remove(oldOptions[i]);
                    }
                },

                _setupSelectOptions: function(selectOptions)
                {
                    var self = this;
                    this.emit(
                        'loading',
                        {
                            'name': this.name
                        }
                    );

                    if (selectOptions.length) {
                        domClass.remove(this.optionsNode, 'aino-hidden');
                    }

                    this._clearOptions();

                    selectOptions.forEach(
                        function(option) {
                            var optionNode = domConstruct.create(
                                'option',
                                {
                                    'value': option.id,
                                    'innerHTML': option.label
                                }
                            );
                            optionNode.editable = option.editable;

                            this.selectNode.options.add(optionNode);
                        },
                        this
                    );

                    clearTimeout(this._setTimeoutHandle);
                    this._setTimeoutHandle = setTimeout(
                        function() {
                            self.emit('ready', {});
                        },
                        10
                    );
                },

                _selectValueChanged: function() {
                    var options = this.selectNode.options;
                    if (options[options.selectedIndex].editable) {
                        this._enableEditor();
                    } else {
                        this._disableEditor();
                    }

                    this.valueNode.value = this.selectNode.value;
                    this.editorNode.value = this.selectNode.value;

                    this._removeCustomOption();
                    this._valueChanged();
                },

                _editorValueChanged: function() {
                    this.valueNode.value = this.editorNode.value;
                    this._addCustomOption(this.editorNode.value);
                    this._valueChanged();
                },

                _addCustomOption: function(value) {
                    var options;
                    this._removeCustomOption();
                    this.selectNode.value = value;
                    options = this.selectNode.options;

                    if (options.selectedIndex == -1) {
                        var optionNode = domConstruct.create(
                            'option',
                            {
                                'value': value,
                                'innerHTML': 'Custom text'
                            }
                        );
                        optionNode.editable = true;
                        optionNode.custom = true;
                        options.add(optionNode);
                        this.selectNode.value = value;
                    }

                    options = this.selectNode.options;
                    if(!(options[options.selectedIndex].editable) ||
                       !(this.parameter.editable)) {
                       this._disableEditor();
                    }

                    return this;
                },

                _removeCustomOption: function() {
                    var options = this.selectNode.options;
                    var lastOption = options[options.length-1];
                    if (lastOption && lastOption.custom) {
                        options.remove(lastOption);
                    }
                    return this;
                },

                _valueChanged: function() {
                    this.emit(
                        'changed',
                        {
                            'name': this.name,
                            'value': this.valueNode.value,
                            'item': this.parameter
                        }
                    );
                }
            }
        );
    }
);
