define(
    'aino/FilteringList',
    [
        'dojo',
        'dojo/_base/declare',
        'aino/PreviewList',
        'dojo/dom-construct',
        'dojo/dom-form',
        'dojo/hash',
        'dojo/_base/array',
        'dojo/on',
        'dojo/text!aino/templates/FilteringList.html',
    ],

    function(
        dojo,
        declare,
        PreviewList,
        domConstruct,
        domForm,
        hash,
        array,
        on,
        template) {

        return declare(
            'aino.FilteringList',
            [PreviewList],
            {
                templateString: template,

                href: '',

                filterPropertiesUrl: '',

                filterPropertyName: '',

                itemIdPropName: '',

                forwardUrl: '',

                itemSelector: null,

                _selectItem: function() {
                    if (this.forwardUrl) {
                        var url =
                            this.forwardUrl + '?' +
                            domForm.toQuery(this.itemSelectionForm);
                        window.location = url;
                    }
                },

                postCreate: function() {
                    this._updateFilterPropertyValues();
                },

                _updatePreviewList: function(query) {
                    var url = this.href;
                    var self = this;
                    if (query) {
                        url = url + '?' + query;
                    }

                    if (this.itemSelector) {
                        this.itemSelector.update(url);
                    } else {
                        var previewList = new PreviewList({
                            itemValueProp:this.itemIdPropName,
                            name: this.itemIdPropName,
                            href: url
                        });

                        previewList.on(
                            'changed',
                            function() {
                                self._selectItem();
                            }
                        );

                        previewList.placeAt(this.itemSelectionForm);

                        this.itemSelector = previewList;
                    }


                    return this;
                },

                _updateFromHash: function() {
                    var values = hash().split(',');
                    array.forEach(
                        this.propertySelector.options,
                        function(option) {
                            if (values.indexOf(option.value) != -1) {
                                option.selected = true;
                            }
                        }
                    );

                    this._upladeList();

                    return this;
                },

                _updateHash: function(query) {
                    if (query) {
                        hash(query);
                    }
                    return this;
                },

                _updateFilterPropertyValues: function() {
                    var self = this;
                    var xhrArgs = {
                        'url': this.filterPropertiesUrl,
                        'handleAs': 'json',
                        'load': function(data) {
                            self._setupFilterItems(data)
                                ._updateFromHash();
                        }
                    };

                    dojo.xhrGet(xhrArgs);

                    return this;
                },

                _setupFilterItems: function(items) {
                    domConstruct.empty(this.propertySelector);
                    items.forEach(
                        function(item) {
                            domConstruct.create(
                                'option',
                                {
                                    'value': item['name'],
                                    'innerHTML': item['name']
                                },
                                this.propertySelector
                            );
                        },
                        this
                    );

                    return this;
                },

                _upladeList: function() {
                    var values = domForm.toObject(this.filterForm);
                    var query = values[this.filterPropertyName].join(',');
                    if (query) {
                        this._updateHash(query);
                        query = this.filterPropertyName + '=' + query;
                    }
                    this._updatePreviewList(query);

                    return this;
                }
            }
        );
    }
);
