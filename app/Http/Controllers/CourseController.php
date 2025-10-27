<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class CourseController extends Controller
{
    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        // Debug: Temporarily uncomment to inspect request
        // dd($request->all());  // Remove after testing

        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'feature_video' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
                'modules' => 'required|array|min:1',
                'modules.*.title' => 'required|string|max:255',
                'modules.*.contents' => 'required|array|min:1',
                'modules.*.contents.*.type' => ['required', Rule::in(['text', 'image', 'video', 'link'])],
                // Robust rules: nullable allows null, required_if enforces presence for specific types
                'modules.*.contents.*.text' => [
                    'nullable',
                    'required_if:modules.*.contents.*.type,text',
                    'string',
                    'max:65535'
                ],
                'modules.*.contents.*.link' => [
                    'nullable',
                    'required_if:modules.*.contents.*.type,link',
                    'url',
                    'max:500'
                ],
                'modules.*.contents.*.image' => [
                    'nullable',
                    'required_if:modules.*.contents.*.type,image',
                    'file',
                    'mimes:jpeg,png,jpg,gif',
                    'max:5120'
                ],
                'modules.*.contents.*.video' => [
                    'nullable',
                    'required_if:modules.*.contents.*.type,video',
                    'file',
                    'mimes:mp4,avi,mov',
                    'max:10240'
                ],
            ], [
                'modules.required' => 'At least one module is required.',
                'modules.min' => 'Each module must have at least one content.',
                'modules.*.contents.*.text.required_if' => 'Text is required for text-type content.',
                'modules.*.contents.*.text.string' => 'Text content must be a valid string.',
                'modules.*.contents.*.link.required_if' => 'Link is required for link-type content.',
                'modules.*.contents.*.image.required_if' => 'Image file is required for image-type content.',
                'modules.*.contents.*.video.required_if' => 'Video file is required for video-type content.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $course = DB::transaction(function () use ($request) {
                $courseData = $request->only(['title', 'description', 'category']);
                if ($request->hasFile('feature_video')) {
                    $courseData['feature_video'] = $request->file('feature_video')->store('videos', 'public');
                }
                $course = Course::create($courseData);

                $moduleOrder = 1;
                foreach ($request->modules as $modIndex => $module) {
                    $moduleModel = Module::create([
                        'course_id' => $course->id,
                        'title' => $module['title'],
                        'order' => $moduleOrder++
                    ]);

                    $contentOrder = 1;
                    foreach ($module['contents'] as $contIndex => $content) {
                        $contentData = [
                            'module_id' => $moduleModel->id,
                            'type' => $content['type'],
                            'order' => $contentOrder++
                        ];

                        switch ($content['type']) {
                            case 'text':
                                $textValue = $content['text'] ?? '';
                                // Safeguard: Ensure string, log if invalid
                                if (!is_string($textValue) && $textValue !== null) {
                                    Log::warning("Invalid text type for content {$contIndex} in module {$modIndex}: " . gettype($textValue));
                                    $textValue = '';
                                }
                                $contentData['data'] = $textValue;
                                break;
                            case 'link':
                                $linkValue = $content['link'] ?? '';
                                // Safeguard: Ensure string, log if invalid
                                if (!is_string($linkValue) && $linkValue !== null) {
                                    Log::warning("Invalid link type for content {$contIndex} in module {$modIndex}: " . gettype($linkValue));
                                    $linkValue = '';
                                }
                                $contentData['data'] = $linkValue;
                                break;
                            case 'image':
                                $fileKey = "modules.{$modIndex}.contents.{$contIndex}.image";
                                if ($request->hasFile($fileKey)) {
                                    $contentData['data'] = $request->file($fileKey)->store('media', 'public');
                                } else {
                                    // For image type, require file—throw if missing (validation should catch, but safeguard)
                                    throw new Exception("Missing image file for content {$contIndex} in module {$modIndex}");
                                }
                                break;
                            case 'video':
                                $fileKey = "modules.{$modIndex}.contents.{$contIndex}.video";
                                if ($request->hasFile($fileKey)) {
                                    $contentData['data'] = $request->file($fileKey)->store('media', 'public');
                                } else {
                                    // For video type, require file—throw if missing
                                    throw new Exception("Missing video file for content {$contIndex} in module {$modIndex}");
                                }
                                break;
                            default:
                                throw new Exception("Invalid content type: {$content['type']} for content {$contIndex}");
                        }
                        Content::create($contentData);
                    }
                }
                return $course;
            });

            Log::info('Course created successfully', ['id' => $course->id]);

            return redirect()->route('courses.show', $course->id)->with('success', 'Course created successfully!');

        } catch (Exception $e) {
            Log::error('Course creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_keys' => array_keys($request->all())
            ]);
            return back()->with('error', 'An error occurred while creating the course: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Course $course)
    {
        $course->load('modules.contents');
        return view('course.show', compact('course'));
    }
}