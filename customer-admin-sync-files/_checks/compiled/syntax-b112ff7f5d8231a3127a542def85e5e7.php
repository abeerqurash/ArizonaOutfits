<?php $__env->startSection('title','Edit Email Template'); ?>
<?php $__env->startSection('page-heading','Edit Email Template'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1000px;margin:auto;background:white;padding:24px;border:1px solid #e2e8f0;border-radius:16px">
<a href="<?php echo e(route('admin.email-templates.index')); ?>">Back to email templates</a>
<h2><?php echo e($emailTemplate->name); ?></h2>
<?php if(session('success')): ?><p role="status"><?php echo e(session('success')); ?></p><?php endif; ?>
<?php if($errors->any()): ?><ul role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?>
<form method="POST" action="<?php echo e(route('admin.email-templates.update',$emailTemplate)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<label for="subject">Subject</label><input id="subject" name="subject" maxlength="255" required value="<?php echo e(old('subject',$emailTemplate->subject)); ?>" style="display:block;width:100%;padding:12px;margin:8px 0 20px;box-sizing:border-box">
<label for="body">Email body (HTML)</label><textarea id="body" name="body" rows="20" required maxlength="50000" style="display:block;width:100%;padding:12px;box-sizing:border-box;font-family:monospace"><?php echo e(old('body',$emailTemplate->body)); ?></textarea>
<p>Available variables: <?php $__currentLoopData = $emailTemplate->available_variables ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><code><?php echo e('{'.'{'.$variable.'}'.'}'); ?></code> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></p>
<p>Basic HTML is supported. Unsafe tags and attributes are removed when rendering.</p>
<input type="hidden" name="is_enabled" value="0"><label><input type="checkbox" name="is_enabled" value="1" <?php if(old('is_enabled',$emailTemplate->is_enabled)): echo 'checked'; endif; ?>> Use this custom template (otherwise use the built-in email)</label>
<p><button type="submit">Save template</button> <a href="<?php echo e(route('admin.email-templates.preview',$emailTemplate)); ?>" target="_blank" rel="noopener">Preview saved template</a></p>
</form>
<hr><h3>Send a test email</h3><form method="POST" action="<?php echo e(route('admin.email-templates.test',$emailTemplate)); ?>"><?php echo csrf_field(); ?>
<label for="test-email">Recipient email</label><input type="email" name="email" id="test-email" required maxlength="255"><button type="submit">Send test email</button>
</form></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>