<h2><?php
  if (!$this->Search->getUser() || $this->Search->getUser() != $this->Session->read('User.username')) {
    echo __("%s by %s", h($media['Media']['name']), $this->Html->link($media['User']['username'], "/explorer/user/".$media['User']['username']));
  } else {
    echo h($media['Media']['name']);
  }
?></h2>
<?php
  $size = $this->ImageData->getimagesize($media, OUTPUT_SIZE_THUMB);
  $imageCrumbs = $this->Breadcrumb->replace($crumbs, 'page', $this->Search->getPage());
  $imageCrumbs = $this->Breadcrumb->replace($imageCrumbs, 'pos', ($pos + $index));
  if ($this->Search->getShow(12) != 12) {
    $imageCrumbs = $this->Breadcrumb->replace($imageCrumbs, 'show', $this->Search->getShow());
  }

  // image centrering from http://www.brunildo.org/test/img_center.html
  if($media['Media']['type'] == "2"){
    echo '<div class="preview video"><span></span>';
    echo $this->Html->tag('a',
      $this->Html->tag('img', false, array(
        'src' => Router::url("/media/thumb/".$media['Media']['id']),
        'width' => $size[0], 'height' => $size[1],
        'alt' => $media['Media']['name'])),
      array('href' => Router::url("/images/view/".$media['Media']['id'].'/'.$this->Breadcrumb->params($imageCrumbs)),'class' => "play"));
    echo $this->Html->tag('a',
      $this->Html->tag('img', false, array(
        'src' => Router::url("/webroot/img/play.icon.png"),
        'alt' => "Video Icon", 'class' => 'play')),
      array('href' => Router::url("/images/view/".$media['Media']['id'].'/'.$this->Breadcrumb->params($imageCrumbs)),'class' => "play"));
        /*echo $this->Html->tag('img', false, array(
          'src' => Router::url("/webroot/img/play.icon.png"),
          'alt' => "Video Icon", 'class' => 'play'));*/
    } else { 
      echo '<div class="preview photo"><span></span>';
      echo $this->Html->tag('a',
        $this->Html->tag('img', false, array(
          'src' => Router::url("/media/thumb/".$media['Media']['id']),
          'width' => $size[0], 'height' => $size[1],
          'alt' => $media['Media']['name'])),
        array('href' => Router::url("/images/view/".$media['Media']['id'].'/'.$this->Breadcrumb->params($imageCrumbs))));
      }
  echo "</div>";
?>

<div class="actions" id="action-<?php echo $media['Media']['id']; ?>">
  <?php echo $this->element('Explorer/actions', array('media' => $media)); ?>
</div>
