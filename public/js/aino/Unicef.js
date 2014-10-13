define(
    'aino/Unicef',
    [
        'dojo/_base/declare',
        'dijit/_Widget',
        'dijit/_TemplatedMixin',
        'dijit/_WidgetsInTemplateMixin',
        'dojo/io/iframe',
        'luxus/Error',
        'dojo/_base/event',
        'dojo/query',
        'dojo/on',
        'dojo/dom-form',
        'dojo/dom-style',
        'dojo/dom-geometry',
        'dojo/has',
        'dojo/text!aino/templates/Unicef.html',
        'dojo/NodeList-dom'
    ],

    function(
        declare,
        Widget,
        TemplatedMixin,
        WidgetsInTemplateMixin,
        iframe,
        Error,
        event,
        query,
        on,
        domForm,
        domStyle,
        domGeometry,
        has,
        template) {

        return declare(
            'aino.Unicef',
            [Widget, TemplatedMixin, WidgetsInTemplateMixin],
            {
                templateString: template,

                baseUrl: '',

                ugcUrl: '',

                bannerSize: '140x350',

                postCreate: function() {
                    this._initForm(this.eCardForm);
                    this._initForm(this.bannerForm);
                    this._update(this.eCardForm);
                    this._update(this.bannerForm);
                },

                _uploadFile: function() {
                    var self = this;
                    var xhrArgs = {
                        form: this.uploadFormNode,
                        handleAs: 'json',
                        load: function(data) {
                            if (data && !data['error']) {
                                self._addImage(data);
                            } else {
                                new Error(data);
                            }
                            self.uploadFormNode.reset();
                        }
                    };

                    iframe.send(xhrArgs);
                },

                _addImage: function(data) {
                    this.uploadedFilename.innerHTML = data.name;
                    this._setValue(
                        'parameters[logoImage]',
                        data.id,
                        this.eCardForm
                    );
                },

                _setValue: function(propName, value, form) {
                    var input = form.children.namedItem(propName);
                    if (input) {
                        input.value = value;
                        this._update(form);
                    }
                },

                _update: function(form) {
                    var values = '';
                    values = domForm.toQuery(form);
                    var preview = form.getAttribute('data-preview') + 'Preview';
                    var download = form.getAttribute('data-preview') + 'Download';

                    if (this[preview]) {
                        this[preview].setAttribute('src', form.action + '?' + values);
                    }

                    if (this[download]) {
                        this[download].setAttribute(
                            'href',
                            form.action + '?' + values + '&download=1&print=1'
                        );
                    }
                },

                _selectImage: function(a, li, form) {
                    var href = a.href.match(/#(.*)/)[1];
                    href = href.split(':');
                    var propName = href[0];
                    var imageId = href[1];

                    if(href.length) {
                        query('.selected', li.parentNode).removeClass('selected');
                        query(li).addClass('selected');
                        this._setValue(propName, imageId, form);
                    }
                },

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

                        if (window['_gaq']) {
                            _gaq.push([
                                '_trackEvent',
                                'Tab',
                                'Change', e.target.innerHTML
                            ]);
                        }
                    }
                    return false;
                },

                _initForm: function(form) {
                    var self = this;
                    query('.img-thumbs li', form).forEach(function(li) {
                        query('a', li).forEach(function(a) {
                            on(a, 'click', function(e) {
                                event.stop(e);
                                self._selectImage(a, li, form);
                            });
                        });
                    });

                    query('input, textarea', form).forEach(function(input) {
                        if(input.type == 'radio' && has('ie') <= 8) {
                            on(input, 'click', function(e) {
                                self._update(form);
                            });
                        } else {
                            on(input, 'change', function(e) {
                                self._update(form);
                            });
                        }
                    });
                },

                _chageSize: function(e) {
                    this.bannerForm.action = e.target.value;
                    if (e.target.value == this.baseUrl + '/api/templates/2') {
                        this.bannerSize = '140x350';
                    } else {
                        this.bannerSize = '468x60';
                    }

                    var self = this;
                    setTimeout(
                        function() {
                            self._update(self.bannerForm);
                        },
                        50
                    );
                },

                _previewLoad: function(e) {
                    var width = this.bannerPreview.width;
                    var rightMargin = (640-width)/2;

                    if (has("ie") <= 7) {
                        this.bannerPreview.style['margin-right'] =
                            rightMargin + 'px';
                    } else {
                        this.bannerPreview.setAttribute(
                            'style',
                            'margin-right:' + rightMargin + 'px'
                        );
                    }
                },

                _eCardDownloaded: function() {
                    if (window['_gaq']) {
                        _gaq.push(['_trackEvent', 'Download', 'eCard']);
                    }

                    return true;
                },

                _bannerDownloaded: function() {
                    if (window['_gaq']) {
                        _gaq.push([
                            '_trackEvent',
                            'Download',
                            'Banner_' + this.bannerSize
                        ]);
                    }

                    return true;
                },

                _posterDownloaded: function() {
                    if (window['_gaq']) {
                        _gaq.push(['_trackEvent', 'Download', 'Poster']);
                    }

                    return true;
                }
            }
        );
    }
);
