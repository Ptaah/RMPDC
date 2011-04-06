<?php if($song){$s = Shozu::getInstance();?>
<fieldset>
    <legend><img src="<?php echo $s->base_url;?>silkicons/music.jpg"/>&nbsp;<strong><?php echo isset($song['Title']) ? $this->escape($song['Title']) : 'unknown title';?></strong></legend>
    <p>
        <img src="<?php echo $s->base_url;?>silkicons/user.jpg"/>&nbsp;<?php echo isset($song['Artist']) ? $this->escape($song['Artist']) : 'unknown artist';?>
        <?php echo isset($song['Album']) ? '<br/><img src="' . $s->base_url . 'silkicons/cd.jpg"/>&nbsp;<span class="album">[ ' . $this->escape($song['Album']) . ']</span>' : '';?>
        <br/>
        <a href="<?php echo '#song' . $song['Id'];?>" title="see in playlist"><img src="<?php echo $s->base_url;?>silkicons/application_view_list.jpg" alt="see in playlist"/></a>
        
        <span class="time"><?php echo \choonz\time2minutes($status['time']) . '/' . \choonz\time2minutes($song['Time']);?></span>
    </p>
</fieldset>
<?php }?>