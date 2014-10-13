define(
    'luxus/svg/TextPath',
    [
        'dojo/_base/declare',
        './Text',
    ],

    function(declare, Text) {
        return declare(
            'luxus.svg.TextPath',
            [Text],
            {
                'xlinkHref': '',

                '_reappend': function() {
                    var textNode = this.domNode.parentNode,
                        parent = textNode.parentNode;

                    if (parent) {
                        parent.removeChild(textNode);
                        parent.appendChild(textNode);
                    }
                },

                'setXlinkHref': function(xlinkHref) {
                    this.domNode.setAttributeNS(
                        'http://www.w3.org/1999/xlink',
                        'xlink:href',
                        xlinkHref
                    );

                    return this;
                },

                'getXlinkHref': function() {
                    return this.domNode.getAttribute('xlink:href');
                }
            }
        );
    }
);
