define(
    'luxus/svg/Text',
    [
        'dojo/_base/declare',
        'dojo/query',
        'luxus/svg/_Base',
        'dojo/has',
        'dojo/_base/sniff'
    ],

    function(declare, query, svgBase, has) {
        return declare(
            'luxus.svg.Text',
            [svgBase],
            {
                'x': 0,

                'y': 0,

                'dx': 0,

                'dy': 0,

                'style': '',

                'baseClass': 'luxus-svg-text',

                'textLength': null,

                'lengthAdjust': null,

                'lineHeight': '1em',

                'verticalCenter': false,

                '_postRendering': function() {
                    var x = this.domNode.getAttribute('x');
                    var y = this.domNode.getAttribute('y');
                    if (x && y) {
                        this.setCoord(x, y);
                    }

                    this.domNode.setAttributeNS(this.xmlns, 'x', 0);
                    this.domNode.setAttributeNS(this.xmlns, 'y', 0);
                },

                '_reappend': function() {
                    var parent = this.domNode.parentNode,
                        next = this.domNode.nextSibling;

                    if (parent) {
                        parent.removeChild(this.domNode);
                        if (next) {
                            parent.insertBefore(this.domNode, next);
                        } else {
                            parent.appendChild(this.domNode);
                        }
                    }
                },

                '_centerTextVertically': function() {
                    var y, nodes = query('tspan', this.domNode);
                    var boundingBox = this.domNode.getBBox();
                    var lineHeight;

                    if(nodes.length) {
                        lineHeight = boundingBox.height / nodes.length;

                        y = lineHeight * ((nodes.length-1)/-2);

                        this.domNode.firstChild.setAttributeNS(
                            null,
                            'dy',
                            y + 'px'
                        );
                    } else {
                        this.domNode.firstChild.setAttributeNS(
                            null,
                            'y',
                            0
                        );
                    }
                },

                'setText': function(text) {
                    var that = this;
                    var tspanNode;

                    while (this.domNode.hasChildNodes()) {
                        this.domNode.removeChild(this.domNode.lastChild);
                    }

                    var svg = this._getSvgNode();
                    svgDocument = svg.ownerDocument;

                    text.split("\n").forEach(function(lineText, lineNumber) {
                        tspanNode = svgDocument.createElementNS(
                            that.xmlns,
                            'tspan'
                        );
                        tspanNode.setAttributeNS(null, 'x', '0');

                        if (lineNumber) {
                            tspanNode.setAttributeNS(null, 'dy', that.lineHeight);
                        }

                        var textNode = svgDocument.createTextNode(lineText);
                        tspanNode.appendChild(textNode);
                        that.domNode.appendChild(tspanNode);
                    });

                    if(has('chrome') == 18 || has('chrome') == 19) {
                        that._reappend();
                    }

                    if (this.verticalCenter) {
                        this._centerTextVertically();
                    }

                    return this;
                },

                'getText': function() {
                    var nodes = query('tspan', this.domNode);

                    var text = [];
                    if (nodes.length) {
                        nodes.forEach(function(node) {
                            text.push(node.textContent);
                        });
                    } else {
                        text.push(this.domNode.textContent);
                    }

                    return text.join("\n");
                },

                'setCoord': function(x, y) {
                    var transform = 'matrix(1 0 0 1 ' + x + ' ' + y + ')';
                    this.domNode.setAttribute('transform', transform);

                    return this;
                },


                'getCoord': function() {
                    var transform = this.domNode.getAttribute('transform');
                    if (transform) {
                        transform = transform.replace('matrix(', '').replace(')', '').split(' ');
                        return {
                            'x': Number(transform[4]),
                            'y': Number(transform[5])
                        };
                    } else {
                        return {
                            'x': this.getAttr('x'),
                            'y': this.getAttr('y')
                        };
                    }
                }
            }
        );
    }
);
