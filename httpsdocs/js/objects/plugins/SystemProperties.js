Ext.namespace('CB.objects.plugins');

CB.objects.plugins.SystemProperties = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){

        var tpl = new Ext.XTemplate(
            '<table class="item-props">'
            ,'<tbody><tr><td class="k">Id</td><td>{id}</td></tr>'
            ,'<tr><td class="k">'+L.Path+'</td><td><a class="click path">{path}</a></td></tr>'
            ,'<tr><td class="k">'+L.Template+'</td><td>{template_name} <span class="dttm">(id: {template_id})</span></td></tr>'
            ,'<tr><td class="k">'+L.Created+'</td><td>{cid_text}<br><span class="dttm" title="{[ displayDateTime(values.cdate) ]}">{cdate_text}</span></td></tr>'
            ,'<tr><td class="k">'+L.Modified+'</td><td>{uid_text}<br><span class="dttm" title="{[ displayDateTime(values.udate) ]}">{udate_text}</span></td></tr>'
            ,'</tbody></table>'
        );

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,data: []
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }
        });

        Ext.apply(this, {
            title: L.Properties
            ,items: this.dataView
        });
        CB.objects.plugins.SystemProperties.superclass.initComponent.apply(this, arguments);

    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }
        if(this.rendered) {
            this.dataView.update(r.data);
        } else {
            this.dataView.data = r.data;
        }
    }

    ,onItemClick: function ( dv, index, el, e) {
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasClass('path')) {
            App.openPath(this.params.path);
        }

    }

    ,getContainerToolbarItems: function() {
        rez = {
            tbar: {}
            ,menu: {}
        };

        if(this.params) {
            if(CB.DB.templates.getType(this.params.template_id) == 'file') {
                rez.menu['webdavlink']  = {};
            }
        }

        return rez;
    }
});

Ext.reg('CBObjectsPluginsSystemProperties', CB.objects.plugins.SystemProperties);
