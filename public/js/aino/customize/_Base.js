define(
    'aino/customize/_Base',
    [
        'dojo/_base/declare',
        'aino/PreviewList'
    ],

    function(declare, PreviewList) {

        return declare(
            'aino.customize._Base',
            [PreviewList],
            {
                groups: '',

                baseUrl: '',
                
                apiUrl: 'api/assets?groups=',
                
                updateGroups: function(groups) {
                    if (groups) {
                        this.groups = groups;
                    }
                    
                    this.setHref(this.baseUrl + this.apiUrl + this.groups);
                    return this;
                },

                postCreate: function() {
                    this.updateGroups();
                    this.inherited(arguments);
                }
            }
        );
    }
);
