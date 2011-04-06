<?php if(count($playlist)>0){ $s = Shozu::getInstance();?>
<form action="" method="post">
    <fieldset>
        <legend>Playlist: <?php echo $status['playlistlength'] . ' songs';?></legend>
        <p>
            <a href="<?php echo $s->url('choonz/index/clearplaylist');?>" title="clear playlist"><img src="<?php echo $s->base_url;?>silkicons/delete.jpg" alt="clear playlist"/></a>
            <a href="<?php echo $s->url('choonz/index/clearplaylist');?>" title="clear playlist"> clear playlist</a>
        </p>
        <ul style="max-height:200px;overflow:auto;">
        <?php foreach($playlist as $song){?>
            <li<?php if(isset($status['songid']) && $song['Id'] == $status['songid']){echo ' class="currentsong"';}?>>
                <?php if(isset($status['songid']) && $song['Id'] == $status['songid']){echo '<a name="song' . $song['Id'] . '"></a>';}?>
                <strong><?php echo isset($song['Artist']) ? $this->escape($song['Artist']) : 'unknown artist';?></strong>
                <br/><em><?php echo isset($song['Title']) ? $this->escape($song['Title']) : 'unknown title';?></em>
                <?php echo isset($song['Album']) ? '<br/><span class="album">[' . $this->escape($song['Album']) . ']</span>' : '';?></li>
        <?php }?>
        </ul>
    </fieldset>
</form>
<?php } ?>