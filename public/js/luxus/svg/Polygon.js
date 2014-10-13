define(
    'luxus/svg/Polygon',
    [
        'dojo',
        'dojo/_base/declare',
        'dojo/_base/array',
        'luxus/svg/_Base'
    ],

    function(dojo, declare, array, svgBase) {
        return declare(
            'luxus.svg.Polygon',
            [svgBase],
            {
                style: '',

                type: 'polygon',

                baseClass: 'luxus-svg-polygon',

                attributeMap: {},

                _parsePoints: function() {
                    var points =
                        this.domNode.getAttribute('points').split(' ');
                    var result = [];
                    array.forEach(points, function(str) {
                        var splited = str.split(',');
                        result.push({
                            'x': Number(splited[0]),
                            'y': Number(splited[1])
                        });
                    });

                    return result;
                },

                setCoord: function(x, y) {
                    var points = [];
                    var origin = this.getCoord();
                    var dx = origin.x - Number(x);
                    var dy = origin.y - Number(y);

                    array.forEach(this.getPoints(), function(point) {
                        point.x = point.x - dx;
                        point.y = point.y - dy;
                        points.push(point.x + ',' + point.y);
                    });

                    this.setAttr('points', points.join(' '));

                    return this;
                },

                getCoord: function() {
                    var points = this.getPoints();

                    if (points.length > 0) {
                        return points[0];
                    }

                    return {
                        'x': 0,
                        'y': 0
                    };
                },

                setPoints: function(points) {
                    var pointsArray = [];
                    array.forEach(points, function(point) {
                        pointsArray.push(point.x + ',' + point.y);
                    });

                    this.setAttr('points', pointsArray.join(' '));
                    return this;
                },

                getPoints: function() {
                    return this._parsePoints();
                },

                getBoundingBox: function() {
                    var minX = 10000,
                        maxX = -10000,
                        minY = 10000,
                        maxY = -10000;

                    array.forEach(this.getPoints(), function(point) {
                        if (point.x < minX) {
                            minX = point.x;
                        }

                        if (point.x > maxX) {
                            maxX = point.x;
                        }

                        if (point.y < minY) {
                            minY = point.y;
                        }

                        if (point.y > maxY) {
                            maxY = point.y;
                        }
                    });

                    return {
                        'width': Number((maxX - minX)),
                        'height': Number((maxY - minY)),
                        'x': minX,
                        'y': minY
                    };
                }
            }
        );
    }
);
