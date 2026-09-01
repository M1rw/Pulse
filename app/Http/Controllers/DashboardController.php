<?php
/**
 * Dashboard Controller.
 * 
 * The main page. Shows my projects, activity feed,
 * some stats, and proves this isn't just scaffolding.
 */
namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Project;
use App\Models\ActivityLog;

class DashboardController
{
    /** home page - the hero */
    public function index(): string
    {
        // log this visit (but not too aggressively)
        $this->throttledLog();

        $projects = Project::featured()->take(6);
        $stats    = $this->computeStats();
        $activity = ActivityLog::recent(8);
        $categories = Project::categories();

        return view('pages.home', [
            'projects'   => $projects,
            'stats'      => $stats,
            'activity'   => $activity,
            'categories' => $categories,
        ]);
    }

    /** projects listing page */
    public function projects(): string
    {
        $category = $_GET['cat'] ?? null;
        $search   = $_GET['q'] ?? null;

        if ($search) {
            $projects = Project::search($search);
        } elseif ($category) {
            $projects = Project::byCategory($category);
        } else {
            $projects = Project::published();
        }

        $categories = Project::categories();

        return view('pages.projects', [
            'projects'   => $projects,
            'categories' => $categories,
            'activeCat'  => $category,
            'search'     => $search,
        ]);
    }

    /** single project detail */
    public function showProject(string $slug): string
    {
        $project = Project::firstWhere('slug', $slug);

        if (!$project || $project->status !== 'published') {
            throw new \App\Core\Exceptions\NotFoundException();
        }

        ActivityLog::log('view_project', "Viewed project: {$project->title}", [
            'project_id' => $project->id,
            'slug'       => $slug,
        ], $_SERVER['REMOTE_ADDR'] ?? '');

        return view('pages.project-detail', [
            'project' => $project,
        ]);
    }

    /** about page */
    public function about(): string
    {
        return view('pages.about');
    }

    /** contact form page */
    public function contact(): string
    {
        return view('pages.contact');
    }

    /** handle contact form submission */
    public function submitContact(Request $request): Response
    {
        $validator = \App\Http\Requests\Validator::make(
            $request->all(),
            [
                'name'    => 'required|min:2|max:100',
                'email'   => 'required|email',
                'subject' => 'required|min:3|max:200',
                'message' => 'required|min:10|max:5000',
            ]
        );

        try {
            $data = $validator->validate();
        } catch (\App\Core\Exceptions\ValidationException $e) {
            flash('errors', $e->getErrors());
            flash('old', $request->all());
            redirect('/contact');
        }

        // save message
        $msg = new \App\Models\ContactMessage([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'ip_address' => $request->ip(),
        ]);
        $msg->save();

        ActivityLog::log('contact', "New message from {$data['email']}", [
            'subject' => $data['subject'],
        ], $request->ip());

        flash('success', 'Message sent. I read every single one.');
        return Response::redirect('/contact');
    }

    /** API: get stats as JSON */
    public function apiStats(): Response
    {
        return Response::json([
            'projects'  => Project::count(),
            'activity'  => ActivityLog::stats(),
            'uptime'    => $this->getUptime(),
            'php'       => PHP_VERSION,
            'memory'    => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        ]);
    }

    /** API: live activity feed */
    public function apiActivity(): Response
    {
        return Response::json(ActivityLog::recent(15)->toArray());
    }

    /** API: search projects */
    public function apiSearch(Request $request): Response
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) {
            return Response::json([]);
        }
        $results = Project::search($q);
        return Response::json($results->map(fn($p) => [
            'title'   => $p['title'],
            'slug'    => $p['slug'],
            'excerpt' => (new Project($p))->excerpt(),
            'category'=> $p['category'],
        ])->toArray());
    }

    // ── internals ───────────────────────────────────────────────

    private function computeStats(): array
    {
        return [
            'projects'    => Project::count(),
            'categories'  => count(Project::categories()),
            'messages'    => \App\Models\ContactMessage::count(),
            'uptime'      => $this->getUptime(),
        ];
    }

    private function getUptime(): string
    {
        // site uptime since first deploy (just use current month as approximation)
        $start = new \DateTime('2025-01-15');
        $now   = new \DateTime();
        $diff  = $start->diff($now);
        $parts = [];
        if ($diff->y) $parts[] = $diff->y . 'y';
        if ($diff->m) $parts[] = $diff->m . 'mo';
        if ($diff->d) $parts[] = $diff->d . 'd';
        return implode(' ', $parts) ?: 'just launched';
    }

    /** don't log every single page refresh, be smart about it */
    private function throttledLog(): void
    {
        $key = 'pulse_last_visit_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $lastVisit = $_SESSION[$key] ?? 0;
        
        if (time() - $lastVisit > 60) { // once per minute per IP
            ActivityLog::log('visit', 'Page visit', [
                'path'  => $_SERVER['REQUEST_URI'] ?? '/',
                'agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ], $_SERVER['REMOTE_ADDR'] ?? '');
            $_SESSION[$key] = time();
        }
    }
}
