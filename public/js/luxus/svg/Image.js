define(
    'luxus/svg/Image',
    [
        'dojo/_base/declare',
        'luxus/svg/_Base',
        'dojo/dom-construct'
    ],

    function(declare, svgBase, domConstruct) {
        return declare(
            'luxus.svg.Image',
            [svgBase],
            {
                'resizeOnload': true,

                'type': 'image',

                'baseClass': 'luxus-svg-image',

                'setCoord': function(x, y) {
                    this.domNode.setAttribute('x', x);
                    this.domNode.setAttribute('y', y);

                    return this;
                },

                'getCoord': function() {
                    return {
                        'x': Number(this.domNode.getAttribute('x')),
                        'y': Number(this.domNode.getAttribute('y'))
                    };
                },

                'setXlinkHref': function(xlinkHref) {
                    this.domNode.setAttributeNS(
                        'http://www.w3.org/1999/xlink',
                        'xlink:href',
                        xlinkHref
                    );

                    this._updateImage();

                    return this;
                },

                'getHref': function() {
                    return this.domNode.getAttributeNS(
                        'http://www.w3.org/1999/xlink',
                        'href'
                    );
                },

                '_updateImage': function() {
                    var image = this;
                    var href = this.getHref();
                    domConstruct.create(
                        'img',
                        {
                            'src': href,
                            'onload': function() {
                                if (image.resizeOnload) {
                                    image.setAttr('width', this.width);
                                    image.setAttr('height', this.height);
                                }

                                image._fireEvent('onLoad');
                            }
                        }
                    );
                },

                'onLoad': function() {}
            }
        );
    }
);

