define(
    'luxus/svg/Group',
    [
        'dojo/_base/declare',
        'luxus/svg/_Base'
    ],

    function(declare, svgBase) {
        return declare(
            'luxus.svg.Group',
            [svgBase],
            {
                'type': 'g',

                'baseClass': 'luxus-svg-group',

                'setCoord': function(x, y) {
                    var translate = ' translate(' + x + ', ' + y + ')';
                    var transform = this.domNode.getAttribute('transform');

                    transform = transform.replace(
                        / ?translate\([^\)]*\) ?/,
                        ''
                    );

                    transform = transform + translate;
                    var transform = this.domNode.setAttribute(
                        'transform',
                        transform
                    );

                    return this;
                },

                'getCoord': function() {
                    var transform = this.domNode.getAttribute('transform');
                    var match;
                    var coords = {
                        x: 0,
                        y: 0
                    }

                    if (transform) {
                        match = transform.match(/translate\((-?\d*)(,| ) ?(-?\d*)\)/);
                        if (match && match[1] && match[3]) {
                            coords.x = match[1];
                            coords.y = match[3];
                        }
                    }

                    return coords;
                }
            }
        );
    }
);
