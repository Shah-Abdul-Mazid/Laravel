@extends('layouts.app')  {{-- Create a basic layout if needed --}}
@section('content')
<div class="container mt-4">
    <h1>Create Course</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Course Fields --}}
        <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" name="category" id="category" class="form-control" value="{{ old('category') }}">
        </div>

        <div class="mb-3">
            <label for="feature_video" class="form-label">Feature Video</label>
            <input type="file" name="feature_video" id="feature_video" class="form-control" accept="video/*">
        </div>

        {{-- Modules Container --}}
        <div id="modules-container">
            <h3>Modules <button type="button" id="add-module" class="btn btn-primary btn-sm">+ Add Module</button></h3>
            {{-- Initial Module --}}
            <div class="module-row border p-3 mb-3" data-index="0">
                <h4>Module 1</h4>
                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="modules[0][title]" class="form-control" value="{{ old('modules.0.title') }}" required>
                </div>

                <div id="contents-container-0">
                    <h5>Contents <button type="button" class="btn btn-secondary btn-sm add-content" data-module-index="0">+ Add Content</button></h5>
                    {{-- Initial Content --}}
                    <div class="content-row border p-2 mb-2" data-cont-index="0">
                        <div class="mb-2">
                            <label class="form-label">Type *</label>
                            <select name="modules[0][contents][0][type]" class="form-control content-type" required>
                                <option value="">Select Type</option>
                                <option value="text" {{ old('modules.0.contents.0.type') == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="image" {{ old('modules.0.contents.0.type') == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="video" {{ old('modules.0.contents.0.type') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="link" {{ old('modules.0.contents.0.type') == 'link' ? 'selected' : '' }}>Link</option>
                            </select>
                        </div>

                        {{-- Conditional Fields --}}
                        <div class="text-field mb-2" style="display: none;">
                            <label class="form-label">Text</label>
                            <textarea name="modules[0][contents][0][text]" class="form-control" placeholder="Enter text...">{{ old('modules.0.contents.0.text') }}</textarea>
                        </div>
                        <div class="link-field mb-2" style="display: none;">
                            <label class="form-label">Link</label>
                            <input type="url" name="modules[0][contents][0][link]" class="form-control" placeholder="https://example.com" value="{{ old('modules.0.contents.0.link') }}">
                        </div>
                        <div class="image-field mb-2" style="display: none;">
                            <label class="form-label">Image</label>
                            <input type="file" name="modules[0][contents][0][image]" class="form-control" accept="image/*">
                        </div>
                        <div class="video-field mb-2" style="display: none;">
                            <label class="form-label">Video</label>
                            <input type="file" name="modules[0][contents][0][video]" class="form-control" accept="video/*">
                        </div>

                        <button type="button" class="btn btn-danger btn-sm remove-content">Remove Content</button>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-module">Remove Module</button>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Create Course</button>
    </form>
</div>

<script>
    let moduleIndex = 1;
    let contentIndex = {};  // Per-module content count
    contentIndex[0] = 1;

    // Add Module
    document.getElementById('add-module').addEventListener('click', () => {
        const container = document.getElementById('modules-container');
        const newModule = document.createElement('div');
        newModule.className = 'module-row border p-3 mb-3';
        newModule.dataset.index = moduleIndex;
        newModule.innerHTML = `
            <h4>Module ${moduleIndex + 1}</h4>
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="modules[${moduleIndex}][title]" class="form-control" required>
            </div>
            <div id="contents-container-${moduleIndex}">
                <h5>Contents <button type="button" class="btn btn-secondary btn-sm add-content" data-module-index="${moduleIndex}">+ Add Content</button></h5>
                <div class="content-row border p-2 mb-2" data-cont-index="0">
                    <div class="mb-2">
                        <label class="form-label">Type *</label>
                        <select name="modules[${moduleIndex}][contents][0][type]" class="form-control content-type" required>
                            <option value="">Select Type</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="link">Link</option>
                        </select>
                    </div>
                    <div class="text-field mb-2" style="display: none;">
                        <label class="form-label">Text</label>
                        <textarea name="modules[${moduleIndex}][contents][0][text]" class="form-control" placeholder="Enter text..."></textarea>
                    </div>
                    <div class="link-field mb-2" style="display: none;">
                        <label class="form-label">Link</label>
                        <input type="url" name="modules[${moduleIndex}][contents][0][link]" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="image-field mb-2" style="display: none;">
                        <label class="form-label">Image</label>
                        <input type="file" name="modules[${moduleIndex}][contents][0][image]" class="form-control" accept="image/*">
                    </div>
                    <div class="video-field mb-2" style="display: none;">
                        <label class="form-label">Video</label>
                        <input type="file" name="modules[${moduleIndex}][contents][0][video]" class="form-control" accept="video/*">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-content">Remove Content</button>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-module">Remove Module</button>
        `;
        container.appendChild(newModule);
        contentIndex[moduleIndex] = 1;
        moduleIndex++;
        attachEventListeners();  // Re-attach for new elements
    });

    // Add Content
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-content')) {
            const modIdx = e.target.dataset.moduleIndex;
            const contContainer = document.getElementById(`contents-container-${modIdx}`);
            const contIdx = contentIndex[modIdx]++;
            const newContent = document.createElement('div');
            newContent.className = 'content-row border p-2 mb-2';
            newContent.dataset.contIndex = contIdx;
            newContent.innerHTML = `
                <div class="mb-2">
                    <label class="form-label">Type *</label>
                    <select name="modules[${modIdx}][contents][${contIdx}][type]" class="form-control content-type" required>
                        <option value="">Select Type</option>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="link">Link</option>
                    </select>
                </div>
                <div class="text-field mb-2" style="display: none;">
                    <label class="form-label">Text</label>
                    <textarea name="modules[${modIdx}][contents][${contIdx}][text]" class="form-control" placeholder="Enter text..."></textarea>
                </div>
                <div class="link-field mb-2" style="display: none;">
                    <label class="form-label">Link</label>
                    <input type="url" name="modules[${modIdx}][contents][${contIdx}][link]" class="form-control" placeholder="https://example.com">
                </div>
                <div class="image-field mb-2" style="display: none;">
                    <label class="form-label">Image</label>
                    <input type="file" name="modules[${modIdx}][contents][${contIdx}][image]" class="form-control" accept="image/*">
                </div>
                <div class="video-field mb-2" style="display: none;">
                    <label class="form-label">Video</label>
                    <input type="file" name="modules[${modIdx}][contents][${contIdx}][video]" class="form-control" accept="video/*">
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-content">Remove Content</button>
            `;
            contContainer.appendChild(newContent);
            attachEventListeners();  // Re-attach
        } else if (e.target.classList.contains('remove-content')) {
            e.target.closest('.content-row').remove();
        } else if (e.target.classList.contains('remove-module')) {
            e.target.closest('.module-row').remove();
        }
    });

    // Type Change Toggle
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('content-type')) {
            const row = e.target.closest('.content-row');
            const type = e.target.value;
            row.querySelectorAll('.text-field, .link-field, .image-field, .video-field').forEach(f => f.style.display = 'none');
            if (type === 'text') row.querySelector('.text-field').style.display = 'block';
            else if (type === 'link') row.querySelector('.link-field').style.display = 'block';
            else if (type === 'image') row.querySelector('.image-field').style.display = 'block';
            else if (type === 'video') row.querySelector('.video-field').style.display = 'block';
        }
    });

    // Initial Listeners (for dynamic re-attachment)
    function attachEventListeners() {
        // Re-attach type changes if needed (already on document)
    }
    attachEventListeners();
</script>
@endsection