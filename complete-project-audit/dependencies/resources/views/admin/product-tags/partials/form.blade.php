@php
    $editing = isset($productTag) && $productTag;
@endphp

@if($errors->any())
<div class="tag-popup" id="tagValidationPopup" role="dialog" aria-modal="true">
    <div class="tag-popup-backdrop" data-tag-popup-close></div>
    <div class="tag-popup-dialog">
        <button type="button" class="tag-popup-x" data-tag-popup-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        <div class="tag-popup-icon tag-popup-icon-error"><i class="fa-solid fa-circle-exclamation"></i></div>
        <span class="admin-page-eyebrow">Product tags</span>
        <h3>Please correct the tag details</h3>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="admin-button admin-button-primary" data-tag-popup-close>Review Fields</button>
    </div>
</div>
@endif

<div class="tag-editor-grid">
    <section class="admin-panel tag-panel">
        <div class="tag-panel-heading">
            <div>
                <span class="admin-page-eyebrow">Tag information</span>
                <h3>{{ $editing ? 'Edit Product Tag' : 'Create Product Tag' }}</h3>
                <p>Use a short, recognizable tag name for grouping related products.</p>
            </div>
            <div class="tag-panel-icon"><i class="fa-solid fa-tags"></i></div>
        </div>

        <div class="tag-fields">
            <div class="tag-field">
                <label for="title">Tag Title <span>*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $editing ? $productTag->title : '') }}"
                    maxlength="255"
                    required
                    autofocus
                    placeholder="e.g. Leather Jackets"
                >
                <small>The slug below is generated automatically until you customize it.</small>
            </div>

            <div class="tag-field">
                <label for="slug">Slug</label>
                <div class="tag-slug-control">
                    <span>/</span>
                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug', $editing ? $productTag->slug : '') }}"
                        maxlength="255"
                        placeholder="leather-jackets"
                        data-original-slug="{{ $editing ? $productTag->slug : '' }}"
                    >
                </div>
                <small>SEO-friendly URL value. You can edit it manually at any time.</small>
            </div>
        </div>
    </section>

    <aside class="admin-panel tag-side-panel">
        <span class="admin-page-eyebrow">{{ $editing ? 'Tag summary' : 'Ready to create' }}</span>

        @if($editing)
            <div class="tag-summary-row">
                <span>Tag ID</span>
                <strong>#{{ $productTag->id }}</strong>
            </div>
            <div class="tag-summary-row">
                <span>Products</span>
                <strong>{{ number_format((int) ($productTag->products_count ?? 0)) }}</strong>
            </div>
            <div class="tag-summary-row">
                <span>Last Updated</span>
                <strong>{{ $productTag->updated_at?->format('M d, Y') ?: '—' }}</strong>
            </div>
        @else
            <div class="tag-help-box">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <p>Enter the title and the slug will be prepared automatically. Edit the slug only when you need a custom URL value.</p>
            </div>
        @endif

        <button type="submit" class="admin-button admin-button-primary tag-submit">
            <i class="fa-solid {{ $editing ? 'fa-floppy-disk' : 'fa-plus' }}"></i>
            {{ $editing ? 'Save Changes' : 'Create Tag' }}
        </button>
    </aside>
</div>

@push('page-styles')
<style>
.tag-editor-grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:16px;align-items:start}
.tag-panel,.tag-side-panel{border:1px solid #e6eaf1!important;border-radius:11px!important;background:#fff!important;box-shadow:none!important}
.tag-panel{padding:20px}
.tag-side-panel{padding:18px;position:sticky;top:18px}
.tag-panel-heading{display:flex;justify-content:space-between;gap:18px;padding-bottom:17px;margin-bottom:18px;border-bottom:1px solid #edf0f4}
.tag-panel-heading h3{margin:4px 0;color:#172033;font-size:16px;font-weight:800}
.tag-panel-heading p{margin:0;color:#7b8497;font-size:11px}
.tag-panel-icon{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:9px;background:#eeedff;color:#635bff}
.tag-fields{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.tag-field label{display:block;margin-bottom:7px;color:#667085;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.tag-field label span{color:#d14343}
.tag-field input{width:100%;min-height:42px;padding:0 11px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#344054;font-size:11px;transition:.18s ease}
.tag-field input:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}
.tag-field small{display:block;margin-top:6px;color:#98a2b3;font-size:9px;line-height:1.5}
.tag-slug-control{display:flex;align-items:center;border:1px solid #e0e5ed;border-radius:7px;background:#fff;overflow:hidden}
.tag-slug-control:focus-within{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10)}
.tag-slug-control>span{display:flex;align-items:center;min-height:40px;padding:0 10px;border-right:1px solid #edf0f4;background:#f8f9fb;color:#98a2b3;font-size:11px}
.tag-slug-control input{border:0!important;border-radius:0!important;box-shadow:none!important}
.tag-summary-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid #edf0f4;font-size:10px}
.tag-summary-row span{color:#7b8497}.tag-summary-row strong{color:#172033}
.tag-help-box{display:flex;gap:10px;margin:14px 0;padding:12px;border:1px solid #e2e1ff;border-radius:8px;background:#f7f6ff;color:#635bff}
.tag-help-box p{margin:0;color:#656d7c;font-size:10px;line-height:1.55}
.tag-submit{width:100%;min-height:40px;margin-top:16px;justify-content:center;border-color:#635bff!important;background:#635bff!important}
.tag-popup{position:fixed;inset:0;z-index:13000;display:flex;align-items:center;justify-content:center;padding:20px}
.tag-popup-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}
.tag-popup-dialog{position:relative;z-index:2;width:100%;max-width:450px;padding:30px;border-radius:14px;background:#fff;box-shadow:0 24px 80px rgba(15,23,42,.24);text-align:center}
.tag-popup-x{position:absolute;top:12px;right:12px;width:32px;height:32px;border:0;border-radius:7px;background:#f3f5f8;color:#687386;cursor:pointer}
.tag-popup-icon{display:flex;align-items:center;justify-content:center;width:58px;height:58px;margin:0 auto 15px;border-radius:50%;font-size:21px}
.tag-popup-icon-error{background:#fff0f0;color:#d14343}
.tag-popup-dialog h3{margin:5px 0 12px;color:#172033;font-size:18px;font-weight:800}
.tag-popup-dialog ul{margin:0 0 18px;padding:12px 16px;border:1px solid #fecaca;border-radius:9px;background:#fff7f7;color:#b42318;font-size:10px;line-height:1.6;list-style-position:inside;text-align:left}
@media(max-width:850px){.tag-editor-grid{grid-template-columns:1fr}.tag-side-panel{position:static}.tag-fields{grid-template-columns:1fr}}
</style>
@endpush

@push('page-scripts')
<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const title = document.getElementById('title');
    const slug = document.getElementById('slug');

    if (title && slug) {
        const originalTitle = @json($editing ? $productTag->title : '');
        const originalSlug = slug.dataset.originalSlug || '';
        let manualSlug = false;

        function slugify(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/['’]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        if (!@json($editing)) {
            manualSlug = slug.value.trim() !== '';
        }

        slug.addEventListener('input', function () {
            const automaticForCurrentTitle = slugify(title.value);
            manualSlug = slug.value.trim() !== '' && slug.value !== automaticForCurrentTitle;
        });

        title.addEventListener('input', function () {
            if (@json($editing) && title.value === originalTitle && slug.value === originalSlug) {
                return;
            }

            if (!manualSlug) {
                slug.value = slugify(title.value);
            }
        });
    }

    const popup = document.getElementById('tagValidationPopup');

    if (popup) {
        document.body.style.overflow = 'hidden';

        popup.querySelectorAll('[data-tag-popup-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                popup.remove();
                document.body.style.overflow = '';
            });
        });
    }
});
</script>
@endpush
