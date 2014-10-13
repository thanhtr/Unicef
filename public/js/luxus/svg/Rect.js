define(
    'luxus/svg/Rect',
    [
        'dojo/_base/declare',
        'luxus/svg/_Base'
    ],

    function(declare, svgBase) {
        return declare(
            'luxus.svg.Rect',
            [svgBase],
            {
                'x': 0,

                'y': 0,

                'width': 0,

                'height': 0,

                'style': '',

                'type': 'rect',

                'baseClass': 'luxus-svg-rect',

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
                }
            }
        );
    }
);
