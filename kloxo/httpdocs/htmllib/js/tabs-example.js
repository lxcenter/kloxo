/*
 * Ext JS Library 1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

var TabsExample = {
    init : function(){
        var tabs = new Ext.TabPanel('tabs1');
        tabs.addTab('script', "Skin");
        tabs.addTab('markup', "Tree");
		//
		// Show tree style as default
		// TODO: make it user selectable because some wants show the other tab as default.
        tabs.activate('markup');
    }
};
Ext.EventManager.onDocumentReady(TabsExample.init, TabsExample, true);
