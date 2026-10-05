
<?php $__env->startSection('title', 'Edit Email Template'); ?>
<?php $__env->startSection('page-heading', 'Edit Email Template'); ?>

<?php $__env->startSection('content'); ?>
<div class="ao-template-editor">
    <header class="ao-template-hero">
        <div>
            <a class="ao-template-back" href="<?php echo e(route('admin.email-templates.index')); ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Email templates</a>
            <span class="ao-template-eyebrow">Arizona Outfits Â· Store communication</span>
            <h2><?php echo e($emailTemplate->name); ?></h2>
            <p><?php echo e($emailTemplate->description ?: 'Customize the subject and message for this automated email.'); ?></p>
        </div>
        <span class="ao-template-state <?php echo e($emailTemplate->is_enabled ? 'enabled' : ''); ?>"><i class="fa-solid <?php echo e($emailTemplate->is_enabled ? 'fa-circle-check' : 'fa-circle-pause'); ?>" aria-hidden="true"></i><?php echo e($emailTemplate->is_enabled ? 'Custom template active' : 'Built-in email active'); ?></span>
    </header>

    <div class="ao-template-grid">
        <section class="ao-template-card">
            <header class="ao-template-card-heading"><span class="ao-template-icon"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></span><div><h3>Edit your message</h3><p>Keep your communication clear and consistent with your store.</p></div></header>
            <form method="POST" action="<?php echo e(route('admin.email-templates.update', $emailTemplate)); ?>" class="ao-template-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="ao-template-field">
                    <label for="subject">Email subject <span aria-hidden="true">*</span></label>
                    <input type="text" id="subject" name="subject" maxlength="255" required value="<?php echo e(old('subject', $emailTemplate->subject)); ?>" aria-describedby="ao-subject-help" <?php if($errors->has('subject')): ?> aria-invalid="true" <?php endif; ?>>
                    <p id="ao-subject-help" class="ao-template-help">The subject customers or administrators will see in their inbox.</p>
                    <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="ao-template-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="ao-template-field">
                    <div class="ao-template-label-row"><label for="body">Email body <span aria-hidden="true">*</span></label><span class="ao-template-format">HTML</span></div>
                    <textarea id="body" name="body" rows="18" required maxlength="50000" spellcheck="false" aria-describedby="ao-body-help" <?php if($errors->has('body')): ?> aria-invalid="true" <?php endif; ?>><?php echo e(old('body', $emailTemplate->body)); ?></textarea>
                    <p id="ao-body-help" class="ao-template-help">Basic HTML is supported. Unsafe tags and attributes are removed when rendering.</p>
                    <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="ao-template-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="ao-template-setting">
                    <input type="hidden" name="is_enabled" value="0">
                    <label class="ao-template-toggle" for="is_enabled"><input id="is_enabled" type="checkbox" name="is_enabled" value="1" <?php if(old('is_enabled', $emailTemplate->is_enabled)): echo 'checked'; endif; ?>><span class="ao-template-switch" aria-hidden="true"></span><span><strong>Use this custom template</strong><small>Turn off to use the built-in email instead.</small></span></label>
                </div>
                <footer class="ao-template-form-actions">
                    <button type="submit" class="ao-template-button primary"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Save template</button>
                    <a class="ao-template-button secondary" href="<?php echo e(route('admin.email-templates.preview', $emailTemplate)); ?>" target="_blank" rel="noopener"><i class="fa-regular fa-eye" aria-hidden="true"></i> Preview saved template</a>
                </footer>
            </form>
        </section>

        <aside class="ao-template-aside" aria-label="Template guidance and test email">
            <section class="ao-template-card">
                <header class="ao-template-card-heading"><span class="ao-template-icon violet"><i class="fa-solid fa-code" aria-hidden="true"></i></span><div><h3>Available variables</h3><p>Personalize each email automatically.</p></div></header>
                <div class="ao-template-card-body">
                    <p class="ao-template-aside-copy">Copy a variable exactly as shown into the subject or email body. It will be replaced with the corresponding information when sending.</p>
                    <div class="ao-template-variables">
                        <?php $__empty_1 = true; $__currentLoopData = $emailTemplate->available_variables ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <code><?php echo e('{'.'{'.$variable.'}'.'}'); ?></code>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="ao-template-help">This template has no available variables.</p>
                        <?php endif; ?>
                    </div>
                    <div class="ao-template-tip"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>Only the variables listed here are accepted for this template.</p></div>
                </div>
            </section>
            <section class="ao-template-card">
                <header class="ao-template-card-heading"><span class="ao-template-icon green"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i></span><div><h3>Send a test email</h3><p>Check the message in a real inbox.</p></div></header>
                <form method="POST" action="<?php echo e(route('admin.email-templates.test', $emailTemplate)); ?>" class="ao-template-test-form">
                    <?php echo csrf_field(); ?>
                    <p class="ao-template-aside-copy">Save your changes first. Preview and test email use the saved template with sample information.</p>
                    <div class="ao-template-field"><label for="test-email">Recipient email <span aria-hidden="true">*</span></label><input type="email" id="test-email" name="email" value="<?php echo e(old('email')); ?>" required maxlength="255" placeholder="you@example.com" autocomplete="email" <?php if($errors->has('email')): ?> aria-invalid="true" <?php endif; ?>><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="ao-template-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <button type="submit" class="ao-template-button primary"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i> Send test email</button>
                </form>
            </section>
            <div class="ao-template-updated"><i class="fa-regular fa-clock" aria-hidden="true"></i> Last saved <?php echo e($emailTemplate->updated_at?->diffForHumans() ?: 'not yet'); ?></div>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.ao-template-editor{display:grid;gap:20px;max-width:1440px;margin:0 auto;color:#172033}
.ao-template-hero{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:28px 30px;border:1px solid #243249;border-radius:20px;background:linear-gradient(120deg,#172033,#25324d);color:#fff;box-shadow:0 12px 30px rgba(15,23,42,.07)}
.ao-template-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:20px;color:#d8e3f5;font-size:12px;font-weight:700;text-decoration:none}.ao-template-back:hover{color:#fff}
.ao-template-eyebrow{display:block;color:#b8c8df;font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.ao-template-hero h2{margin:8px 0;font-size:clamp(23px,2.5vw,31px);line-height:1.2;overflow-wrap:anywhere}.ao-template-hero p{max-width:700px;margin:0;color:#d4deec;font-size:13px;line-height:1.7}
.ao-template-state{display:inline-flex;flex-shrink:0;align-items:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(255,255,255,.08);color:#e2e8f0;font-size:11px;font-weight:750}.ao-template-state.enabled{background:#d1fae5;border-color:#d1fae5;color:#065f46}
.ao-template-grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;align-items:start;gap:20px}.ao-template-aside{display:grid;gap:18px;min-width:0}.ao-template-card{min-width:0;overflow:hidden;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 7px 22px rgba(15,23,42,.035)}
.ao-template-card-heading{display:flex;align-items:center;gap:12px;padding:20px 22px;border-bottom:1px solid #edf0f5}.ao-template-card-heading h3{margin:0;font-size:16px;font-weight:800}.ao-template-card-heading p{margin:4px 0 0;color:#64748b;font-size:11px;line-height:1.5}.ao-template-icon{display:grid;flex:0 0 40px;width:40px;height:40px;place-items:center;border-radius:11px;background:#e8edf5;color:#25324d;font-size:17px}.ao-template-icon.violet{background:#ede9fe;color:#6d28d9}.ao-template-icon.green{background:#d1fae5;color:#047857}
.ao-template-form{padding:24px}.ao-template-field{display:grid;gap:9px;margin-bottom:22px}.ao-template-field label{color:#334155;font-size:10px;font-weight:850;letter-spacing:.1em;text-transform:uppercase}.ao-template-field label>span{color:#be123c}.ao-template-field input,.ao-template-field textarea{display:block;box-sizing:border-box;width:100%;min-width:0;padding:13px 14px;border:1px solid #dbe2ed;border-radius:10px;background:#f9fbfe;color:#172033;font:inherit;font-size:13px;line-height:1.6;transition:border-color .15s,box-shadow .15s}.ao-template-field input{min-height:48px}.ao-template-field textarea{min-height:390px;resize:vertical;font-family:Consolas,"Courier New",monospace;font-size:12px;tab-size:2}.ao-template-field input::placeholder{color:#7b889b}.ao-template-field input:focus,.ao-template-field textarea:focus{outline:0;border-color:#657fa7;box-shadow:0 0 0 3px rgba(101,127,167,.15);background:#fff}.ao-template-field [aria-invalid=true]{border-color:#e11d48}
.ao-template-label-row{display:flex;align-items:center;justify-content:space-between;gap:12px}.ao-template-format{padding:3px 7px;border-radius:5px;background:#f1f5f9;color:#64748b;font-size:9px;font-weight:800}.ao-template-help{margin:0;color:#64748b;font-size:11px;line-height:1.6}.ao-template-field-error{margin:0;color:#be123c;font-size:12px}
.ao-template-setting{padding:16px;border:1px solid #e2e8f0;border-radius:11px;background:#f8fafc}.ao-template-toggle{display:flex;align-items:center;gap:12px;position:relative;cursor:pointer}.ao-template-toggle input{position:absolute;width:1px;height:1px;opacity:0}.ao-template-switch{position:relative;flex:0 0 40px;width:40px;height:23px;border-radius:999px;background:#64748b;transition:background .15s}.ao-template-switch:after{content:"";position:absolute;left:3px;top:3px;width:17px;height:17px;border-radius:50%;background:#fff;transition:transform .15s}.ao-template-toggle input:checked+.ao-template-switch{background:#0f766e}.ao-template-toggle input:checked+.ao-template-switch:after{transform:translateX(17px)}.ao-template-toggle input:focus-visible+.ao-template-switch{outline:3px solid #8bb7e6;outline-offset:3px}.ao-template-toggle strong,.ao-template-toggle small{display:block}.ao-template-toggle strong{font-size:12px}.ao-template-toggle small{margin-top:3px;color:#64748b;font-size:11px;line-height:1.5}
.ao-template-form-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px;padding-top:21px;border-top:1px solid #edf0f5}.ao-template-button{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;box-sizing:border-box;padding:11px 19px;border:1px solid transparent;border-radius:999px;cursor:pointer;font:inherit;font-size:11px;font-weight:800;text-decoration:none;transition:background .15s,box-shadow .15s}.ao-template-button.primary{background:#172033;color:#fff}.ao-template-button.primary:hover{background:#2d3d5a;box-shadow:0 4px 12px rgba(15,23,42,.12)}.ao-template-button.secondary{border-color:#dbe2ed;background:#fff;color:#334155}.ao-template-button.secondary:hover{background:#f1f5f9}.ao-template-button:focus-visible,.ao-template-back:focus-visible{outline:3px solid #8bb7e6;outline-offset:3px}
.ao-template-card-body,.ao-template-test-form{padding:20px 22px}.ao-template-aside-copy{margin:0 0 16px;color:#64748b;font-size:12px;line-height:1.7}.ao-template-variables{display:flex;flex-wrap:wrap;gap:8px}.ao-template-variables code{max-width:100%;padding:7px 9px;border:1px solid #e3def9;border-radius:7px;background:#f5f3ff;color:#5b21b6;font-family:Consolas,"Courier New",monospace;font-size:11px;overflow-wrap:anywhere;user-select:all}.ao-template-tip{display:flex;align-items:flex-start;gap:8px;margin-top:17px;padding:12px;border-radius:9px;background:#f0f9ff;color:#075985}.ao-template-tip i{margin-top:2px}.ao-template-tip p{margin:0;font-size:11px;line-height:1.6}.ao-template-test-form .ao-template-button{width:100%}.ao-template-updated{display:flex;align-items:center;gap:7px;padding:0 5px;color:#64748b;font-size:11px}
@media(max-width:1150px){.ao-template-grid{grid-template-columns:minmax(0,1fr) 290px}.ao-template-hero{align-items:flex-start;flex-direction:column}.ao-template-card-heading,.ao-template-card-body,.ao-template-test-form{padding:18px}.ao-template-form{padding:20px}}
@media(max-width:850px){.ao-template-grid{grid-template-columns:1fr}.ao-template-aside{grid-template-columns:repeat(2,minmax(0,1fr))}.ao-template-updated{grid-column:1/-1}}
@media(max-width:600px){.ao-template-editor{gap:16px}.ao-template-hero{padding:22px;border-radius:15px}.ao-template-aside{grid-template-columns:1fr}.ao-template-form{padding:17px}.ao-template-form-actions{flex-direction:column}.ao-template-button{width:100%}.ao-template-field textarea{min-height:300px}.ao-template-card-heading h3{font-size:15px}}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\email-templates\edit.blade.php ENDPATH**/ ?>