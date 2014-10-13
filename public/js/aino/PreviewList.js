define(
    'aino/PreviewList',
    [
        'dojo',
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dojo/_base/array',
        'dojo/dom-construct',
        'aino/previewList/Item',
        'dojo/on',
        'dojo/query',
        'dojo/Evented',
        'dijit/registry',
        'dojo/text!aino/templates/PreviewList.html',
        'luxus/Error',
        'dijit/form/TextBox'
    ],

    function(
        dojo,
        declare,
        widget,
        templatedMixin,
        widgetsInTemplateMixin,
        array,
        domConstruct,
        ListItem,
        on,
        query,
        Evented,
        registry,
        template,
        Error) {

        return declare(
            'aino.PreviewList',
            [widget, templatedMixin, widgetsInTemplateMixin, Evented],
            {
                templateString: template,

                widgetsInTemplate: true,

                href: '',

                _changedHref: true,

                updated: false,

                name: '',

                itemValueProp: '',

                thumbnailSize: 'medium',

                selectedItem: null,

                selectFirst: false,

                onChange: function(value) {},

                postCreate: function() {
                    this.update();
                },

                update: function(href) {
                    if (href) {
                        this.setHref(href);
                    }

                    var self = this;
                    if (this.href && this._changedHref) {
                        self.emit(
                            'loading',
                            {
                                'name': this.name
                            }
                        );
                        var xhrArgs = {
                            url: this.href,
                            handleAs: 'json',
                            load: function(data) {
                                self._updateList(data);
                                self._changedHref = false;
                                self.updated = true;
                                self.emit('ready', {});
                            },
                            error: function(e) {
                                new Error({'error': e});
                            }
                        }

                        dojo.xhrGet(xhrArgs);
                    }
                },

                setHref: function(href) {
                    if (this.href != href) {
                        this._changedHref = true;
                        this.updated = false;
                        this.href = href;
                    }

                    return this;
                },

                _clearList: function() {
                    if(this.listNode) {
                        query('li', this.listNode).forEach(function(liNode) {
                            var widget = registry.byNode(liNode);

                            if (widget) {
                                widget.destroyRecursive(false);
                            }
                        });
                    }
                },

                _updateList: function(data) {
                    this._clearList();

                    data.forEach(
                        function(item) {
                            this._addItem(item)
                        },
                        this
                    );

                    if (this.selectFirst) {
                        this.selectFirstItem();
                    }
                },

                _addItem: function(item) {
                    var self = this;
                    //var liNode = domConstruct.create('li', null, this.listNode);
                    var widget = new ListItem(
                        {
                            'item': item,
                            'itemValueProp': this.itemValueProp,
                            'thumbnailSize': this.thumbnailSize
                        }
                    );

                    widget.startup();
                    widget.placeAt(this.listNode);

                    on(widget, 'selected', function() {
                        self._onItemSelection(this);
                    });

                    on(widget, 'deleted', function(deleted) {
                        if (self.selectedItem.id == deleted.item.item.id) {
                            self.selectFirstItem();
                        }
                    });
                },

                _onItemSelection: function(caller) {
                    var item = caller.item;
                    this.selectedItem = item;
                    if (item[this.itemValueProp]) {
                        this._updateValue(item[this.itemValueProp]);
                    }
                },

                _updateValue: function(value) {
                    var oldValue = this.valueNode.get('value');

                    if (oldValue != value) {
                        this.valueNode.set('value', value);
                        this.onChange(value);
                        this.emit(
                            'changed',
                            {
                                'name': this.name,
                                'value': value,
                                'item': this.selectedItem
                            }
                        );
                    }
                },

                selectFirstItem: function() {
                    if (this.listNode.firstChild) {
                        registry.byNode(this.listNode.firstChild)._onClick();
                    }
                },

                selectLastItem: function() {
                    if (this.listNode.lastChild) {
                        registry.byNode(this.listNode.lastChild)._onClick();
                    }
                }
            }
        );
    }
);
