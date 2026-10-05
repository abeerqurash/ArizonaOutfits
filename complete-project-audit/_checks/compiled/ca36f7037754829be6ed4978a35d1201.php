<?php $__env->startSection('title', 'Admin Profile'); ?>

<?php $__env->startSection('page-heading', 'Edit Profile'); ?>



<?php $__env->startSection('content'); ?>

<?php

    $profileErrors = $errors->getBag('default');

    $passwordErrors = $errors->getBag('updatePassword');

    $popupTitle = null;

    $popupMessage = null;

    $popupType = 'success';



    if (session('status')) {

        $popupTitle = 'Profile updated';

        $popupMessage = session('status');

    } elseif (session('password_status')) {

        $popupTitle = 'Password updated';

        $popupMessage = session('password_status');

    } elseif ($profileErrors->any()) {

        $popupTitle = 'Unable to update profile';

        $popupMessage = $profileErrors->first();

        $popupType = 'error';

    } elseif ($passwordErrors->any()) {

        $popupTitle = 'Unable to update password';

        $popupMessage = $passwordErrors->first();

        $popupType = 'error';

    }

?>



<div class="admin-profile-page">

    <div class="admin-profile-page-heading">

        <div>

            <span class="admin-profile-eyebrow">Administrator account</span>

            <h2>Profile & security</h2>

            <p>Manage your dedicated administrator account details and password.</p>

        </div>



        <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-profile-back">

            <i class="fa-solid fa-arrow-left"></i>

            <span>Dashboard</span>

        </a>

    </div>



    <div class="admin-profile-grid">

        <section class="admin-profile-card">

            <div class="admin-profile-card-heading">

                <span class="admin-profile-card-icon">

                    <i class="fa-regular fa-user"></i>

                </span>

                <div>

                    <h3>Administrator details</h3>

                    <p>This updates the dedicated admin account only.</p>

                </div>

            </div>



            <form method="POST" action="<?php echo e(route('admin.profile.update')); ?>" class="admin-profile-form" enctype="multipart/form-data">

                <?php echo csrf_field(); ?>

                <?php echo method_field('PATCH'); ?>



                <div class="admin-profile-field">

                    <label for="admin-name">Name</label>

                    <input

                        id="admin-name"

                        type="text"

                        name="name"

                        value="<?php echo e(old('name', $admin->name)); ?>"

                        maxlength="255"

                        autocomplete="name"

                        required

                    >

                </div>



                <div class="admin-profile-field">

                    <label for="admin-email">Email address</label>

                    <input

                        id="admin-email"

                        type="email"

                        name="email"

                        value="<?php echo e(old('email', $admin->email)); ?>"

                        maxlength="255"

                        autocomplete="email"

                        required

                    >

                    <small>Changing this does not change a customer account with the same email.</small>

                </div>



                <div class="admin-profile-field">

                    <label for="admin-phone">Phone</label>

                    <input

                        id="admin-phone"

                        type="text"

                        name="phone"

                        value="<?php echo e(old('phone', $admin->phone)); ?>"

                        maxlength="50"

                        autocomplete="tel"

                    >

                </div>



                <div class="admin-profile-author-section">
                    <div class="admin-profile-section-heading">
                        <span class="admin-profile-card-icon"><i class="fa-regular fa-pen-to-square"></i></span>
                        <div>
                            <h3>Public blog author profile</h3>
                            <p>These details are displayed on blog articles written by this administrator.</p>
                        </div>
                    </div>

                    <?php
                        $adminProfileImageUrl = $admin->profile_image
                            ? asset('storage/' . ltrim($admin->profile_image, '/'))
                            : null;
                    ?>

                    <div class="admin-profile-author-image-row">
                        <div class="admin-profile-author-preview <?php echo e($adminProfileImageUrl ? 'has-image' : ''); ?>"
                             id="adminAuthorImagePreview"
                             <?php if($adminProfileImageUrl): ?> style="background-image:url('<?php echo e($adminProfileImageUrl); ?>')" <?php endif; ?>>
                            <span id="adminAuthorImageFallback"><i class="fa-regular fa-user"></i></span>
                        </div>

                        <div class="admin-profile-author-image-actions">
                            <input type="file" name="profile_image_upload" id="adminProfileImageUpload"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>

                            <button type="button" class="admin-profile-upload-button" id="adminProfileImageChoose">
                                <i class="fa-regular fa-image"></i> Choose profile picture
                            </button>

                            <label class="admin-profile-remove-option">
                                <input type="checkbox" name="remove_profile_image" value="1"
                                       id="adminProfileImageRemove" <?php echo e(old('remove_profile_image') ? 'checked' : ''); ?>>
                                <span>Remove current picture</span>
                            </label>
                            <small>JPG, JPEG, PNG or WebP. Maximum 5 MB.</small>
                        </div>
                    </div>

                    <div class="admin-profile-field">
                        <label for="admin-author-title">Author title</label>
                        <input id="admin-author-title" type="text" name="author_title"
                               value="<?php echo e(old('author_title', $admin->author_title)); ?>"
                               maxlength="255" placeholder="e.g. Founder, Editor, Fashion Writer">
                        <small>This is your public editorial title, not your Admin permission role.</small>
                    </div>

                    <div class="admin-profile-social-grid">
                        <div class="admin-profile-field">
                            <label for="admin-facebook-url">Facebook URL</label>
                            <input id="admin-facebook-url" type="url" name="facebook_url"
                                   value="<?php echo e(old('facebook_url', $admin->facebook_url)); ?>" maxlength="2048"
                                   placeholder="https://facebook.com/...">
                        </div>
                        <div class="admin-profile-field">
                            <label for="admin-instagram-url">Instagram URL</label>
                            <input id="admin-instagram-url" type="url" name="instagram_url"
                                   value="<?php echo e(old('instagram_url', $admin->instagram_url)); ?>" maxlength="2048"
                                   placeholder="https://instagram.com/...">
                        </div>
                        <div class="admin-profile-field">
                            <label for="admin-x-url">X URL</label>
                            <input id="admin-x-url" type="url" name="x_url"
                                   value="<?php echo e(old('x_url', $admin->x_url)); ?>" maxlength="2048"
                                   placeholder="https://x.com/...">
                        </div>
                        <div class="admin-profile-field">
                            <label for="admin-linkedin-url">LinkedIn URL</label>
                            <input id="admin-linkedin-url" type="url" name="linkedin_url"
                                   value="<?php echo e(old('linkedin_url', $admin->linkedin_url)); ?>" maxlength="2048"
                                   placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="admin-profile-field admin-profile-social-full">
                            <label for="admin-youtube-url">YouTube URL</label>
                            <input id="admin-youtube-url" type="url" name="youtube_url"
                                   value="<?php echo e(old('youtube_url', $admin->youtube_url)); ?>" maxlength="2048"
                                   placeholder="https://youtube.com/@...">
                        </div>
                    </div>
                </div>

                <div class="admin-profile-meta">

                    <div>

                        <span>Account type</span>

                        <strong><?php echo e($admin->isSuperAdmin() ? 'Super Administrator' : 'Administrator'); ?></strong>

                    </div>

                    <div>

                        <span>Status</span>

                        <strong><?php echo e(ucfirst($admin->status ?? 'active')); ?></strong>

                    </div>

                </div>



                <button type="submit" class="admin-profile-submit">

                    <i class="fa-regular fa-floppy-disk"></i>

                    Save profile

                </button>

            </form>

        </section>



        <section class="admin-profile-card">

            <div class="admin-profile-card-heading">

                <span class="admin-profile-card-icon">

                    <i class="fa-solid fa-lock"></i>

                </span>

                <div>

                    <h3>Change password</h3>

                    <p>Confirm your current administrator password first.</p>

                </div>

            </div>



            <form method="POST" action="<?php echo e(route('admin.profile.password.update')); ?>" class="admin-profile-form">

                <?php echo csrf_field(); ?>

                <?php echo method_field('PUT'); ?>



                <div class="admin-profile-field">

                    <label for="admin-current-password">Current password</label>

                    <input

                        id="admin-current-password"

                        type="password"

                        name="current_password"

                        autocomplete="current-password"

                        required

                    >

                </div>



                <div class="admin-profile-field">

                    <label for="admin-new-password">New password</label>

                    <input

                        id="admin-new-password"

                        type="password"

                        name="password"

                        autocomplete="new-password"

                        required

                    >

                </div>



                <div class="admin-profile-field">

                    <label for="admin-password-confirmation">Confirm new password</label>

                    <input

                        id="admin-password-confirmation"

                        type="password"

                        name="password_confirmation"

                        autocomplete="new-password"

                        required

                    >

                </div>



                <button type="submit" class="admin-profile-submit">

                    <i class="fa-solid fa-key"></i>

                    Update password

                </button>

            </form>

        </section>

    </div>

</div>



<?php if($popupTitle): ?>

<div

    class="admin-profile-popup-backdrop"

    id="adminProfilePopup"

    role="dialog"

    aria-modal="true"

    aria-labelledby="adminProfilePopupTitle"

>

    <div class="admin-profile-popup">

        <span class="admin-profile-popup-icon <?php echo e($popupType); ?>">

            <i class="fa-solid <?php echo e($popupType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"></i>

        </span>



        <h3 id="adminProfilePopupTitle"><?php echo e($popupTitle); ?></h3>

        <p><?php echo e($popupMessage); ?></p>



        <button type="button" class="admin-profile-popup-close" id="adminProfilePopupClose">

            OK

        </button>

    </div>

</div>

<?php endif; ?>



<style>

.admin-profile-page{display:grid;gap:20px}

.admin-profile-page-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px;border:1px solid #e5eaf1;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(23,32,51,.05)}

.admin-profile-eyebrow{display:block;margin-bottom:5px;color:#635bff;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}

.admin-profile-page-heading h2{margin:0;color:#172033;font-size:24px}

.admin-profile-page-heading p{margin:7px 0 0;color:#64748b;font-size:13px}

.admin-profile-back,.admin-profile-submit,.admin-profile-popup-close{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:10px;font-weight:800;text-decoration:none;cursor:pointer}

.admin-profile-back{padding:10px 13px;background:#f1f5f9;color:#172033}

.admin-profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}

.admin-profile-card{padding:22px;border:1px solid #e5eaf1;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(23,32,51,.05)}

.admin-profile-card-heading{display:flex;gap:12px;margin-bottom:20px}

.admin-profile-card-icon{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border-radius:10px;background:#eef2ff;color:#635bff}

.admin-profile-card-heading h3{margin:0;color:#172033;font-size:17px}

.admin-profile-card-heading p{margin:5px 0 0;color:#64748b;font-size:12px}

.admin-profile-form{display:grid;gap:15px}

.admin-profile-field{display:grid;gap:7px}

.admin-profile-field label{color:#334155;font-size:12px;font-weight:800}

.admin-profile-field input{width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #dbe2ea;border-radius:10px;background:#fff;color:#172033;outline:none}

.admin-profile-field input:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.1)}

.admin-profile-field small{color:#64748b;font-size:10px}

.admin-profile-author-section{display:grid;gap:15px;margin-top:4px;padding:16px;border:1px solid #e5eaf1;border-radius:12px;background:#f8fafc}
.admin-profile-section-heading{display:flex;align-items:flex-start;gap:11px}
.admin-profile-section-heading h3{margin:0;color:#172033;font-size:15px}
.admin-profile-section-heading p{margin:4px 0 0;color:#64748b;font-size:11px;line-height:1.5}
.admin-profile-author-image-row{display:flex;align-items:center;gap:14px;padding:13px;border:1px solid #e5eaf1;border-radius:11px;background:#fff}
.admin-profile-author-preview{display:grid;width:82px;height:82px;flex:0 0 82px;place-items:center;overflow:hidden;border:1px solid #dbe2ea;border-radius:12px;background:#eef2ff center/cover no-repeat;color:#635bff;font-size:25px}
.admin-profile-author-preview.has-image span{display:none}
.admin-profile-author-image-actions{display:grid;justify-items:start;gap:8px;min-width:0}
.admin-profile-upload-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 12px;border:1px solid #dbe2ea;border-radius:9px;background:#fff;color:#172033;font-size:12px;font-weight:800;cursor:pointer}
.admin-profile-upload-button:hover{border-color:#635bff;color:#635bff}
.admin-profile-remove-option{display:flex;align-items:center;gap:7px;color:#475569;font-size:11px;font-weight:700;cursor:pointer}
.admin-profile-remove-option input{width:auto;margin:0;accent-color:#635bff}
.admin-profile-author-image-actions small{color:#64748b;font-size:10px}
.admin-profile-social-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.admin-profile-social-full{grid-column:1/-1}

.admin-profile-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}

.admin-profile-meta>div{padding:11px;border-radius:10px;background:#f8fafc}

.admin-profile-meta span,.admin-profile-meta strong{display:block}

.admin-profile-meta span{color:#64748b;font-size:10px}

.admin-profile-meta strong{margin-top:3px;color:#172033;font-size:12px}

.admin-profile-submit{width:max-content;padding:11px 15px;background:#635bff;color:#fff}

.admin-profile-popup-backdrop{position:fixed;inset:0;z-index:99999;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.48)}

.admin-profile-popup{width:min(390px,100%);padding:28px 24px;border-radius:16px;background:#fff;text-align:center;box-shadow:0 24px 70px rgba(15,23,42,.24)}

.admin-profile-popup-icon{display:grid;width:48px;height:48px;margin:0 auto 13px;place-items:center;border-radius:50%;background:#dcfce7;color:#15803d;font-size:20px}

.admin-profile-popup-icon.error{background:#fee2e2;color:#b91c1c}

.admin-profile-popup h3{margin:0;color:#172033;font-size:18px}

.admin-profile-popup p{margin:8px 0 18px;color:#64748b;font-size:13px}

.admin-profile-popup-close{min-width:100px;padding:10px 15px;background:#635bff;color:#fff}



@media(max-width:900px){

    .admin-profile-grid{grid-template-columns:1fr}

    .admin-profile-page-heading{align-items:flex-start;flex-direction:column}

    .admin-profile-back{width:100%}

}



@media(max-width:520px){

    .admin-profile-page-heading,.admin-profile-card{padding:17px}

    .admin-profile-meta{grid-template-columns:1fr}
    .admin-profile-social-grid{grid-template-columns:1fr}
    .admin-profile-social-full{grid-column:auto}
    .admin-profile-author-image-row{align-items:flex-start;flex-direction:column}

}

</style>



<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const fileInput = document.getElementById('adminProfileImageUpload');
    const chooseButton = document.getElementById('adminProfileImageChoose');
    const removeCheckbox = document.getElementById('adminProfileImageRemove');
    const preview = document.getElementById('adminAuthorImagePreview');
    const fallback = document.getElementById('adminAuthorImageFallback');

    if (!fileInput || !chooseButton || !preview) return;

    chooseButton.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.addEventListener('load', function (event) {
            preview.style.backgroundImage = 'url("' + event.target.result + '")';
            preview.classList.add('has-image');
            if (fallback) fallback.style.display = 'none';
            if (removeCheckbox) removeCheckbox.checked = false;
        });
        reader.readAsDataURL(file);
    });

    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function () {
            if (!removeCheckbox.checked) return;
            fileInput.value = '';
            preview.style.backgroundImage = '';
            preview.classList.remove('has-image');
            if (fallback) fallback.style.display = '';
        });
    }
});
</script>

<?php if($popupTitle): ?>

<script>

document.addEventListener('DOMContentLoaded', function () {

    'use strict';



    const popup = document.getElementById('adminProfilePopup');

    const closeButton = document.getElementById('adminProfilePopupClose');



    if (!popup || !closeButton) {

        return;

    }



    const closePopup = function () {

        popup.remove();

    };



    closeButton.addEventListener('click', closePopup);



    popup.addEventListener('click', function (event) {

        if (event.target === popup) {

            closePopup();

        }

    });



    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            closePopup();

        }

    });

});

</script>

<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\profile\edit.blade.php ENDPATH**/ ?>