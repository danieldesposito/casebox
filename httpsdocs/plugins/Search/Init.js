Ext.namespace('CB.plugins.Search');

CB.plugins.Search.init = function(){
    App.on('browserinit', function(c){
        if(!c.isXType(CB.browser.ViewContainer, true)) {
            return;
        }
        /* check if we have search templates */
        if(CB.DB.templates.query('type', 'search').getCount() > 0) {
            var p = Ext.apply([], Ext.value(c.plugins, []));
            p.push({ptype: 'CBPluginsSearchButton'});
            c.plugins = p;
        }
    });
};

Ext.onReady(CB.plugins.Search.init);
