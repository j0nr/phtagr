<?php echo $session->flash(); ?>

<?php echo $form->create('User', array('action' => 'login')); ?>
<fieldset>
<legend>Login</legend>
<?php
  echo $form->input('User.username');
  echo $form->input('User.password');
?>
</fieldset>
<?php echo $form->submit('Login'); ?>
</form>

<?php echo $html->link('Forgot your password', 'password'); ?>
<?php if ($register): ?>
 or create <?php echo $html->link('your account', 'register'); ?>
<?php endif; ?>
