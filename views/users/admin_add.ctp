<h1>Add new User</h1>

<?php echo $session->flash(); ?>

<?php echo $form->create('User', array('action' => 'add')); ?>
<fieldset><legend>Create new user</legend>
<?php
  echo $form->input('User.username');
  echo $form->input('User.email');
  echo $form->input('User.password');
  echo $form->input('User.confirm', array('type' => 'password'));
?>
</fieldset>
<?php echo $form->submit("Create"); ?>
</form>
