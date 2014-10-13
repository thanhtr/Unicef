define(
    'aino/customize/Asset',
    [
        'dojo/_base/declare',
        'aino/customize/_Base',
        'aino/PreviewList'
    ],

    function(declare, Base, PreviewList) {

        return declare(
            'aino.customize.Asset',
            [ Base, PreviewList],
            {
                apiUrl: 'api/assets?groups='
            }
        );
    }
);
