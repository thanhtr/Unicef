define(
    'aino/customize/Ugc',
    [
        'dojo/_base/declare',
        'aino/customize/_Base',
        'aino/UgcPreviewList'
    ],

    function(declare, Base, UgcPreviewList) {

        return declare(
            'aino.customize.Ugc',
            [Base, UgcPreviewList],
            {
                apiUrl: 'api/ugc?groups='
            }
        );
    }
);
