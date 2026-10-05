@extends('admin.layouts.app')

@section('title', 'Blog Categories')
@section('page-heading', 'Blog Categories')

@section('content')
@php
    $renderTreeRows = function ($nodes, $depth = 0) use (&$renderTreeRows) {
        foreach ($nodes as $node) {
            $children = $node->relationLoaded('childrenRecursive') ? $node->childrenRecursive : collect();
            $postsCount = (int) ($node->posts_count ?? 0);
            $childrenCount = (int) ($node->children_count ?? $children->count());
            echo '<tr>';
            echo '<td><div class="az-cat-name" style="--depth:' . (int)$depth . '">';
            echo '<span class="az-tree-line">' . ($depth > 0 ? '↳' : '<i class="fa-solid fa-folder-tree"></i>') . '</span>';
            echo '<div class="az-cat-image">';
            if ($node->image_url) echo '<img src="' . e($node->image_url) . '" alt="' . e($node->title) . '" loading="lazy">';
            else echo '<i class="fa-regular fa-folder"></i>';
            echo '</div><div><strong>' . e($node->title) . '</strong><small>/blog/category/' . e($node->slug) . '</small></div></div></td>';
            echo '<td><span class="az-type ' . ($depth === 0 ? 'az-root' : 'az-child') . '">' . ($depth === 0 ? 'Root' : 'Level ' . $depth) . '</span></td>';
            echo '<td><span class="az-count">' . $postsCount . '</span></td>';
            echo '<td><span class="az-count">' . $childrenCount . '</span></td>';
            echo '<td><div class="az-row-actions"><a class="az-icon-btn" href="' . route('admin.categories.edit', $node) . '" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>';
            echo '<button type="button" class="az-icon-btn az-danger" title="Delete" data-delete-category data-delete-url="' . route('admin.categories.destroy', $node) . '" data-category-title="' . e($node->title) . '" data-child-count="' . $childrenCount . '" data-post-count="' . $postsCount . '"><i class="fa-regular fa-trash-can"></i></button></div></td>';
            echo '</tr>';
            if ($children->isNotEmpty()) $renderTreeRows($children, $depth + 1);
        }
    };
@endphp

<style>
.az-cats{display:flex;flex-direction:column;gap:16px}.az-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.az-eyebrow{font-size:10px;font-weight:800;letter-spacing:.13em;color:#635bff;text-transform:uppercase}.az-head h1{margin:3px 0 4px;color:#101828;font-size:24px}.az-head p{margin:0;color:#667085;font-size:12px}.az-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 12px;border:1px solid #dfe3ea;border-radius:10px;background:#fff;color:#344054;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}.az-btn-primary{background:#635bff;border-color:#635bff;color:#fff}.az-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.az-stat{background:#fff;border:1px solid #e4e7ec;border-radius:11px;padding:12px}.az-stat-top{display:flex;align-items:center;justify-content:space-between}.az-stat-label{color:#667085;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}.az-stat-icon{width:28px;height:28px;border-radius:8px;background:#f0efff;color:#635bff;display:grid;place-items:center;font-size:11px}.az-stat-value{margin-top:8px;color:#101828;font-size:20px;font-weight:800}.az-panel{background:#fff;border:1px solid #e4e7ec;border-radius:12px;overflow:hidden}.az-toolbar{padding:10px;border-bottom:1px solid #eef0f3;display:flex;align-items:center;justify-content:space-between;gap:10px}.az-search-form{display:flex;gap:7px;flex:1}.az-search{position:relative;flex:1;max-width:520px}.az-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#98a2b3;font-size:11px}.az-search input{width:100%;height:36px;box-sizing:border-box;padding:0 10px 0 31px;border:1px solid #dfe3ea;border-radius:9px;font-size:11px;outline:none}.az-search input:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-view-note{color:#98a2b3;font-size:9px;white-space:nowrap}.az-table-wrap{overflow-x:auto}.az-table{width:100%;border-collapse:collapse}.az-table th{padding:9px 11px;background:#f9fafb;border-bottom:1px solid #e4e7ec;color:#667085;font-size:9px;text-align:left;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}.az-table td{padding:9px 11px;border-bottom:1px solid #eef0f3;color:#344054;font-size:11px;vertical-align:middle}.az-table tbody tr:last-child td{border-bottom:0}.az-cat-name{display:flex;align-items:center;gap:8px;min-width:280px;padding-left:calc(var(--depth) * 18px)}.az-tree-line{width:18px;flex:0 0 18px;color:#98a2b3;text-align:center;font-size:11px}.az-cat-image{width:38px;height:38px;flex:0 0 38px;border-radius:8px;overflow:hidden;background:#f2f4f7;color:#98a2b3;display:grid;place-items:center}.az-cat-image img{width:100%;height:100%;object-fit:cover}.az-cat-name strong{display:block;color:#101828;font-size:11px}.az-cat-name small{display:block;margin-top:2px;color:#98a2b3;font-size:9px}.az-type{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:9px;font-weight:800}.az-root{background:#f0efff;color:#5148d8}.az-child{background:#f2f4f7;color:#667085}.az-count{display:inline-flex;min-width:24px;height:24px;align-items:center;justify-content:center;padding:0 6px;border-radius:7px;background:#f9fafb;color:#475467;font-size:9px;font-weight:800}.az-row-actions{display:flex;gap:5px}.az-icon-btn{width:30px;height:30px;border:1px solid #dfe3ea;border-radius:8px;background:#fff;display:grid;place-items:center;color:#475467;text-decoration:none;cursor:pointer}.az-danger{color:#c01048}.az-empty{padding:45px 20px;text-align:center;color:#667085;font-size:11px}.az-search-results{padding:8px 11px;border-bottom:1px solid #eef0f3;background:#fcfcfd;color:#667085;font-size:9px}.az-modal-bg{position:fixed;inset:0;z-index:1300;background:rgba(15,23,42,.52);display:grid;place-items:center;padding:20px}.az-modal-bg[hidden]{display:none}.az-modal{width:min(430px,100%);background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:20px;box-shadow:0 24px 70px rgba(15,23,42,.22)}.az-modal-icon{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:#fff1f3;color:#c01048;margin-bottom:11px}.az-modal h2{margin:4px 0 7px;color:#101828;font-size:18px}.az-modal p{margin:0;color:#667085;font-size:11px;line-height:1.55}.az-warning{display:none;margin-top:10px;padding:9px;border:1px solid #fedf89;border-radius:8px;background:#fffaeb;color:#b54708;font-size:9px;line-height:1.5}.az-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:17px}.az-delete{background:#d92d20;border-color:#d92d20;color:#fff}.az-pagination{padding:10px;border-top:1px solid #eef0f3}@media(max-width:700px){.az-head,.az-toolbar{flex-direction:column;align-items:stretch}.az-stats{grid-template-columns:1fr}.az-search-form{flex-direction:column}.az-search{max-width:none}.az-view-note{white-space:normal}.az-modal-actions{flex-direction:column-reverse}.az-modal-actions>*{width:100%}}
</style>

<div class="az-cats">
    <header class="az-head">
        <div><div class="az-eyebrow">Content Management / Taxonomy</div><h1>Blog Categories</h1><p>Build and manage the hierarchical content structure used by Arizona Outfits articles.</p></div>
        <a href="{{ route('admin.categories.create') }}" class="az-btn az-btn-primary"><i class="fa-solid fa-plus"></i> Create Category</a>
    </header>

    <div class="az-stats">
        <div class="az-stat"><div class="az-stat-top"><span class="az-stat-label">Total Categories</span><span class="az-stat-icon"><i class="fa-solid fa-layer-group"></i></span></div><div class="az-stat-value">{{ number_format($stats['total']) }}</div></div>
        <div class="az-stat"><div class="az-stat-top"><span class="az-stat-label">Root Categories</span><span class="az-stat-icon"><i class="fa-solid fa-folder-tree"></i></span></div><div class="az-stat-value">{{ number_format($stats['roots']) }}</div></div>
        <div class="az-stat"><div class="az-stat-top"><span class="az-stat-label">Child Categories</span><span class="az-stat-icon"><i class="fa-solid fa-code-branch"></i></span></div><div class="az-stat-value">{{ number_format($stats['children']) }}</div></div>
    </div>

    <section class="az-panel">
        <div class="az-toolbar">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="az-search-form">
                <div class="az-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Search category name, slug or description..."></div>
                <button type="submit" class="az-btn az-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                @if(request()->filled('search'))<a href="{{ route('admin.categories.index') }}" class="az-btn">Reset</a>@endif
            </form>
            <span class="az-view-note"><i class="fa-solid fa-diagram-project"></i> Hierarchical tree view</span>
        </div>

        @if(request()->filled('search'))
            <div class="az-search-results">
                Search matched {{ $categories->total() }} categor{{ $categories->total() === 1 ? 'y' : 'ies' }}. The tree below remains visible so you can understand each category's hierarchy.
            </div>
        @endif

        <div class="az-table-wrap">
            <table class="az-table">
                <thead><tr><th>Category</th><th>Hierarchy</th><th>Posts</th><th>Children</th><th>Actions</th></tr></thead>
                <tbody>
                    @if($tree->isNotEmpty())
                        @php $renderTreeRows($tree); @endphp
                    @else
                        <tr><td colspan="5"><div class="az-empty"><i class="fa-regular fa-folder-open" style="font-size:22px;margin-bottom:8px"></i><br>No blog categories have been created yet.</div></td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(request()->filled('search') && $categories->hasPages())
            <div class="az-pagination">{{ $categories->links() }}</div>
        @endif
    </section>
</div>

<div class="az-modal-bg" data-delete-modal hidden>
    <div class="az-modal" role="dialog" aria-modal="true" aria-labelledby="az-delete-category-title">
        <div class="az-modal-icon"><i class="fa-regular fa-trash-can"></i></div>
        <div class="az-eyebrow">Permanent Action</div>
        <h2 id="az-delete-category-title">Delete this category?</h2>
        <p><strong data-delete-name>Category</strong> will be permanently deleted if it is not currently used by posts and has no child categories.</p>
        <div class="az-warning" data-delete-warning></div>
        <div class="az-modal-actions">
            <button type="button" class="az-btn" data-delete-cancel>Cancel</button>
            <form method="POST" data-delete-form>@csrf @method('DELETE')<button type="submit" class="az-btn az-delete" data-delete-submit><i class="fa-regular fa-trash-can"></i> Delete Permanently</button></form>
        </div>
    </div>
</div>

<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.querySelector('[data-delete-modal]');
    const form = document.querySelector('[data-delete-form]');
    const name = document.querySelector('[data-delete-name]');
    const warning = document.querySelector('[data-delete-warning]');
    const submit = document.querySelector('[data-delete-submit]');

    document.querySelectorAll('[data-delete-category]').forEach(button => {
        button.addEventListener('click', function () {
            const childCount = Number(button.dataset.childCount || 0);
            const postCount = Number(button.dataset.postCount || 0);
            form.action = button.dataset.deleteUrl || '';
            name.textContent = button.dataset.categoryTitle || 'Category';

            if (childCount > 0 || postCount > 0) {
                const reasons = [];
                if (childCount > 0) reasons.push(childCount + ' child categor' + (childCount === 1 ? 'y' : 'ies'));
                if (postCount > 0) reasons.push(postCount + ' linked post' + (postCount === 1 ? '' : 's'));
                warning.textContent = 'Deletion is currently blocked because this category has ' + reasons.join(' and ') + '. Reassign or remove those relationships first.';
                warning.style.display = 'block';
                submit.disabled = true;
                submit.style.opacity = '.5';
                submit.style.cursor = 'not-allowed';
            } else {
                warning.textContent = '';
                warning.style.display = 'none';
                submit.disabled = false;
                submit.style.opacity = '';
                submit.style.cursor = '';
            }
            modal.hidden = false;
        });
    });

    document.querySelector('[data-delete-cancel]')?.addEventListener('click', () => modal.hidden = true);
    modal?.addEventListener('click', event => { if (event.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal && !modal.hidden) modal.hidden = true; });
});
</script>
@endsection
