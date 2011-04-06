<?php
foreach($results as $r)
{    
    echo '<treeitem>
            <treerow>
                <treecell label="' . (isset($r['Artist']) ? str_replace('','',htmlspecialchars($r['Artist'], ENT_COMPAT, 'UTF-8')) : '') .' - ' . (isset($r['Title']) ? str_replace('','',htmlspecialchars($r['Title'], ENT_COMPAT, 'UTF-8')) : '') .' ' . (isset($r['Album']) ? '[' . str_replace('','',htmlspecialchars($r['Album'], ENT_COMPAT, 'UTF-8')) . ']' : '') . '"/>
                <treecell label="' . (isset($r['Time']) ? choonz\time2minutes($r['Time']) : '') .'"/>
                </treerow>
                </treeitem>';
    
}