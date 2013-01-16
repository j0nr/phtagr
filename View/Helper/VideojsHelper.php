<?php
/**
 * PHP versions 5
 *
 * phTagr : Tag, Browse, and Share Your Photos.
 * Copyright 2006-2013, Sebastian Felis (sebastian@phtagr.org)
 *
 * Licensed under The GPL-2.0 License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2006-2013, Sebastian Felis (sebastian@phtagr.org)
 * @link          http://www.phtagr.org phTagr
 * @package       Phtagr
 * @since         phTagr 2.2b3
 * @license       GPL-2.0 (http://www.opensource.org/licenses/GPL-2.0)
 */

class VideojsHelper extends AppHelper
{
  var $helpers = array('Html', 'ImageData');

  /**
   * Loads the required script of flowplayer for scripts_for_layout variable
   */
  function importPlayer() {
    $this->Html->script('http://vjs.zencdn.net/c/video.js', array('inline' => false));
    return '';
  }
  
  /**
   * Create array of alternative video formats
   */
  function getSources(&$media) {
    $extensions = array ('mp4', 'webm', 'flv');
    $id = $media['Media']['id'];

    $results = array();
    foreach($extensions as $ext) {
      $params = array('src' => Router::url("/media/video/$id/$id.$ext",true), 'type' => "video/$ext");
      $results[] = $this->Html->tag('source', '', $params);
    }
    return $results;
  }

  /**
   * Creates the link container for Videojs
   */
  function getVideojsParams(&$media) {
    list($width, $height) = $this->ImageData->getimagesize($this->request->data, OUTPUT_SIZE_VIDEO);
    $height += 24;
    $id = $media['Media']['id'];
    return array(
      'width' => $width,
      'height' => $height,
      'preload' => 'auto',
      'controls' => 'controls',
      'class' => 'video-js vjs-default-skin',
      'data-setup' => '{}',
      'poster' => Router::url("/media/preview/$id/$id.jpg", true));
  }

  /**
   * Creates the start script for the flowplayer
   *
  function player($media) {
    $id = $media['Media']['id'];
    return $this->Html->scriptBlock("flowplayer('player', '".Router::url("/flowplayer/flowplayer-3.1.5.swf", true)."', {
playlist: [
  {
    url: '".Router::url("/media/preview/$id/$id.jpg", true)."',
    scaling: 'fit'
  },
  {
    url: '".Router::url("/media/video/$id/$id.flv", true)."',
    autoPlay: false,
    autoBuffering: false
  }
]});\n");
  }
  */

  function video($media) {
    $out = $this->importPlayer();
    $params = $this->getVideojsParams($media);
    $sources = $this->getSources($media);
    
    $out .= $this->Html->tag('video', join("\n", $sources), $params);
    return $out;
  }
}
?>
