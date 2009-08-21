<?php

App::import('Component', 'Logger');
App::import('File', 'Search', array('file' => APP.'search.php'));

class SearchTest extends CakeTestCase {
  var $Search;

  function _init() {
    $this->Search = new Search();

    $this->Search->clear();
  }

  function testParam() {
    $this->_init();
    
    // get not existing values
    $result = $this->Search->getParam('notExists'); 
    $this->assertEqual($result, null);
    
    // get default value of not existing value 
    $result = $this->Search->getParam('notExists', 'default'); 
    $this->assertEqual($result, 'default');

    // set and get
    $this->Search->setParam('page', 2);
    $result = $this->Search->getParam('page'); 
    $this->assertEqual($result, 2);

    // delete
    $this->Search->delParam('page');
    $result = $this->Search->getParam('page'); 
    $this->assertEqual($result, null);
    
    // add single value
    $this->Search->addParam('tag', 'tag1');
    $result = $this->Search->getParam('tag'); 
    $this->assertEqual($result, null);
    $result = $this->Search->getParam('tags'); 
    $this->assertEqual($result, array('tag1'));

    // add array
    $this->Search->addParam('tag', array('tag2', 'tag3'));
    $result = $this->Search->getParam('tags'); 
    $this->assertEqual($result, array('tag1', 'tag2', 'tag3'));

    // delete singel value from array
    $this->Search->delParam('tags', 'tag2');
    $result = $this->Search->getParam('tags'); 
    $this->assertEqual($result, array('tag1', 2 => 'tag3'));

    // delete array
    $this->Search->delParam('tags');
    $result = $this->Search->getParam('tags'); 
    $this->assertEqual($result, null);
  }

  function testSingle() {
    $this->_init();

    // set and delete
    $this->Search->setPage(1);
    $result = $this->Search->getPage();
    $this->assertEqual($result, 1);

    $this->Search->delPage();
    $result = $this->Search->getPage();
    $this->assertEqual($result, null);

    // get default value
    $result = $this->Search->getPage(2);
    $this->assertEqual($result, 2);
  }

  function testMultiple() {
    $this->_init();

    // add multiple tags
    
    $this->Search->addTag('tag1');
    $result = $this->Search->getTags();
    $this->assertEqual($result, array('tag1'));    

    $this->Search->addTag('tag2');
    $result = $this->Search->getTags();
    $this->assertEqual($result, array('tag1', 'tag2'));    

    $this->Search->delTag('tag1');
    $result = $this->Search->getTags();
    $this->assertEqual($result, array(1 => 'tag2'));    

    // delete non existsing value
    $this->Search->delTag('tag3');
    $result = $this->Search->getTags();
    $this->assertEqual($result, array(1 => 'tag2'));    

    $this->Search->delTag('tag2');
    $result = $this->Search->getTags();
    $this->assertEqual($result, null);    

    // add and delete multiple
    $this->Search->addTag(array('-tag1', 'tag2', 'tag3'));
    $result = $this->Search->getTags();
    $this->assertEqual($result, array('-tag1', 'tag2', 'tag3'));    

    $this->Search->delTag(array('-tag1', 'tag2'));
    $result = $this->Search->getTags();
    $this->assertEqual($result, array(2 => 'tag3'));    
  }
}
?>
