<?php echo $this->Session->flash(); ?>

<?php echo $this->Form->create('User', array('action' => 'confirm')); ?>
<fieldset>
<legend><?php echo __('Account Confirmation'); ?></legend>
<p><?php echo __('Please insert your confirmation key to finalize the account creation.'); ?></p>
<?php
  echo $this->Form->input('User.key', array('label' => __('Key')));
?>
</fieldset>
<?php echo $this->Form->end(__('Confirm')); ?>

