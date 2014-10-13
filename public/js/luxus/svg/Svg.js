define(
    'luxus/svg/Svg',
    [
        'dojo',
        'dojo/_base/declare',
        'dijit/_WidgetBase',
        'dojo/dom-construct',
        'dojo/parser',
        'dojo/_base/xhr'
    ],

    function(
        dojo,
        declare,
        widgetBase,
        construct,
        parser
    ) {
        return declare(
            'luxus.svg.Svg',
            [widgetBase],
            {
                'widgetsInTemplate': true,

                'xmlns': 'http://www.w3.org/2000/svg',

                'width': '300',

                'height': '300',

                'viewPort': '0 0 0 0',

                'href': '',

                '_buildDom': function(svgXml) {
                    var node = null;
                    if (svgXml) {
                        if (svgXml.firstChild.tagName == 'svg') {
                            node = svgXml.firstChild;
                        } else if (svgXml.firstChild.nextSibling.tagName == 'svg') {
                            node = svgXml.firstChild.nextSibling;
                        }

                        if (this.domNode) {
                            construct.place(
                                node,
                                this.domNode,
                                'replace'
                            );
                        } else {
                            this.domNode = node;
                        }
                    } else {
                        this.domNode = this.srcNodeRef;
                    }

                    if (this.domNode.xml) {
                        this.domNode =
                            construct.create(
                                'div',
                                {'innerHTML': this.domNode.xml}
                            );
                    }
                    parser.parse(this.domNode);
                    this.onLoad(svgXml);
                },

                'onLoad': function(/*String*/svgXml) {},

                'buildRendering': function() {
                    if (this.href) {
                        this.update();
                    } else {
                        this.domNode = this.srcNodeRef;
                    }
                },

                'getSvgXML': function() {
                    var svg = this.domNode.cloneNode(true);
                    var div = construct.create('div');
                    div.appendChild(svg);

                    return div.innerHTML;
                },

                'update': function() {
                    var that = this;

                    if (this.href) {
                        dojo.xhrGet({
                            'url': this.href,
                            'handleAs': 'xml',
                            'sync': true,
                            'load': function(svgXml) {
                                that._buildDom(svgXml);
                            }
                        });
                    }
                }
            }
        );
    }
);
