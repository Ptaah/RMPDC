<?php $s = Shozu::getInstance();?>
<form method="post" action="<?php echo $s->url('choonz/index/addtoplaylist');?>">
<fieldset>
    <legend><?php echo count($results);?> results for "<?php echo $this->escape($term);?>"</legend>
    <?php if(count($results)>0){ ?>
    <input type="submit" value="send to playlist"/>
    <ul>
        <?php foreach($results as $key => $song){?>
        <li>
            <input type="checkbox" checked="checked" name="files[]" id="result-<?php echo $key;?>" value="<?php echo $this->escape($song['file']);?>"/>
            <label for="result-<?php echo $key;?>">
            <strong><?php echo isset($song['Artist']) ? $this->escape(trim($song['Artist'])) : 'unknown artist';?></strong>
            <br/><em><?php echo isset($song['Title']) ? $this->escape(trim($song['Title'])) : 'unknown title';?></em>
            <label>
            <?php echo isset($song['Album']) ? '<br/><span class="album">[' . $this->escape(trim($song['Album'])) . ']</span>' : '';?>
        </li>
        <?php }?>
    </ul>
    <?php } ?>
</fieldset>
</form>