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

class VideojsComponent extends Component {

  var $controller = null;
  var $components = array('FileCache', 'Command');
  var $config = array(
    'size' => OUTPUT_SIZE_VIDEO,
    'bitrate' => OUTPUT_BITRATE_VIDEO,
    'framerate' => 30
    );
  var $_semaphoreId = false;

  public function initialize(Controller $controller) {
    $this->controller = $controller;
    if (function_exists('sem_get')) {
      $this->_semaphoreId = sem_get(4712);
    }
  }

  public function _scaleSize($media, $size) {
    $width = $media['Media']['width'];
    $height = $media['Media']['height'];
    if ($width > $size && $width > $height) {
      $height = intval($size * $height / $width);
      $width = $size;
    } elseif ($height > $size) {
      $width = intval($size * $width / $height);
      $height = $size;
    }
    // fix for ffmpeg: even frame size
    $width += ($width & 1);
    $height += ($height & 1);
    return $width . 'x' . $height;
  }
  /** Evaluates if the media is a MP4 H264 movie
    @param media Current media
    @param file Video file of the media
    @return True if current media is a mp4/h.264 movie */
  public function isValidMP4($media, $file) {
    $bin = $this->controller->getOption('bin.ffmpeg');
    if (!$bin) {
      Logger::warn("Path to external program ffmpeg is missing");
      return false;
    }
    exec('ffmpeg -i '.$file.' 2>&1', $results);
    $results = implode(',',$results);
    if(strpos($results, 'h264')) {
        $h264 = 'true';
    }
    if ($this->controller->MyFile->getExtension($file) == 'mp4' &&
      $media['Media']['width'] <= $this->config['size'] &&
      $media['Media']['height'] <= $this->config['size'] &&
      $h264 == 'true'
      ) {
      return true;
    }
    return false;
  }
  
  /** Evaluates if the media is a flash movie
    @param media Current media
    @param file Video file of the media
    @return True if current media is a flash movie */
  public function isValidFlash($media, $file) {
    if ($this->controller->MyFile->getExtension($file) == 'flv' &&
      $media['Media']['width'] <= $this->config['size'] &&
      $media['Media']['height'] <= $this->config['size']) {
      return true;
    }
    return false;
  }

  public function create($media, $config = array()) {
    $config = am($this->config, $config);
    if (!$this->controller->Media->isType($media, MEDIA_TYPE_VIDEO)) {
      Logger::err("Media {$media['Media']['id']} is not a video");
      return false;
    }
    $video = $this->controller->Media->getFile($media, FILE_TYPE_VIDEO);
    if (!$video) {
      Logger::err("Could not find video for media {$media['Media']['id']}");
      return false;
    }

    $src = $this->controller->MyFile->getFilename($video);
    if ($this->isValidMP4($media, $video)) {
      Logger::verbose("Source video $src is MP4, creating WEBM and FLV.");
      //TODO If video is valid MP4, need to go ahead and make webm and flv version
      return $src;
    }
   /* 
    $src = $this->controller->MyFile->getFilename($video);
    if ($this->isValidWEBM($media, $video)) {
      Logger::verbose("Source video $src is webm, creating MP4 and FLV.");
      //TODO If video is valid MP4, need to go ahead and make webm and flv version
      return $src;
    }
    
    $src = $this->controller->MyFile->getFilename($video);
    if ($this->isValidFlash($media, $video)) {
      Logger::verbose("Use media's flash video as source: $src");
      //TODO If video is valid FLV, need to go ahead and make webm and mp4 version
      return $src;
    }
*/
    $videojsFilename = $this->FileCache->getFilePath($media, 'mp4movie', 'mp4');
    if (!$videojsFilename) {
      Logger::fatal("Precondition of cache directory failed: $cacheDir");
      return false;
    }

    if (!file_exists($videojsFilename) && !$this->convertVideo($media, $src, $videojsFilename, $config)) {
      Logger::err("Could not create preview file {$videojsFilename}");
      return false;
    }

    return $videojsFilename;
  }

  public function convertVideo($media, $src, $dst, $config = array()) {
    $config = am($this->config, $config);
    $bin = $this->controller->getOption('bin.ffmpeg');
    if (!$bin) {
      Logger::warn("Path to external program ffmpeg is missing");
      return false;
    }
    $args = array(
      '-i' => $src,
      '-s' => $this->_scaleSize($media, $config['size']),
      '-r' => $config['framerate'],
      '-b' => $config['bitrate'],
      '-ar' => 22050,
      '-ab' => 48,
      '-y', $dst);
    if ($this->_semaphoreId) {
      sem_acquire($this->_semaphoreId);
    }
    $result = $this->Command->run($bin, $args);
    if ($this->_semaphoreId) {
      sem_release($this->_semaphoreId);
    }
    if ($result != 0) {
      Logger::err("Command '$bin' returned unexcpected with $result");
      @unlink($dst);
      return false;
    }
    Logger::info("Created flash video '$dst' of '$src'");
    $this->_addCuePoints($dst);
    return true;
  }

  public function _addCuePoints($filename) {
    $bin = $this->controller->getOption('bin.flvtool2');
    if (!$bin) {
      return;
    }
    if ($this->Command->run($bin, array('-U' => $filename))) {
      Logger::err("Command '$bin' returned unexcpected $result");
      return false;
    }
    Logger::info("Updated flash video '$filename' with meta tags");
    return true;
  }
}

?>