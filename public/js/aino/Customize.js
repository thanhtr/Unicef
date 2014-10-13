define(
    'aino/Customize',
    [
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dijit/registry',
        'dojo/_base/array',
        'dojo/_base/event',
        'dojo/query',
        'dojo/dom-form',
        'dojo/dom-class',
        'dojo/io-query',
        'dojo/dom-construct',
        'dojo/_base/lang',
        'dojo/on',
        'dojo/text!aino/templates/Customize.html',
        'luxus/svg/Svg',
        'luxus/Error',
        'luxus/svg/Group',
        'luxus/svg/Image',
        'luxus/svg/Rect',
        'luxus/svg/Polygon',
        'aino/PreviewList',
        'aino/customize/Asset',
        'aino/customize/Ugc',
        'aino/customize/Text'

    ],

    function(
        declare,
        widget,
        templatedMixin,
        widgetsInTemplateMixin,
        registry,
        array,
        event,
        query,
        domForm,
        domClass,
        ioQuery,
        domConstruct,
        lang,
        on,
        template,
        Svg,
        Error) {

        return declare(
            'aino.Customize',
            [widget, templatedMixin, widgetsInTemplateMixin],
            {
                templateString: template,

                widgetsInTemplate: true,

                svgWidget: null,

                href: '',

                baseUrl: '/',

                previewFormat: 'svg',

                downloadFormats: ['png', 'jpg', 'pdf', 'svg'],

                _parameters: {},

                _tabedInputs: [],

                _hiddenInputs: {},

                _components: {},

                _filterDependencies: {},

                _loadingCounter: 0,

                _previewImage: null,

                _selectTab: function(e) {
                    event.stop(e);
                    var tabName = e.target.href.match(/#(.*)/)[1];
                    var parentLi = e.target.parentNode;

                    if(tabName) {
                        query('.tab-open').removeClass('tab-open');
                        query(
                            '.tab-content.' + tabName
                        ).addClass('tab-open');
                        query(
                            '.tab-active',
                            parentLi.parentNode
                        ).removeClass('tab-active');
                        query(parentLi).addClass('tab-active');
                    }
                    return false;
                },

                _createTabHandler: function(tabName, label) {
                    var self = this;
                    var li = domConstruct.create(
                        'li',
                        {
                            'class': 'parameter-tab'
                        },
                        this.tabHandles
                    );
                    domConstruct.create(
                        'a',
                        {
                            'href': '#tab-' + tabName,
                            'innerHTML': label,
                            'onclick': function(event) {
                                self._selectTab(event);
                                return false;
                            }
                        },
                        li
                    );
                },

                _updateParameters: function() {
                    this._removeAllInputs();
                    var template = this.templateSelector.selectedItem;

                    this._parameters = {};
                    this._components = {};
                    array.forEach(
                        template.parameters,
                        function(parameter) {
                            this._setupParameter(parameter);
                            this._parameters[parameter.id] = parameter;
                        },
                        this
                    );
                },

                _removeAllInputs: function() {
                    array.forEach(
                        this._tabedInputs,
                        function(widget) {
                            widget.destroyRecursive(false);
                        }
                    );

                    domConstruct.empty(this.hiddenFieldsNode);

                    this._tabedInputs = [];
                    this._hiddenInputs = {};
                    this._loadingCounter = 0;

                    query('.parameter-tab').orphan();
                },

                _createTabedWidget: function(type, parameter) {
                    var widgetClass, widget, properties;
                    var self = this;

                    widgetClass = lang.getObject(type);

                    properties = {
                        'parent': this,
                        'itemValueProp': 'id',
                        'baseUrl': this.baseUrl,
                        'name': parameter.id,
                        'groups': parameter.assetGroups,
                        'parameter': parameter,
                        'class': 'tab-content tab-' + parameter.id.replace(' ', '_')
                    }

                    widget = new widgetClass(properties);
                    widget.placeAt(this.tabContainer);

                    on(
                        widget,
                        'loading',
                        function() {
                            self._loadingCounter--;
                        }
                    );

                    on(
                        widget,
                        'ready',
                        function() {
                            widget.selectFirstItem();
                            self._inputWidgetReady();
                        }
                    );

                    on(
                        widget,
                        'changed',
                        function(e) {
                            self._showLoading();
                            var item = e.item;
                            if(item.components && item.components.length) {
                                item.components.forEach(
                                    function(component) {
                                        self._updateDependentInputs(
                                            e.name,
                                            component
                                        );
                                    }
                                );
                            } else {
                                self._resetDependentInputs(e.name);
                            }

                            self._updateDependentOptions(item.id, e.value);

                            self._update();
                        }
                    );

                    this._createTabHandler(
                        parameter.id,
                        parameter.label
                    );

                    this._linkRelations(parameter);

                    return widget;
                },

                _updateDependentInputs: function(masterInputName, component) {
                    var inputs = this._findDependentInputs(
                        masterInputName,
                        component.name
                    );

                    if (inputs.length) {
                        inputs.forEach(
                            function(input) {
                                if(component.groups.length) {
                                    var query = this._compileGroupsString(
                                        component
                                    );
                                    input.updateGroups(query).update();
                                } else {
                                    this._resetDependentInputs(masterInputName);
                                }
                            },
                            this
                        );
                    } else {
                        this._resetDependentInputs(masterInputName);
                    }
                },

                _resetDependentInputs: function(masterName) {
                    var componentName;
                    var components = this._components[masterName];

                    if (components) {
                        for(componentName in components) {
                            components[componentName].forEach(
                                function(inputName) {
                                    this._resetInput(inputName);
                                },
                                this
                            );
                        }

                        return;
                    }
                },

                _resetInput: function(inputName) {
                    var input = this._findInput(inputName);
                    var assetGroup = this._parameters[inputName].assetGroup;

                    if (input && assetGroup) {
                        input.updateGroups(assetGroup).update();
                    }
                },

                _findInput: function(inputName) {
                    var inputWidget;
                    this._tabedInputs.forEach(
                        function(input) {
                            if (input.name == inputName) {
                                inputWidget = input;
                            }
                        }
                    );
                    return inputWidget;
                },

                _compileGroupsString: function(component) {
                    var str = [];
                    component.groups.forEach(function(group) {
                        str.push(group.name);
                    });

                    return str.join(',');

                },

                _inputWidgetReady: function() {
                    this._loadingCounter++;
                    this._update();
                },

                _registerComponent: function(
                    masterInputName,
                    dependInputName,
                    componentName) {
                    if (!this._components[masterInputName]) {
                        this._components[masterInputName] = {};
                    }

                    if (!this._components[masterInputName][componentName]) {
                        this._components[masterInputName][componentName] = [];
                    }

                    this._components[masterInputName][componentName].push(
                        dependInputName
                    );

                    return this;
                },

                _registerFilterDependencies: function(
                    masterInputName,
                    dependInputName,
                    dependencyMap) {

                    if (!this._filterDependencies[masterInputName]) {
                        this._filterDependencies[masterInputName] = {};
                    }

                    this._filterDependencies[masterInputName][dependInputName] =
                        dependencyMap;

                    return this;
                },

                _updateDependentOptions: function(parentName, value) {
                    var deps, dependInputName, dependInput;
                    if (this._filterDependencies[parentName]) {
                        deps = this._filterDependencies[parentName];
                        for(dependInputName in deps) {
                            dependInput = this._findInput(dependInputName);
                            if (dependInput) {
                                dependInput.updateDependOptions(
                                    parentName,
                                    value
                                );
                            }
                        }
                    }

                    return this;
                },

                _findDependentInputs: function(masterInputName, componentName) {
                    var inputNames = [], inputs = [];
                    if (this._components[masterInputName] &&
                        this._components[masterInputName][componentName]) {
                        inputNames =
                            this._components[masterInputName][componentName];
                    }

                    if (inputNames.length) {
                        array.forEach(
                            this._tabedInputs,
                            function(input) {
                                if (inputNames.indexOf(input.name) > -1) {
                                    inputs.push(input);
                                }
                            }
                        );
                    }

                    return inputs;
                },

                _linkRelations: function(parameter) {
                    if (parameter.isComponent) {
                        var component = parameter.component;
                        this._registerComponent(
                            component.parentId,
                            parameter.id,
                            component.name
                        );
                    }

                    if (parameter.dependencies.length) {
                        parameter.dependencies.forEach(
                            function(dependency) {
                                this._registerFilterDependencies(
                                    dependency.id,
                                    parameter.id,
                                    dependency.dependencyMap
                                );
                            },
                            this
                        );
                    }

                    return this;
                },

                _getFormValuesJson: function() {
                    var values = [];
                    values = domForm.toJson(this.valuesForm);
                    return values;
                },

                _updateDownloadLinks: function(templateUrl, query) {
                    query.download = 1;
                    query.print = 1;

                    this.downloadFormats.forEach(
                        function(format) {
                            query.format = format;
                            if (this[format + 'DownloadLinkNode']) {
                                this[format + 'DownloadLinkNode'].href =
                                    templateUrl + '?' +
                                    ioQuery.objectToQuery(query);
                                domClass.remove(
                                    this[format + 'DownloadLinkNode'].parentNode,
                                    'aino-hidden'
                                );
                            }
                        },
                        this
                    );

                    query.print = 0;

                    return this;
                },

                _clearPreview: function() {
                    if (this._previewImage) {
                        if (this._previewImage['destroyRecursive']) {
                            this._previewImage.destroyRecursive(false);
                        } else {
                            domConstruct.destroy(this._previewImage);
                        }
                    }

                    return this;
                },

                _createImgPreview: function(url) {
                    var self = this;
                    var img = domConstruct.create(
                        'img',
                        {
                            'src': url,
                            'onload': function() {
                                self._hideLoading();
                            }
                        },
                        this.previewContainer
                    );

                    this._previewImage = img;

                    return this;
                },

                _showLoading: function() {
                    domClass.remove(this.contentFieldNode, 'aino-hidden');
                    domClass.add(this.contentFieldNode, 'loading');
                },

                _hideLoading: function() {
                    domClass.remove(this.contentFieldNode, 'loading');
                },

                _createSvgPreview: function(url) {
                    var self = this;
                    setTimeout(
                        function() {
                            self._previewImage = new Svg(
                                {
                                    'href': url,
                                    'onLoad': function() {
                                        self._connectSvgWidgets();
                                        self._hideLoading();
                                    }
                                }
                            );
                            self._previewImage.placeAt(self.previewContainer);
                        },
                        10
                    );
                },

                _connectSvgWidgets: function() {
                    var self = this;
                    array.forEach(registry.toArray(), function(widget) {
                        if(widget.type == 'g') {
                            widget.mouseUp = function() {
                                var transform = this.getAttr('transform');
                                var name = this.name;
                                if (name &&
                                    transform &&
                                    self._hiddenInputs[name]
                                ) {
                                    self._hiddenInputs[name].value = transform;
                                    self._update();
                                }
                            };
                        }
                    });
                },

                _updateImage: function(templateUrl, query) {
                    var url;
                    this._clearPreview();
                    query.format = this.previewFormat;

                    url = templateUrl + '?' + ioQuery.objectToQuery(query);

                    switch(query.format) {
                        case 'png':
                        case 'jpg':
                            this._createImgPreview(url);
                            break;
                        case 'svg':
                            this._createSvgPreview(url);
                            break;
                        default:
                            break;
                    }

                    return this;
                },

                _update: function() {
                    var templateUrl;
                    var query = {};

                    this._showLoading();

                    if (this._loadingCounter == this._tabedInputs.length) {
                        templateUrl = this.templateSelector.selectedItem.url;
                        query.parameters = this._getFormValuesJson();

                        this._updateImage(templateUrl, query)
                            ._updateDownloadLinks(templateUrl, query);
                    }
                    
                    return this;
                },

                _setupParameter: function(parameter) {
                    var type, input;
                    var self = this;
                    if(parameter.interactive) {
                        type = parameter.type.toLowerCase();
                        type = type.charAt(0).toUpperCase() + type.slice(1);
                        type = 'aino.customize.' + type;

                        if (lang.exists(type)) {
                            input = this._createTabedWidget(type, parameter);
                            this._tabedInputs.push(input);
                        } else {
                            input = domConstruct.create(
                                'input',
                                {
                                    'type': 'hidden',
                                    'name': parameter.id
                                },
                                this.hiddenFieldsNode
                            );

                            self._hiddenInputs[parameter.id] = input;
                        }
                    }
                    return this;
                }
            }
        );
    }
);
