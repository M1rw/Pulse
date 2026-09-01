<?php
/**
 * Admin Controller.
 * 
 * Simple CRUD for projects. Nothing fancy, but it works.
 * The admin area is where I manage everything.
 */
namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Http\Requests\Validator;

class AdminController
{
    /** admin dashboard */
    public function dashboard(): string
    {
        $stats = [
            'projects'    => Project::count(),
            'messages'    => ContactMessage::count(),
            'unread'      => count(ContactMessage::unread()->all()),
            'activity'    => ActivityLog::stats(),
        ];
        $recent = ActivityLog::recent(10);
        $messages = ContactMessage::unread()->take(5);

        return view('pages.admin.dashboard', [
            'stats'    => $stats,
            'activity' => $recent,
            'messages' => $messages,
        ]);
    }

    /** list all projects (admin) */
    public function projects(): string
    {
        $projects = Project::query("SELECT * FROM projects ORDER BY created_at DESC");
        return view('pages.admin.projects', ['projects' => $projects]);
    }

    /** create project form */
    public function createProject(): string
    {
        return view('pages.admin.project-form', ['project' => null]);
    }

    /** store new project */
    public function storeProject(Request $request): Response
    {
        $data = $request->all();
        $data['slug'] = str_slug($data['title'] ?? 'untitled');

        $validator = Validator::make($data, [
            'title'       => 'required|min:2|max:200',
            'slug'        => 'required|slug',
            'description' => 'required|min:10',
            'category'    => 'required|max:50',
            'status'      => 'in:draft,published,archived',
        ]);

        try {
            $validated = $validator->validate();
        } catch (\App\Core\Exceptions\ValidationException $e) {
            flash('errors', $e->getErrors());
            flash('old', $data);
            return Response::redirect('/admin/projects/new');
        }

        $validated['featured']  = ($data['featured'] ?? 0) ? 1 : 0;
        $validated['sort_order']= (int)($data['sort_order'] ?? 0);
        $validated['tech_stack']= $data['tech_stack'] ?? '';
        $validated['live_url']  = $data['live_url'] ?? '';
        $validated['source_url']= $data['source_url'] ?? '';
        $validated['long_description'] = $data['long_description'] ?? '';
        $validated['thumbnail'] = $data['thumbnail'] ?? '';

        $project = new Project($validated);
        $project->save();

        ActivityLog::log('create_project', "Created project: {$project->title}", [
            'project_id' => $project->id,
        ], $_SERVER['REMOTE_ADDR'] ?? '');

        flash('success', 'Project published.');
        return Response::redirect('/admin/projects');
    }

    /** edit project form */
    public function editProject(Request $request): string
    {
        $id = (int) $request->routeParam('id');
        $project = Project::find($id);
        if (!$project) throw new \App\Core\Exceptions\NotFoundException();
        return view('pages.admin.project-form', ['project' => $project]);
    }

    /** update project */
    public function updateProject(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $project = Project::find($id);
        if (!$project) throw new \App\Core\Exceptions\NotFoundException();

        $data = $request->all();
        $data['slug'] = str_slug($data['title'] ?? 'untitled');

        $validator = Validator::make($data, [
            'title'       => 'required|min:2|max:200',
            'slug'        => 'required|slug',
            'description' => 'required|min:10',
            'category'    => 'required|max:50',
            'status'      => 'in:draft,published,archived',
        ]);

        try {
            $validated = $validator->validate();
        } catch (\App\Core\Exceptions\ValidationException $e) {
            flash('errors', $e->getErrors());
            flash('old', $data);
            return Response::redirect("/admin/projects/{$id}/edit");
        }

        foreach ($validated as $key => $val) {
            $project->$key = $val;
        }
        $project->featured  = ($data['featured'] ?? 0) ? 1 : 0;
        $project->sort_order= (int)($data['sort_order'] ?? 0);
        $project->tech_stack= $data['tech_stack'] ?? '';
        $project->live_url  = $data['live_url'] ?? '';
        $project->source_url= $data['source_url'] ?? '';
        $project->long_description = $data['long_description'] ?? '';
        $project->thumbnail = $data['thumbnail'] ?? '';
        $project->save();

        flash('success', 'Project updated.');
        return Response::redirect('/admin/projects');
    }

    /** delete project */
    public function deleteProject(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $project = Project::find($id);
        if (!$project) throw new \App\Core\Exceptions\NotFoundException();

        $title = $project->title;
        $project->delete();

        ActivityLog::log('delete_project', "Deleted project: {$title}", [], $_SERVER['REMOTE_ADDR'] ?? '');

        flash('success', 'Project deleted.');
        return Response::redirect('/admin/projects');
    }

    /** messages list */
    public function messages(): string
    {
        $messages = \App\Models\ContactMessage::query(
            "SELECT * FROM contact_messages ORDER BY created_at DESC"
        );
        return view('pages.admin.messages', ['messages' => $messages]);
    }

    /** mark message as read */
    public function markMessageRead(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        ContactMessage::markRead($id);
        flash('success', 'Message marked as read.');
        return Response::redirect('/admin/messages');
    }

    /** mark message as unread */
    public function markMessageUnread(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        ContactMessage::markUnread($id);
        flash('success', 'Message marked as unread.');
        return Response::redirect('/admin/messages');
    }

    /** delete message */
    public function deleteMessage(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $msg = ContactMessage::find($id);
        if ($msg) {
            $msg->delete();
            flash('success', 'Message deleted.');
        }
        return Response::redirect('/admin/messages');
    }

    /** API: toggle project featured status */
    public function toggleFeatured(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $project = Project::find($id);
        if (!$project) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $newVal = $project->featured ? 0 : 1;
        $project->featured = $newVal;
        $project->save();

        return Response::json(['featured' => $newVal]);
    }

    /** API: get activity stats */
    public function apiActivityStats(): Response
    {
        return Response::json(ActivityLog::stats());
    }
}
