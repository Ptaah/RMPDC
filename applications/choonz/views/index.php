<?php $s = Shozu::getInstance();?><?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>
<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>
<?xml-stylesheet href="xbl/dragbox/dragbox.css" type="text/css"?>
<!--<?xml-stylesheet href="<?php echo $s->base_url;?>style.css" type="text/css"?>-->
<window
    id="choonz"
    title="choonz"
    orient="vertical"
    xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
    xmlns:html="http://www.w3.org/1999/xhtml">
<script src="jquery/jquery.js"/>
<script src="jquery/jquery.taconite.js"/>
<script>
<![CDATA[
function dropElement(event)
{
    var originalTarget = event.relatedNode; 
    var target = event.target;
    var sel = new Array(); // un array pour stoker toutes les index des lignes selectionnées
    var current = null;
    var start = new Object();
    var end = new Object();
    var numRanges = originalTarget.object.view.selection.getRangeCount();
    var value;
    var treeitems = new Array();
    for (var t=0; t < numRanges; t++)
    {
        originalTarget.object.view.selection.getRangeAt(t,start,end);
        for (var v=start.value; v <=end.value; v++)
        {
            treeitems.push(originalTarget.object.view.getItemAtIndex(v));
        }
    }
    var before = null;
    var row = target.object.treeBoxObject.getRowAt(event.clientX, event.clientY);
    if(row != -1) before = target.object.view.getItemAtIndex(row)
    for (var i=0; i<treeitems.length; i++)
    {
        target.object.body.insertBefore(treeitems[i], before);
    }
}

function doSearch()
{
    $.post('index.php?/choonz/index/search', {'term':$('#searchTerm').val(),'field':$('#searchField').val()});
}

var comp;
$(document).ready(function(){
    $('#searchTerm').bind('keydown',function(){
        clearTimeout(comp);
        comp=(setTimeout("doSearch()",700));
    });
    $('#searchField').bind('command',function(){
        doSearch();
    });
});
]]>
</script>
<!--
<toolbox>
    <menubar>
        <menu label="action">
            <menupopup>
                <menuitem label="test"/>
            </menupopup>
        </menu>
    </menubar>
</toolbox>
-->
<vbox id="middleContainer" flex="1">
    <hbox flex="1">
        <vbox flex="1">
            <hbox>
                <textbox flex="1" id="searchTerm"/>
                <menulist id="searchField">
                    <menupopup>
                        <menuitem label="Tout" value="any"/>
                        <menuitem label="Artiste" value="artist"/>
                        <menuitem label="Titre" value="title"/>
                        <menuitem label="Album" value="album"/>
                        <menuitem label="Genre" value="genre"/>
                    </menupopup>
                </menulist>
            </hbox>
            <dragbox flex="1" drag="playlist">
                    <tree flex="1" hidecolumnpicker="true" id="hits">
                        <treecols>
                            <treecol label="Results" flex="1"/>
                            <treecol label="Time"  />
                        </treecols>
                        <treechildren>
                        </treechildren>
                    </tree>
            </dragbox>
        </vbox>
        <splitter/>
        <vbox flex="2">
            <hbox>
                <textbox style="font-size:14px;" flex="1" readonly="false" value="Ron Trent - Altered States"/>
            </hbox>
            <hbox>
                <button label="pause"/>
                <scale flex="1"/>
            </hbox>
            
            <tabbox flex="1">
                <tabs>
                    <tab label="Playlist" flex="1"/>
                    <tab label="Song" flex="1"/>
                </tabs>
                <tabpanels flex="1">
                    <tabpanel orient="vertical">
                        <dragbox flex="1" drag="playlist" acceptdrag="playlist" ondrop="dropElement(event)">
                                <tree flex="1" hidecolumnpicker="true">
                                    <treecols>
                                        <treecol label="Track" flex="1"/>
                                        <treecol label="Time"  />
                                    </treecols>
                                    <treechildren>
                                        <treeitem>
                                            <treerow>
                                                <treecell label="Faze Action - Original Disco Motion"/>
                                                <treecell label="12:20"/>
                                            </treerow>
                                        </treeitem>
                                        <treeitem>
                                            <treerow>
                                                <treecell label="Ron Trent - Altered States"/>
                                                <treecell label="8:21"/>
                                            </treerow>
                                        </treeitem>
                                    </treechildren>
                                </tree>
                        </dragbox>
                    </tabpanel>
                    <tabpanel>
                    </tabpanel>
                </tabpanels>
            </tabbox>
        </vbox>
    </hbox>
</vbox>
<statusbar>
	<statusbarpanel id="mystatus" flex="1">
            <label>test</label>
            <spacer flex="1"/>
        </statusbarpanel>
</statusbar>
</window>