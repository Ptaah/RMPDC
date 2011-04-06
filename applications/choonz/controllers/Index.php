<?php
namespace choonz\controllers;
class Index extends \Controller
{
    public function controlsAction()
    {
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $this->display('controls', array('status' => $mpd->getStatus()));
    }
    
    public function setvolumeAction()
    {
        $volume = (int)$this->getParam('volume', 100);
        if($volume > 100)
        {
            $volume = 100;
        }
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $mpd->setVolume($volume);
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }
    
    public function playingAction()
    {
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $this->display('playing', array('song' => $mpd->getCurrentSong(), 'status' => $mpd->getStatus()));
    }

    public function playlistAction()
    {
        $mpd = \Net_MPD::factory('Playlist', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $this->display('playlist', array('playlist' => $mpd->getPlaylistInfo(), 'status' => $mpd->getStatus()));
    }

    public function indexAction()
    {
        $mpd = \Net_MPD::factory('Playlist', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $this->setLayout('layout');
        $this->display('mobile');
    }

    public function previousAction()
    {
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $mpd->previousSong();
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }

    public function nextAction()
    {
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $mpd->nextSong();
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }

    public function toggleplayAction()
    {
        $mpd = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $mpd->pause();
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }
    
    public function clearplaylistAction()
    {
        $mpd = \Net_MPD::factory('Playlist', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $mpd->clear();
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }
    
    public function addtoplaylistAction()
    {
        $mpd = \Net_MPD::factory('Playlist', \choonz\MPD_HOST, \choonz\MPD_PORT);
        $files = $this->getParam('files', array());
        foreach($files as $file)
        {
            $mpd->addSong($file);
        }
        $status = $mpd->getStatus();
        if($status['state'] != 'play')
        {
            $playback = \Net_MPD::factory('Playback', \choonz\MPD_HOST, \choonz\MPD_PORT);
            $playback->play();
        }
        header('Location: ' . \Shozu::getInstance()->base_url);
        die;
    }
    
    public function searchAction()
    {
        $term = trim($this->getParam('term', ''));
        if(empty($term))
        {
            header('Location: ' . \Shozu::getInstance()->base_url);
            die;
        }
        $field = trim($this->getParam('field','any'));
        $cache = \Cache::getInstance('choonz');
        if(($results = $cache->fetch('mpd_search_results-' . $field . ':' . $term)) === false)
        {
            $db = \Net_MPD::factory('Database', \choonz\MPD_HOST, \choonz\MPD_PORT);
            $results = $db->find(array($field => $term));
            $cache->store('mpd_search_results-' . $field . ':' . $term, $results, 1800);
        }
        $this->setLayout('layout');
        $this->assignToLayout('term', $term);
        $this->assignToLayout('field', $field);
        $this->display('results', array('results' => $results, 'term' => $term));
    }
}