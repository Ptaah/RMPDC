<?php $s = Shozu::getInstance();?>
<form method="post" action="<?php echo $s->url('choonz/index/setvolume');?>">
<fieldset>
    <!--<legend>Controls</legend>-->
    <a href="<?php echo $s->url('choonz/index/previous');?>" title="previous song"><img src="<?php echo $s->base_url;?>silkicons/control_start_blue.jpg" alt="previous"/></a>
    <a href="<?php echo $s->url('choonz/index/toggleplay');?>" title="play/pause"><img src="<?php echo $s->base_url;?>silkicons/control_<?php echo $status['state'] == 'play' ? 'pause' : 'play';?>_blue.jpg" alt="play/pause"/></a>
    <a href="<?php echo $s->url('choonz/index/next');?>" title="next song"><img src="<?php echo $s->base_url;?>silkicons/control_end_blue.jpg" alt="next"/></a>
    <label for="volume">Vol</label><input id="volume" name="volume" type="text" size="3" maxlength="3" value="<?php echo $status['volume']?>"/>
    <input type="submit" value="set"/>
</fieldset>
</form>