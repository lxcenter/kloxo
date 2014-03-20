/*
 * Ext JS Library 1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

/*
 *  Create the 2 tabs in the Left Panel side
 *
 */
var TabsExample = {
    init : function(){
        var tabs = new Ext.TabPanel('tabs1');
        tabs.addTab('script', "Normal");
        tabs.addTab('markup', "Tree");
        // Activate the focus on tree
        tabs.activate('markup');
        }
}
Ext.EventManager.onDocumentReady(TabsExample.init, TabsExample, true);
