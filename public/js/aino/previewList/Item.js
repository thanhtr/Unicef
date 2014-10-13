define(
    'aino/previewList/Item',
    [
        'dojo',
        'dijit/registry',
        'dojo/dom-construct',
        'dojo/_base/declare',
        'dojo/dom-class',
        'dojo/query',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dojo/_base/event',
        'dojo/Evented',
        'dojo/text!aino/templates/previewList/Item.html',
        'dojo/NodeList-dom'
    ],

    function(
        dojo,
        registry,
        domConstruct,
        declare,
        domClass,
        query,
        widget,
        templatedMixin,
        widgetsInTemplateMixin,
        event,
        evented,
        template) {

        return declare(
            'aino.previewList.Item',
            [widget, templatedMixin, widgetsInTemplateMixin, evented],
            {
                templateString: template,

                item: null,

                thumbnailSize: 'medium',

                itemValueProp: '',

                postCreate: function() {
                    if(this.item.ugc) {
                        this._enableDeletion();
                    }

                    if (this.item) {
                        this.imageNode.setAttribute(
                            'src',
                            this.item.thumbnails[this.thumbnailSize]
                        );

                        if (this.item.name) {
                            this.nameNode.innerHTML =
                                this.item.name.replace(/_/g, ' ');
                        }
                    }
                },

                onSelection: function(item) {},

                onDelete: function() {},

                _onClick: function() {
                    query(
                        'li.selected',
                        this.domNode.parentElement
                    ).removeClass('selected');

                    domClass.add(this.domNode, 'selected');
                    this.emit('selected', {});
                },

                _enableDeletion: function() {
                    domClass.remove(this.deleteNode, 'hidden');
                },

                _delete: function(e) {
                    event.stop(e);
                    var self = this;
                    if(this.item.ugc) {
                        dojo.xhrDelete({
                            url: this.item.url,
                            handleAs: 'json',
                            load: function(data) {
                                if(data) {
                                    self._deleteItem();
                                }
                            }
                        });
                    }
                },

                _deleteItem: function() {
                    domConstruct.destroy(this.domNode);
                    this.emit('deleted', {item: this});
                    registry.remove(this.id);
                }
            }
        );
    }
);
