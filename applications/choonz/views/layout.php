<?php $s = Shozu::getInstance(); if(!isset($field)){$field = 'any';}?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>RMPDC</title>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <link href="<?php echo $s->base_url;?>style.css" media="screen" rel="stylesheet" type="text/css" >
    </head>
    <body>
        <?php echo $this->action('choonz', 'index', 'controls');?>
        <?php echo $this->action('choonz', 'index', 'playing');?>
        <form method="post" action="<?php echo $s->url('choonz/index/search');?>">
            <fieldset>
                <legend>Search</legend>
                <p>
                <input type="text" name="term" value="<?php echo isset($term) ? $this->escape($term): '';?>"/>
                <!--
                <select name="field">
                    <option value="any"<?php if($field=='any'){echo ' selected="selected"';}?>>any</option>
                    <option value="artist"<?php if($field=='artist'){echo ' selected="selected"';}?>>artist</option>
                    <option value="title"<?php if($field=='title'){echo ' selected="selected"';}?>>title</option>
                    <option value="album"<?php if($field=='album'){echo ' selected="selected"';}?>>album</option>
                    <option value="genre"<?php if($field=='genre'){echo ' selected="selected"';}?>>genre</option>
                </select>
                -->
                <input type="submit" value="search"/>
                </p>
            </fieldset>
        </form>
        <?php echo $content_for_layout;?>
        <?php echo $this->action('choonz', 'index', 'playlist');?>
    </body>
</html>