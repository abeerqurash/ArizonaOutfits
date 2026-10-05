@php



    $editing = isset($post);



    $selected = array_map('intval', (array) old('categories', $selectedCategoryIds ?? []));



    $primaryId = old('primary_category_id', $editing ? $post->primary_category_id : null);



    $statusValue = old('status', $editing ? $post->status : \App\Models\Post::STATUS_DRAFT);



    $imageUrl = $editing ? $post->feature_image_url : null;



    $ogImageUrl = $editing ? $post->og_image_url : null;







    $renderCategoryChecks = function ($nodes, $depth = 0) use (&$renderCategoryChecks, $selected) {



        foreach ($nodes as $node) {



            $checked = in_array((int) $node->id, $selected, true);



            echo '<label class="az-tree-option" style="--depth:' . (int) $depth . '">';



            echo '<input type="checkbox" name="categories[]" value="' . (int) $node->id . '" ' . ($checked ? 'checked' : '') . ' data-category-checkbox>';



            echo '<span class="az-check-box"><i class="fa-solid fa-check"></i></span>';



            echo '<span class="az-tree-text">' . e($node->title) . '</span>';



            echo '</label>';



            if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {



                $renderCategoryChecks($node->childrenRecursive, $depth + 1);



            }



        }



    };







    $renderPrimaryOptions = function ($nodes, $depth = 0) use (&$renderPrimaryOptions, $primaryId) {



        foreach ($nodes as $node) {



            $checked = (string) $primaryId === (string) $node->id;



            echo '<label class="az-tree-option az-primary-option" style="--depth:' . (int) $depth . '">';



            echo '<input type="radio" name="primary_category_id" value="' . (int) $node->id . '" ' . ($checked ? 'checked' : '') . ' data-primary-radio>';



            echo '<span class="az-radio-dot"></span>';



            echo '<span class="az-tree-text">' . e($node->title) . '</span>';



            echo '</label>';



            if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {



                $renderPrimaryOptions($node->childrenRecursive, $depth + 1);



            }



        }



    };



@endphp







<style>



.az-post-editor{display:flex;flex-direction:column;gap:16px}.az-post-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.az-eyebrow{font-size:10px;font-weight:800;letter-spacing:.13em;color:#635bff;text-transform:uppercase}.az-post-head h1{margin:3px 0 4px;color:#101828;font-size:24px;line-height:1.25}.az-post-head p{margin:0;color:#667085;font-size:12px;line-height:1.5}.az-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 12px;border:1px solid #dfe3ea;border-radius:10px;background:#fff;color:#344054;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}.az-btn:hover{background:#f9fafb}.az-btn-primary{background:#635bff;border-color:#635bff;color:#fff}.az-btn-primary:hover{background:#554de8}.az-editor-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.72fr);gap:14px;align-items:start}.az-stack{display:flex;flex-direction:column;gap:12px}.az-panel{background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:15px}.az-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:14px}.az-panel h2{margin:0 0 3px;color:#101828;font-size:13px}.az-panel-desc{margin:0;color:#98a2b3;font-size:10px;line-height:1.5}.az-field{margin-bottom:13px}.az-field:last-child{margin-bottom:0}.az-label{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:5px;color:#344054;font-size:10px;font-weight:800}.az-required{color:#d92d20}.az-control{width:100%;box-sizing:border-box;border:1px solid #dfe3ea;border-radius:9px;background:#fff;color:#344054;font:inherit;font-size:12px;outline:none}.az-input{height:38px;padding:0 11px}.az-textarea{min-height:92px;padding:10px 11px;resize:vertical;line-height:1.55}.az-content{min-height:360px;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px}.az-control:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-help{display:block;margin-top:4px;color:#98a2b3;font-size:9px;line-height:1.45}.az-error{display:block;margin-top:4px;color:#b42318;font-size:10px;font-weight:700}.az-counter{color:#98a2b3;font-size:9px;font-weight:700}.az-slug{display:flex;align-items:center;border:1px solid #dfe3ea;border-radius:9px;overflow:hidden;background:#fff}.az-slug:focus-within{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-slug-prefix{height:36px;display:flex;align-items:center;padding:0 9px;border-right:1px solid #e4e7ec;background:#f9fafb;color:#98a2b3;font-size:10px;white-space:nowrap}.az-slug input{height:36px;flex:1;min-width:0;padding:0 10px;border:0;outline:0;color:#344054;font-size:12px}.az-two{display:grid;grid-template-columns:1fr 1fr;gap:10px}.az-status-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}.az-status-option{position:relative}.az-status-option input{position:absolute;opacity:0;pointer-events:none}.az-status-card{min-height:42px;padding:8px 9px;border:1px solid #e4e7ec;border-radius:9px;display:flex;align-items:center;gap:8px;color:#475467;font-size:10px;font-weight:800;cursor:pointer}.az-status-card i{width:23px;height:23px;border-radius:7px;background:#f2f4f7;display:grid;place-items:center;color:#667085}.az-status-option input:checked+.az-status-card{border-color:#b8b3ff;background:#f5f4ff;color:#5148d8}.az-status-option input:checked+.az-status-card i{background:#e7e5ff;color:#635bff}.az-date-fields{margin-top:11px;padding-top:11px;border-top:1px solid #eef0f3}.az-upload{border:1px dashed #cfd4dc;border-radius:10px;background:#fafbfc;overflow:hidden}.az-upload-preview{height:155px;display:grid;place-items:center;color:#98a2b3}.az-upload-preview img{width:100%;height:100%;object-fit:cover}.az-upload-empty{text-align:center;font-size:10px}.az-upload-empty i{display:block;margin-bottom:6px;font-size:22px}.az-upload-actions{display:flex;align-items:center;gap:7px;padding:9px;border-top:1px solid #eaecf0;background:#fff}.az-file-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}.az-upload-name{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#667085;font-size:9px}.az-remove-check{display:flex;align-items:center;gap:6px;margin-top:8px;color:#b42318;font-size:9px;font-weight:800}.az-tree{max-height:260px;overflow:auto;border:1px solid #eaecf0;border-radius:9px;padding:5px;background:#fcfcfd}.az-tree-option{position:relative;display:flex;align-items:center;gap:8px;min-height:34px;padding:4px 7px 4px calc(7px + (var(--depth) * 16px));border-radius:7px;cursor:pointer}.az-tree-option:hover{background:#f5f4ff}.az-tree-option input{position:absolute;opacity:0;pointer-events:none}.az-check-box{width:16px;height:16px;flex:0 0 16px;border:1px solid #d0d5dd;border-radius:5px;background:#fff;display:grid;place-items:center;color:transparent;font-size:8px}.az-tree-option input:checked+.az-check-box{border-color:#635bff;background:#635bff;color:#fff}.az-radio-dot{width:16px;height:16px;flex:0 0 16px;border:1px solid #d0d5dd;border-radius:50%;background:#fff;position:relative}.az-tree-option input:checked+.az-radio-dot{border-color:#635bff}.az-tree-option input:checked+.az-radio-dot:after{content:"";position:absolute;inset:3px;border-radius:50%;background:#635bff}.az-tree-text{min-width:0;color:#475467;font-size:10px;font-weight:700}.az-tree-empty{padding:18px;text-align:center;color:#98a2b3;font-size:10px}.az-primary-wrap{margin-top:11px}.az-primary-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;color:#344054;font-size:10px;font-weight:800}.az-primary-clear{border:0;background:transparent;color:#635bff;font-size:9px;font-weight:800;cursor:pointer}.az-switch-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #f0f1f3}.az-switch-row:last-child{border-bottom:0}.az-switch-copy strong{display:block;color:#344054;font-size:10px}.az-switch-copy span{display:block;margin-top:2px;color:#98a2b3;font-size:9px}.az-switch{position:relative;width:34px;height:19px;flex:0 0 34px}.az-switch input{position:absolute;opacity:0}.az-switch span{position:absolute;inset:0;border-radius:999px;background:#d0d5dd;cursor:pointer;transition:.15s}.az-switch span:after{content:"";position:absolute;top:3px;left:3px;width:13px;height:13px;border-radius:50%;background:#fff;transition:.15s}.az-switch input:checked+span{background:#635bff}.az-switch input:checked+span:after{transform:translateX(15px)}.az-section-tabs{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:14px}.az-tab{border:1px solid #e4e7ec;border-radius:8px;background:#fff;padding:6px 9px;color:#667085;font-size:9px;font-weight:800;cursor:pointer}.az-tab.active{background:#f0efff;border-color:#cbc8ff;color:#5148d8}.az-tab-panel[hidden]{display:none}.az-actions{position:sticky;bottom:10px;z-index:15;display:flex;justify-content:flex-end;gap:8px;padding:10px;border:1px solid #e4e7ec;border-radius:12px;background:rgba(255,255,255,.96);box-shadow:0 8px 24px rgba(16,24,40,.08);backdrop-filter:blur(8px)}@media(max-width:980px){.az-editor-grid{grid-template-columns:1fr}.az-actions{position:static}}@media(max-width:620px){.az-post-head{flex-direction:column}.az-two,.az-status-grid{grid-template-columns:1fr}.az-actions{flex-direction:column-reverse}.az-actions .az-btn{width:100%}}



.az-revision-list{display:flex;flex-direction:column;gap:8px}

.az-revision-item{border:1px solid #eaecf0;border-radius:9px;background:#fcfcfd;overflow:hidden}

.az-revision-summary{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 10px;cursor:pointer;list-style:none}

.az-revision-summary::-webkit-details-marker{display:none}

.az-revision-main{min-width:0;display:flex;align-items:center;gap:8px}

.az-revision-icon{width:28px;height:28px;flex:0 0 28px;border-radius:8px;background:#f0efff;color:#635bff;display:grid;place-items:center;font-size:10px}

.az-revision-copy{min-width:0}

.az-revision-copy strong{display:block;color:#344054;font-size:10px}

.az-revision-copy span{display:block;margin-top:2px;color:#98a2b3;font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.az-revision-chevron{color:#98a2b3;font-size:9px;transition:transform .15s}

.az-revision-item[open] .az-revision-chevron{transform:rotate(180deg)}

.az-revision-body{padding:0 10px 10px;border-top:1px solid #eef0f3}

.az-revision-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;padding-top:9px}

.az-revision-field{min-width:0;padding:7px 8px;border:1px solid #eef0f3;border-radius:7px;background:#fff}

.az-revision-field-wide{grid-column:1/-1}

.az-revision-field span{display:block;margin-bottom:3px;color:#98a2b3;font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}

.az-revision-field strong,.az-revision-field p{margin:0;color:#475467;font-size:9px;line-height:1.45;word-break:break-word}

.az-revision-empty{padding:4px 0;color:#98a2b3;font-size:9px;line-height:1.5}

@media(max-width:620px){.az-revision-grid{grid-template-columns:1fr}}
.az-compare-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:9px;padding-top:9px;border-top:1px solid #eef0f3}
.az-compare-head strong{color:#344054;font-size:9px}.az-compare-count{padding:3px 6px;border-radius:999px;background:#f0efff;color:#5148d8;font-size:8px;font-weight:800}
.az-change-list{display:flex;flex-direction:column;gap:7px;margin-top:7px}.az-change{border:1px solid #e4e7ec;border-radius:8px;overflow:hidden;background:#fff}
.az-change-title{padding:6px 8px;background:#f9fafb;border-bottom:1px solid #eef0f3;color:#344054;font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.az-change-values{display:grid;grid-template-columns:1fr 1fr}.az-change-side{min-width:0;padding:7px 8px}.az-change-side+.az-change-side{border-left:1px solid #eef0f3}
.az-change-side span{display:block;margin-bottom:3px;font-size:7px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}.az-change-old span{color:#b42318}.az-change-new span{color:#027a48}
.az-change-side p{margin:0;color:#475467;font-size:9px;line-height:1.45;white-space:pre-wrap;word-break:break-word}.az-no-changes{margin-top:7px;padding:8px;border:1px dashed #d0d5dd;border-radius:8px;color:#667085;font-size:9px}
@media(max-width:620px){.az-change-values{grid-template-columns:1fr}.az-change-side+.az-change-side{border-left:0;border-top:1px solid #eef0f3}}

</style>







<div class="az-post-editor" data-post-editing="{{ $editing ? '1' : '0' }}">



    <header class="az-post-head">



        <div>



            <div class="az-eyebrow">Content Management / Blog</div>



            <h1>{{ $editing ? 'Edit Post' : 'Create Post' }}</h1>



            <p>{{ $editing ? 'Manage content, publishing, categories and search visibility.' : 'Create a structured, search-ready article for Arizona Outfits.' }}</p>



        </div>



        <a href="{{ route('admin.posts.index') }}" class="az-btn"><i class="fa-solid fa-arrow-left"></i> Back to Posts</a>



    </header>







    <form action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" data-post-form>



        @csrf



        @if($editing) @method('PUT') @endif







        <div class="az-editor-grid">



            <div class="az-stack">



                <section class="az-panel">



                    <div class="az-panel-head">



                        <div><h2>Article</h2><p class="az-panel-desc">The main content and URL identity of this post.</p></div>



                    </div>







                    <div class="az-field">



                        <label class="az-label" for="title"><span>Title <span class="az-required">*</span></span></label>



                        <input class="az-control az-input" id="title" type="text" name="title" value="{{ old('title', $editing ? $post->title : '') }}" required data-title>



                        @error('title')<span class="az-error">{{ $message }}</span>@enderror



                    </div>







                    <div class="az-field">



                        <label class="az-label" for="slug"><span>URL Slug</span></label>



                        <div class="az-slug">



                            <span class="az-slug-prefix">/blogs/</span>



                            <input id="slug" type="text" name="slug" value="{{ old('slug', $editing ? $post->slug : '') }}" data-slug>



                        </div>



                        <span class="az-help">Follows the title until you manually edit it. The server also guarantees uniqueness.</span>



                        @error('slug')<span class="az-error">{{ $message }}</span>@enderror



                    </div>







                    <div class="az-field">



                        <label class="az-label" for="excerpt"><span>Excerpt</span><span class="az-counter" data-count-for="excerpt"></span></label>



                        <textarea class="az-control az-textarea" id="excerpt" name="excerpt" maxlength="1000" data-count>{{ old('excerpt', $editing ? $post->excerpt : '') }}</textarea>



                        <span class="az-help">Concise article summary for cards, previews and editorial use.</span>



                        @error('excerpt')<span class="az-error">{{ $message }}</span>@enderror



                    </div>







                    <div class="az-field">



                        <label class="az-label" for="content"><span>Article Content</span></label>



                        <textarea class="az-control az-textarea az-content" id="content" name="content" placeholder="Write or paste the article content here...">{{ old('content', $editing ? $post->content : '') }}</textarea>



                        <span class="az-help">Main article body. We will connect this to the public blog renderer in the later public SEO phase.</span>



                        @error('content')<span class="az-error">{{ $message }}</span>@enderror



                    </div>







                    <details>



                        <summary style="cursor:pointer;color:#667085;font-size:10px;font-weight:800">Legacy compatibility fields</summary>



                        <div style="padding-top:12px">



                            <div class="az-field">



                                <label class="az-label" for="expert"><span>Legacy Expert / Summary</span></label>



                                <textarea class="az-control az-textarea" id="expert" name="expert">{{ old('expert', $editing ? $post->expert : '') }}</textarea>



                            </div>



                            <div class="az-field">



                                <label class="az-label" for="template"><span>Custom Article Template</span></label>



                                <input class="az-control az-input" id="template" type="text" name="template" value="{{ old('template', $editing ? $post->template : '') }}">



                                <span class="az-help">Use the file name from resources/views/blogs/posts, without .blade.php. Published and scheduled articles require this file.</span>



                            </div>



                        </div>



                    </details>



                </section>







                <section class="az-panel">



                    <div class="az-panel-head">



                        <div><h2>Search & Social</h2><p class="az-panel-desc">Control search snippets, canonical behavior and social sharing metadata.</p></div>



                    </div>







                    <div class="az-section-tabs" role="tablist">



                        <button type="button" class="az-tab active" data-tab="seo">SEO</button>



                        <button type="button" class="az-tab" data-tab="social">Social</button>



                        <button type="button" class="az-tab" data-tab="robots">Robots</button>



                    </div>







                    <div class="az-tab-panel" data-panel="seo">



                        <div class="az-field">



                            <label class="az-label" for="meta_title"><span>Meta Title</span><span class="az-counter" data-count-for="meta_title"></span></label>



                            <input class="az-control az-input" id="meta_title" type="text" name="meta_title" maxlength="255" value="{{ old('meta_title', $editing ? $post->meta_title : '') }}" data-count>



                            @error('meta_title')<span class="az-error">{{ $message }}</span>@enderror



                        </div>



                        <div class="az-field">



                            <label class="az-label" for="meta_description"><span>Meta Description</span><span class="az-counter" data-count-for="meta_description"></span></label>



                            <textarea class="az-control az-textarea" id="meta_description" name="meta_description" data-count>{{ old('meta_description', $editing ? $post->meta_description : '') }}</textarea>



                            @error('meta_description')<span class="az-error">{{ $message }}</span>@enderror



                        </div>



                        <div class="az-field">



                            <label class="az-label" for="canonical_url"><span>Canonical URL</span></label>



                            <input class="az-control az-input" id="canonical_url" type="url" name="canonical_url" value="{{ old('canonical_url', $editing ? $post->canonical_url : '') }}" placeholder="https\\://arizonaoutfits.com/blogs/example">



                            <span class="az-help">Leave empty to use the article's own canonical URL later.</span>



                            @error('canonical_url')<span class="az-error">{{ $message }}</span>@enderror



                        </div>



                    </div>







                    <div class="az-tab-panel" data-panel="social" hidden>



                        <div class="az-field">



                            <label class="az-label" for="og_title"><span>Social Title</span></label>



                            <input class="az-control az-input" id="og_title" type="text" name="og_title" value="{{ old('og_title', $editing ? $post->og_title : '') }}">



                        </div>



                        <div class="az-field">



                            <label class="az-label" for="og_description"><span>Social Description</span></label>



                            <textarea class="az-control az-textarea" id="og_description" name="og_description">{{ old('og_description', $editing ? $post->og_description : '') }}</textarea>



                        </div>



                        <div class="az-field">



                            <label class="az-label"><span>Social Image</span></label>



                            <div class="az-upload">



                                <div class="az-upload-preview" data-preview="og">



                                    @if($ogImageUrl)



                                        <img src="{{ $ogImageUrl }}" alt="Social image preview">



                                    @else



                                        <div class="az-upload-empty"><i class="fa-regular fa-images"></i>Optional social sharing image</div>



                                    @endif



                                </div>



                                <div class="az-upload-actions">



                                    <input class="az-file-native" id="og_image_upload" type="file" name="og_image_upload" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-file="og">



                                    <label class="az-btn" for="og_image_upload"><i class="fa-solid fa-arrow-up-from-bracket"></i> Choose Image</label>



                                    <span class="az-upload-name" data-file-name="og">No new file selected</span>



                                </div>



                            </div>



                            <input class="az-control az-input" style="margin-top:8px" type="text" name="og_image" value="{{ old('og_image', $editing ? $post->og_image : '') }}" placeholder="Optional existing image path or URL">



                            @if($editing && $post->og_image)



                                <label class="az-remove-check"><input type="checkbox" name="remove_og_image" value="1"> Remove current social image</label>



                            @endif



                        </div>



                    </div>







                    <div class="az-tab-panel" data-panel="robots" hidden>



                        <div class="az-switch-row">



                            <div class="az-switch-copy"><strong>Allow indexing</strong><span>Permit search engines to index this article.</span></div>



                            <label class="az-switch">



                                <input type="hidden" name="robots_index" value="0">



                                <input type="checkbox" name="robots_index" value="1" @checked((bool) old('robots_index', $editing ? $post->robots_index : true))>



                                <span></span>



                            </label>



                        </div>



                        <div class="az-switch-row">



                            <div class="az-switch-copy"><strong>Follow links</strong><span>Permit crawlers to follow links on this article.</span></div>



                            <label class="az-switch">



                                <input type="hidden" name="robots_follow" value="0">



                                <input type="checkbox" name="robots_follow" value="1" @checked((bool) old('robots_follow', $editing ? $post->robots_follow : true))>



                                <span></span>



                            </label>



                        </div>



                    </div>



                </section>



            </div>







            <aside class="az-stack">



                <section class="az-panel">



                    <div class="az-panel-head"><div><h2>Publishing</h2><p class="az-panel-desc">Control article visibility and timing.</p></div></div>



                    <div class="az-status-grid">



                        @foreach($statuses as $value => $label)



                            @php



                                $icon = match($value) {



                                    'published' => 'fa-circle-check',



                                    'scheduled' => 'fa-clock',



                                    'archived' => 'fa-box-archive',



                                    default => 'fa-file-pen',



                                };



                            @endphp



                            <label class="az-status-option">



                                <input type="radio" name="status" value="{{ $value }}" @checked($statusValue === $value) data-status>



                                <span class="az-status-card"><i class="fa-solid {{ $icon }}"></i>{{ $label }}</span>



                            </label>



                        @endforeach



                    </div>



                    @error('status')<span class="az-error">{{ $message }}</span>@enderror
                    @error('template')<span class="az-error">{{ $message }}</span>@enderror







                    <div class="az-date-fields">



                        <div class="az-field" data-publish-date>



                            <label class="az-label" for="published_at"><span>Published Date</span></label>



                            <input class="az-control az-input" id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $editing && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">



                            <span class="az-help">Optional. If publishing now, the server can set the current time.</span>



                        </div>



                        <div class="az-field" data-schedule-date>



                            <label class="az-label" for="scheduled_at"><span>Scheduled Date <span class="az-required">*</span></span></label>



                            <input class="az-control az-input" id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $editing && $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '') }}">



                            @error('scheduled_at')<span class="az-error">{{ $message }}</span>@enderror



                        </div>



                    </div>



                    @if($editing && $post->author)



                        <span class="az-help"><i class="fa-regular fa-user"></i> Author: {{ $post->author->name }}</span>



                    @endif



                </section>







                <section class="az-panel">



                    <div class="az-panel-head"><div><h2>Featured Image</h2><p class="az-panel-desc">JPG, PNG or WebP up to 5 MB.</p></div></div>



                    <div class="az-upload">



                        <div class="az-upload-preview" data-preview="feature">



                            @if($imageUrl)



                                <img src="{{ $imageUrl }}" alt="{{ old('feature_image_alt', $editing ? ($post->feature_image_alt ?: $post->title) : 'Featured image') }}">



                            @else



                                <div class="az-upload-empty"><i class="fa-regular fa-image"></i>No featured image selected</div>



                            @endif



                        </div>



                        <div class="az-upload-actions">



                            <input class="az-file-native" id="feature_image_upload" type="file" name="feature_image_upload" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-file="feature">



                            <label class="az-btn" for="feature_image_upload"><i class="fa-solid fa-arrow-up-from-bracket"></i> Choose Image</label>



                            <span class="az-upload-name" data-file-name="feature">No new file selected</span>



                        </div>



                    </div>



                    @error('feature_image_upload')<span class="az-error">{{ $message }}</span>@enderror







                    <div class="az-field" style="margin-top:10px">



                        <label class="az-label" for="feature_image_alt"><span>Image Alt Text</span></label>



                        <input class="az-control az-input" id="feature_image_alt" type="text" name="feature_image_alt" value="{{ old('feature_image_alt', $editing ? $post->feature_image_alt : '') }}">



                    </div>



                    <div class="az-field">



                        <label class="az-label" for="feature_image"><span>Existing / Manual Path</span></label>



                        <input class="az-control az-input" id="feature_image" type="text" name="feature_image" value="{{ old('feature_image', $editing ? $post->feature_image : '') }}" placeholder="posts/example.webp">



                    </div>



                    @if($editing && $post->feature_image)



                        <label class="az-remove-check"><input type="checkbox" name="remove_feature_image" value="1"> Remove current featured image</label>



                    @endif



                </section>







                <section class="az-panel">



                    <div class="az-panel-head"><div><h2>Categories</h2><p class="az-panel-desc">Select all relevant categories, then choose one primary category.</p></div></div>



                    <div class="az-tree">



                        @if($categories->isNotEmpty())



                            @php $renderCategoryChecks($categories); @endphp



                        @else



                            <div class="az-tree-empty">No blog categories available.</div>



                        @endif



                    </div>



                    @error('categories.*')<span class="az-error">{{ $message }}</span>@enderror







                    <div class="az-primary-wrap">



                        <div class="az-primary-title"><span>Primary Category</span><button type="button" class="az-primary-clear" data-clear-primary>Clear</button></div>



                        <div class="az-tree">



                            @if($categories->isNotEmpty())



                                @php $renderPrimaryOptions($categories); @endphp



                            @else



                                <div class="az-tree-empty">No categories available.</div>



                            @endif



                        </div>



                        <span class="az-help">Choosing a primary category automatically includes it in the article's selected categories.</span>



                        @error('primary_category_id')<span class="az-error">{{ $message }}</span>@enderror



                    </div>



                </section>





                @if($editing)
                    <section class="az-panel">
                        <div class="az-panel-head">
                            <div>
                                <h2>Revision History</h2>
                                <p class="az-panel-desc">Previous saved versions and what changed afterward.</p>
                            </div>
                            @if($post->revisions->isNotEmpty())
                                <span class="az-eyebrow">{{ $post->revisions->count() }} saved</span>
                            @endif
                        </div>

                        @if($post->revisions->isNotEmpty())
                            <div class="az-revision-list">
                                @foreach($post->revisions as $revision)
                                    @php
                                        $snapshot = is_array($revision->snapshot) ? $revision->snapshot : [];
                                        $revisionPost = isset($snapshot['post']) && is_array($snapshot['post']) ? $snapshot['post'] : [];
                                        $categoryIds = isset($snapshot['category_ids']) && is_array($snapshot['category_ids']) ? array_map('intval', $snapshot['category_ids']) : [];

                                        $flattenCategories = function ($nodes) use (&$flattenCategories) {
                                            $flat = collect();
                                            foreach ($nodes as $node) {
                                                $flat->push($node);
                                                if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {
                                                    $flat = $flat->merge($flattenCategories($node->childrenRecursive));
                                                }
                                            }
                                            return $flat;
                                        };

                                        $allCategories = $flattenCategories($categories);
                                        $oldCategoryNames = $allCategories->whereIn('id', $categoryIds)->pluck('title')->values()->all();
                                        $currentCategoryNames = $post->categories->pluck('title')->values()->all();

                                        $formatDateValue = function ($value) {
                                            if (empty($value)) return '—';
                                            try {
                                                return \Illuminate\Support\Carbon::parse($value)->format('M j, Y · g:i A');
                                            } catch (\Throwable $e) {
                                                return (string) $value;
                                            }
                                        };

                                        $displayValue = function ($value) {
                                            if (is_bool($value)) return $value ? 'Yes' : 'No';
                                            if ($value === null || $value === '') return '—';
                                            return (string) $value;
                                        };

                                        $compareFields = [
                                            'Title' => [$revisionPost['title'] ?? null, $post->title],
                                            'Slug' => [$revisionPost['slug'] ?? null, $post->slug],
                                            'Status' => [$revisionPost['status'] ?? null, $post->status],
                                            'Published Date' => [$formatDateValue($revisionPost['published_at'] ?? null), $formatDateValue($post->published_at)],
                                            'Scheduled Date' => [$formatDateValue($revisionPost['scheduled_at'] ?? null), $formatDateValue($post->scheduled_at)],
                                            'Excerpt' => [$revisionPost['excerpt'] ?? null, $post->excerpt],
                                            'Article Content' => [$revisionPost['content'] ?? null, $post->content],
                                            'Featured Image' => [$revisionPost['feature_image'] ?? null, $post->feature_image],
                                            'Featured Image Alt' => [$revisionPost['feature_image_alt'] ?? null, $post->feature_image_alt],
                                            'Template' => [$revisionPost['template'] ?? null, $post->template],
                                            'Primary Category' => [
                                                optional($allCategories->firstWhere('id', (int) ($revisionPost['primary_category_id'] ?? 0)))->title,
                                                optional($post->primaryCategory)->title
                                            ],
                                            'Categories' => [count($oldCategoryNames) ? implode(', ', $oldCategoryNames) : 'None', count($currentCategoryNames) ? implode(', ', $currentCategoryNames) : 'None'],
                                            'Meta Title' => [$revisionPost['meta_title'] ?? null, $post->meta_title],
                                            'Meta Description' => [$revisionPost['meta_description'] ?? null, $post->meta_description],
                                            'Canonical URL' => [$revisionPost['canonical_url'] ?? null, $post->canonical_url],
                                            'Allow Indexing' => [(bool) ($revisionPost['robots_index'] ?? false), (bool) $post->robots_index],
                                            'Follow Links' => [(bool) ($revisionPost['robots_follow'] ?? false), (bool) $post->robots_follow],
                                            'Social Title' => [$revisionPost['og_title'] ?? null, $post->og_title],
                                            'Social Description' => [$revisionPost['og_description'] ?? null, $post->og_description],
                                            'Social Image' => [$revisionPost['og_image'] ?? null, $post->og_image],
                                        ];

                                        $changes = collect($compareFields)->filter(fn ($values) => $values[0] !== $values[1]);
                                    @endphp

                                    <details class="az-revision-item">
                                        <summary class="az-revision-summary">
                                            <span class="az-revision-main">
                                                <span class="az-revision-icon"><i class="fa-solid fa-code-compare"></i></span>
                                                <span class="az-revision-copy">
                                                    <strong>Revision #{{ $revision->revision_number }}</strong>
                                                    <span>{{ $revision->admin ? $revision->admin->name : 'Unknown admin' }} · {{ $revision->created_at ? $revision->created_at->format('M j, Y · g:i A') : 'Unknown date' }}</span>
                                                </span>
                                            </span>
                                            <i class="fa-solid fa-chevron-down az-revision-chevron"></i>
                                        </summary>

                                        <div class="az-revision-body">
                                            <div style="margin-top:10px;margin-bottom:10px">
                                                <button type="submit" class="az-btn az-btn-primary"
                                                    form="restore-revision-{{ $revision->id }}">
                                                    <i class="fa-solid fa-clock-rotate-left"></i> Restore this revision
                                                </button>
                                                <p class="az-panel-desc" style="margin-top:6px">
                                                    Your current saved version will stay in history. Unsaved edits will not be saved.
                                                </p>
                                            </div>
                                            <div class="az-compare-head">
                                                <strong>Changes vs current post</strong>
                                                <span class="az-compare-count">{{ $changes->count() }} changed</span>
                                            </div>

                                            @if($changes->isNotEmpty())
                                                <div class="az-change-list">
                                                    @foreach($changes as $label => $values)
                                                        <div class="az-change">
                                                            <div class="az-change-title">{{ $label }}</div>
                                                            <div class="az-change-values">
                                                                <div class="az-change-side az-change-old"><span>Previous</span><p>{{ $displayValue($values[0]) }}</p></div>
                                                                <div class="az-change-side az-change-new"><span>Current</span><p>{{ $displayValue($values[1]) }}</p></div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="az-no-changes"><i class="fa-regular fa-circle-check"></i> This saved revision matches the current post values being compared.</div>
                                            @endif
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        @else
                            <div class="az-revision-empty"><i class="fa-regular fa-clock"></i> No revisions yet. A revision is created when this post is updated.</div>
                        @endif
                    </section>
                @endif

</aside>



        </div>







        <div class="az-actions">



            <a href="{{ route('admin.posts.index') }}" class="az-btn">Cancel</a>



            <button type="submit" class="az-btn az-btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ $editing ? 'Save Changes' : 'Create Post' }}</button>



        </div>



    </form>



</div>







<script>



'use strict';



document.addEventListener('DOMContentLoaded', function () {



    const title = document.querySelector('[data-title]');



    const slug = document.querySelector('[data-slug]');



    const postEditor = document.querySelector('[data-post-editing]');
    let slugManual = postEditor?.dataset.postEditing === '1';







    const slugify = value => value.toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');







    slug?.addEventListener('input', () => { slugManual = true; });



    title?.addEventListener('input', () => { if (!slugManual && slug) slug.value = slugify(title.value); });







    document.querySelectorAll('[data-file]').forEach(input => {



        input.addEventListener('change', function () {



            const key = input.dataset.file;



            const file = input.files && input.files[0];



            if (!file) return;



            const name = document.querySelector('[data-file-name="' + key + '"]');



            const preview = document.querySelector('[data-preview="' + key + '"]');



            if (name) name.textContent = file.name;



            if (preview) {



                const reader = new FileReader();



                reader.onload = e => {



                    preview.innerHTML = '';



                    const img = document.createElement('img');



                    img.src = e.target.result;



                    img.alt = 'Image preview';



                    preview.appendChild(img);



                };



                reader.readAsDataURL(file);



            }



        });



    });







    const updateDates = () => {



        const status = document.querySelector('[data-status]:checked')?.value;



        const publish = document.querySelector('[data-publish-date]');



        const schedule = document.querySelector('[data-schedule-date]');



        if (publish) publish.style.display = status === 'published' ? '' : 'none';



        if (schedule) schedule.style.display = status === 'scheduled' ? '' : 'none';



    };



    document.querySelectorAll('[data-status]').forEach(input => input.addEventListener('change', updateDates));



    updateDates();







    document.querySelectorAll('[data-primary-radio]').forEach(radio => {



        radio.addEventListener('change', function () {



            const matching = document.querySelector('[data-category-checkbox][value="' + radio.value + '"]');



            if (matching) matching.checked = true;



        });



    });



    document.querySelector('[data-clear-primary]')?.addEventListener('click', function () {



        document.querySelectorAll('[data-primary-radio]').forEach(radio => radio.checked = false);



    });







    document.querySelectorAll('[data-tab]').forEach(tab => {



        tab.addEventListener('click', function () {



            document.querySelectorAll('[data-tab]').forEach(item => item.classList.remove('active'));



            document.querySelectorAll('[data-panel]').forEach(panel => panel.hidden = true);



            tab.classList.add('active');



            const panel = document.querySelector('[data-panel="' + tab.dataset.tab + '"]');



            if (panel) panel.hidden = false;



        });



    });







    document.querySelectorAll('[data-count]').forEach(field => {



        const target = document.querySelector('[data-count-for="' + field.id + '"]');



        if (!target) return;



        const update = () => {



            const max = field.getAttribute('maxlength');



            target.textContent = field.value.length + (max ? ' / ' + max : '') + ' characters';



        };



        field.addEventListener('input', update);



        update();



    });



});



</script>
