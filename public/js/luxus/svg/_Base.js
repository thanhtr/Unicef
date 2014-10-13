define(
    'luxus/svg/_Base',
    [
        'dojo/_base/declare',
        'dijit/_Widget',
        'dojo/query',
        'dojo/_base/connect',
        'dojo/_base/event',
        'dojo/dom-class',
        'dojo/_base/array'
    ],

    function(
        declare,
        widget,
        query,
        connect,
        event,
        domClass
    ) {
        return declare(
            'luxus.svg._Base',
            [widget],
            {
                'xmlns': 'http://www.w3.org/2000/svg',

                'class': '',

                'style': '',

                'type': '',

                'movable': false,

                'linkMovementTo': '',

                '_preRendering': function() {},

                '_postRendering': function() {},

                '_mouseDownEventHandle': null,

                'fireRecursiveEvents': true,

                'attributeMap': {},

                'moveStart': function() {},

                'moveEnd': function() {},

                'mouseUp': function() {},

                'select': function() {},

                'mouseDown': function() {},

                '_fireEvent': function(eventName) {
                    var args = [{'type': eventName, 'target': this}];

                    if(this[eventName]) {
                        try {
                            this[eventName].apply(this, args);
                        } catch(exception) {
                            console.error(
                                'exception in svgWidget handler for:',
                                eventName
                            );
                            console.error(exception);
                        }
                    }

                    return this;
                },

                'setAttr': function(key, value) {
                    if(value) {
                        this.domNode.setAttribute(key, value);
                    }

                    return this;
                },

                'getAttr': function(key) {
                    return this.domNode.getAttribute(key);
                },

                'setCoord': function(x, y) {},

                'getCoord': function() {},

                'getBoundingBox': function() {
                    var boundingBox = this.getCoord();
                    boundingBox.width = this.width;
                    boundingBox.height = this.height;

                    return boundingBox;
                },

                'move': function(x, y, fireEvent) {
                    fireEvent = fireEvent || fireEvent === undefined;
                    if (fireEvent) {
                        this._fireEvent('moveStart');
                    }

                    var origin = this.getCoord();
                    var dx = origin.x - Number(x);
                    var dy = origin.y - Number(y);
                    var originDomNode = this.domNode;
                    var fireRecursiveEvents = this.fireRecursiveEvents;

                    var redrawHandle = this._getSvgNode().suspendRedraw(500);

                    if (this.linkMovementTo) {
                        query(this.linkMovementTo, this._getSvgNode()).forEach(
                            function(node) {
                                var svgDigit = dijit.registry.byNode(node);
                                if (svgDigit &&
                                    svgDigit.getCoord &&
                                    svgDigit.move &&
                                    (originDomNode != node)) {

                                    var coords = svgDigit.getCoord();

                                    svgDigit.move(
                                        Number(coords.x) - dx,
                                        Number(coords.y) - dy,
                                        fireRecursiveEvents
                                    );
                                }
                            }
                        );
                    }

                    this.setCoord(x, y);

                    this._getSvgNode().unsuspendRedraw(redrawHandle);

                    if (fireEvent) {
                        this._fireEvent('moveEnd');
                    }

                    return this;
                },

                'moveBy': function(dx, dy, fireEvent) {
                    var coords = this.getCoord();
                    this.move(
                        coords.x + Number(dx),
                        coords.y + Number(dy),
                        fireEvent
                    );

                    return this;
                },

                '_getSvgNode': function() {
                    return this.domNode.ownerSVGElement;
                },

                'enableMovement': function() {
                    var self = this;

                    this.domNode.setAttribute('cursor', 'move');
                    domClass.add(this.domNode, 'movable-svg-element');

                    connect.connect(
                        self.domNode,
                        'onmousedown',
                        function(e) {
                            if(e.which == 1) {
                                event.stop(e);
                                var coord = self.getCoord();
                                var startX = coord.x - e.clientX;
                                var startY = coord.y - e.clientY;

                                var svgNode = self._getSvgNode();

                                var downEventHandle = connect.connect(
                                    svgNode,
                                    'onmousemove',
                                    function(e) {
                                        event.stop(e);
                                        self.move(
                                            (Number(e.clientX) + startX),
                                            (Number(e.clientY) + startY)
                                        );
                                    }
                                );

                                var upEventHandle = connect.connect(
                                    svgNode,
                                    'onmouseup',
                                    function(e) {
                                        connect.disconnect(downEventHandle);
                                        connect.disconnect(upEventHandle);
                                        self._fireEvent('mouseUp');
                                    }
                                );
                            }
                        }
                    );

                    return this;
                },

                'disableMovement': function() {
                    if (this._mouseDownEventHandle) {
                        connect.disconnect(this._mouseDownEventHandle);
                    }

                    this.domNode.removeAttribute('cursor');
                    domClass.remove(this.domNode, 'movable-svg-element');

                    return this;
                },

                'buildRendering': function() {
                    this._preRendering();

                    this.domNode = this.srcNodeRef;

                    if (this.movable) {
                        this.enableMovement();
                    }

                    this._postRendering();

                    var that = this;
                    connect.connect(this.domNode, 'onmousedown', function(e) {
                        that._fireEvent('select');
                    });

                    return this;
                }
            }
        );
    }
);
