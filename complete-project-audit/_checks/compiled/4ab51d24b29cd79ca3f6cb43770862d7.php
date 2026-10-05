

<?php $__env->startSection('title', 'Inventory Management'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">
            <div class="container">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">

                    <div>
                        <h1 style="margin:0;">
                            Inventory Management
                        </h1>

                        <p style="margin:8px 0 0;color:#666;">
                            <?php echo e($product->title); ?>

                        </p>

                        <?php if($product->sku): ?>
                            <small style="color:#888;">
                                SKU : <?php echo e($product->sku); ?>

                            </small>
                        <?php endif; ?>
                    </div>

                    <a
                        href="<?php echo e(route('admin.products.index')); ?>"
                        style="
                            background:#444;
                            color:#fff;
                            padding:10px 18px;
                            border-radius:5px;
                            text-decoration:none;
                        "
                    >
                        ← Back to Products
                    </a>

                </div>

                <?php if(session('success')): ?>

                    <div
                        style="
                            background:#d4edda;
                            color:#155724;
                            border:1px solid #c3e6cb;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        <?php echo e(session('success')); ?>

                    </div>

                <?php endif; ?>

                <?php if(session('error')): ?>

                    <div
                        style="
                            background:#f8d7da;
                            color:#721c24;
                            border:1px solid #f5c6cb;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        <?php echo e(session('error')); ?>

                    </div>

                <?php endif; ?>

                <?php if($errors->any()): ?>

                    <div
                        style="
                            background:#fff3cd;
                            color:#856404;
                            border:1px solid #ffeeba;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        <ul style="margin:0;padding-left:20px;">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                <?php endif; ?>


                <?php if($product->variants->count()): ?>

                    <h2 style="margin-bottom:20px;">
                        Product Variants
                    </h2>

                    <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <form
                            method="POST"
                            action="<?php echo e(route('admin.products.inventory.update',$product)); ?>"
                            style="
                                border:1px solid #ddd;
                                border-radius:8px;
                                padding:20px;
                                margin-bottom:20px;
                            "
                        >

                            <?php echo csrf_field(); ?>

                            <input
                                type="hidden"
                                name="variant_id"
                                value="<?php echo e($variant->id); ?>"
                            >

                            <h3 style="margin-top:0;">

                                <?php echo e(collect($variant->options)
                                    ->pluck('value_label')
                                    ->implode(' / ')); ?>


                            </h3>

                            <p>

                                Current Stock :

                                <strong>

                                    <?php echo e($variant->stock); ?>


                                </strong>

                            </p>

                            <div
                                style="
                                    display:grid;
                                    grid-template-columns:repeat(4,1fr);
                                    gap:15px;
                                "
                            >

                                <select
                                    name="adjustment_type"
                                    required
                                >
                                    <option value="add">
                                        Add Stock
                                    </option>

                                    <option value="remove">
                                        Remove Stock
                                    </option>

                                    <option value="set">
                                        Set Exact Stock
                                    </option>
                                </select>

                                <input
                                    type="number"
                                    name="quantity"
                                    min="0"
                                    required
                                    placeholder="Quantity"
                                >

                                <input
                                    type="text"
                                    name="reason"
                                    required
                                    placeholder="Reason"
                                >

                                <button
                                    type="submit"
                                    style="
                                        background:#0d6efd;
                                        color:#fff;
                                        border:none;
                                        border-radius:5px;
                                        cursor:pointer;
                                    "
                                >
                                    Save
                                </button>

                            </div>

                            <textarea
                                name="notes"
                                rows="3"
                                placeholder="Notes (optional)"
                                style="
                                    width:100%;
                                    margin-top:15px;
                                "
                            ></textarea>

                        </form>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php else: ?>

                    <form
                        method="POST"
                        action="<?php echo e(route('admin.products.inventory.update',$product)); ?>"
                        style="
                            border:1px solid #ddd;
                            border-radius:8px;
                            padding:25px;
                        "
                    >

                        <?php echo csrf_field(); ?>

                        <h2 style="margin-top:0;">
                            Current Stock :
                            <?php echo e($product->stock); ?>

                        </h2>

                        <div
                            style="
                                display:grid;
                                grid-template-columns:repeat(4,1fr);
                                gap:15px;
                            "
                        >

                            <select
                                name="adjustment_type"
                                required
                            >
                                <option value="add">
                                    Add Stock
                                </option>

                                <option value="remove">
                                    Remove Stock
                                </option>

                                <option value="set">
                                    Set Exact Stock
                                </option>
                            </select>

                            <input
                                type="number"
                                name="quantity"
                                min="0"
                                required
                                placeholder="Quantity"
                            >

                            <input
                                type="text"
                                name="reason"
                                required
                                placeholder="Reason"
                            >

                            <button
                                type="submit"
                                style="
                                    background:#198754;
                                    color:#fff;
                                    border:none;
                                    border-radius:5px;
                                    cursor:pointer;
                                "
                            >
                                Save Adjustment
                            </button>

                        </div>

                        <textarea
                            name="notes"
                            rows="4"
                            placeholder="Notes"
                            style="
                                width:100%;
                                margin-top:20px;
                            "
                        ></textarea>

                    </form>

                <?php endif; ?>


                <div style="margin-top:50px;">

                    <h2>
                        Recent Inventory History
                    </h2>

                    <table
                        width="100%"
                        border="1"
                        cellspacing="0"
                        cellpadding="10"
                        style="
                            border-collapse:collapse;
                            margin-top:20px;
                        "
                    >

                        <thead>

                            <tr>

                                <th>Date</th>

                                <th>Movement</th>

                                <th>Before</th>

                                <th>After</th>

                                <th>Change</th>

                                <th>Reason</th>

                                <th>Performed By</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php $__empty_1 = true; $__currentLoopData = $product->inventoryHistories()->latest()->take(20)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                <tr>

                                    <td>

                                        <?php echo e($history->created_at->format('d M Y H:i')); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->movement_label); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->stock_before); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->stock_after); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->formatted_quantity_change); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->reason); ?>


                                    </td>

                                    <td>

                                        <?php echo e($history->performed_by); ?>


                                    </td>

                                </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                <tr>

                                    <td colspan="7">

                                        No inventory history found.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\products\inventory.blade.php ENDPATH**/ ?>