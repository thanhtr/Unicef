define(
    'aino/Mapper',
    [
        'dojo',
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dojo/query',
        'dijit/registry',
        'dojo/_base/array',
        'dojo/json',
        'dojo/dom-construct',
        'dojo/text!aino/templates/Mapper.html',
        'aino/PreviewList',
        'luxus/svg/Rect',
        'luxus/svg/Svg',
        'luxus/svg/Polygon',
        'luxus/svg/Image'
    ],

    function(
        dojo,
        declare,
        widget,
        templatedMixin,
        widgetsInTemplateMixin,
        query,
        registry,
        array,
        json,
        domConstruct,
        template) {
        return declare(
            'aino.Mapper',
            [widget, templatedMixin, widgetsInTemplateMixin],
            {
                templateString: template,

                baseUrl: '',

                href: '',

                componentName: 'screen',

                componentDescription: 'Component',

                postCreate: function() {
                    var self = this;
                    var xhrArgs = {
                        url: this.baseUrl + 'api/asset-groups/',
                        handleAs: 'json',
                        load:function(data) {
                            array.forEach(data, function(option) {
                                self.filterSelectNode.options.add(
                                    new Option(option.name, option.name)
                                );

                                self.groupSelectNode.options.add(
                                    new Option(option.name, option.id)
                                );
                            });
                        }
                    }

                    dojo.xhrGet(xhrArgs);
                },

                _updateAssets: function() {
                    var url = this.href + this.filterSelectNode.value;
                    this.sourceImageSelector.update(url);
                },

                _updateMask: function() {
                    var points = [];
                    var cornerPoint;
                    query('rect.cornerPoint').forEach(function(node) {
                        var cornerPoint = registry.byNode(node).getCoord();
                        points.push({
                            x: cornerPoint.x + 10,
                            y: cornerPoint.y + 10
                        });
                    });

                    this.maskNode.setPoints(points);
                },

                _setupMask: function(component) {
                    var args = null;
                    var filter = this._findFilter(
                        component,
                        'DistortPerspective'
                    );

                    if (filter) {
                        args = filter.options.args;
                        query('rect.cornerPoint').forEach(
                            function(node, index) {
                                var key = index * 2;
                                var cornerPoint = registry.byNode(node);
                                cornerPoint.setCoord(
                                    args[key] - 10,
                                    args[key + 1] - 10
                                );
                            }
                        );

                        this._updateMask();
                    }
                },

                _setupGroups: function(component) {
                    var value = [];
                    array.forEach(
                        component.groups,
                        function(group) {
                            value.push(group.id);
                        }
                    );

                    this.groupSelectNode.value = null;
                    array.forEach(
                        this.groupSelectNode.options,
                        function(option) {
                            if (value.indexOf(option.value) != -1) {
                                option.selected = true;
                            }
                        }
                    );
                },

                _findComponent: function(componentName) {
                    if (!this.sourceImageSelector.selectedItem) {
                        return null;
                    }
                    var components =
                        this.sourceImageSelector.selectedItem.components;
                    var foundComponent = null;
                    array.forEach(components, function(component) {
                        if (component.name.toLowerCase() == componentName.toLowerCase()) {
                            foundComponent = component;
                        }
                    });

                    return foundComponent;
                },

                _findFilter: function(component, filterName) {
                    var returnValue = null;
                    array.forEach(component.filters, function(filter) {
                        if(filter.type.toLowerCase() == filterName.toLowerCase()) {
                            returnValue = filter;
                        }
                    });
                    return returnValue;
                },

                _updateMapperUrl: function(value) {
                    var screenComponent = this._findComponent(
                        this.componentName
                    );
                    if (screenComponent) {
                        this._setupMask(screenComponent);
                        this._setupGroups(screenComponent);
                    }
                    this.sourceImageNode.setXlinkHref(value + '?format=png');
                },

                _saveMaskDistortion: function() {
                    var filter, component, data, url;

                    component = this._findComponent(
                        this.componentName
                    );

                    if (component) {
                        filter = this._findFilter(
                            component,
                            'DistortPerspective'
                        );
                    } else {
                        component = {
                            'name': this.componentName,
                            'description': this.componentDescription
                        }
                    }

                    component.groups = [];
                    array.forEach(
                        this.groupSelectNode.options,
                        function(option) {
                            if (option.selected) {
                                component.groups.push({'id': option.value});
                            }
                        }
                    );


                    if (this.componentName == 'screen') {
                        var options = {
                            args: [],
                            width: this.sourceImageNode.getAttr('width'),
                            height: this.sourceImageNode.getAttr('height')
                        };

                        var points = this.maskNode.getPoints();
                        array.forEach(points, function(point) {
                            options.args.push(point.x);
                            options.args.push(point.y);
                        });

                        if (filter) {
                            filter.options = options;
                        } else {
                            filter = {
                                'options': options,
                                'type': 'DistortPerspective'
                            };
                        }

                        filter.sequence = 0;
                        component.filters = [filter];
                    }

                    component['asset_id'] =
                        this.sourceImageSelector.selectedItem.id;

                    data = json.stringify(component);
                    if (component.url) {
                        url = component.url;
                    } else {
                        url = this.baseUrl + 'api/components/';
                    }

                    var xhrArgs = {
                        'url': url,
                        'handleAs': 'json',
                        'postData': data,
                        'load': function(data) {
                            //console.log(data);
                        },
                        'error': function() {
                            console.log('error');
                        }
                    };

                    dojo.xhrPost(xhrArgs);

                    return;
                },

                _changeComponentName: function() {
                    this.componentName = this.componentNameNode.value;
                    var component = this._findComponent(this.componentName);
                    if (component) {
                        this._setupGroups(component);
                    }
                }
            }
        );
    }
);

