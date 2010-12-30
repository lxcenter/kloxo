/*
 * Ext JS Library 1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

var TabsExample = {
    init : function(){
        // basic tabs 1, built from existing content
        var tabs = new Ext.TabPanel('tabs1');
        tabs.addTab('script', "Vista Style");
        tabs.addTab('markup', "Tree View");
        tabs.activate('markup');
    
        // second tabs built from JS
    }
}
Ext.EventManager.onDocumentReady(TabsExample.init, TabsExample, true);
