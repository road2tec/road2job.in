<?php

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class EventController extends Controller
{
    protected const CATEGORIES = ['hackathon', 'job-fair', 'seminar', 'webinar'];
    protected const ORGANIZER_ROLES = ['employer', 'institute', 'college', 'mentor'];

    // ---- Public ----

    public function index(Request $request): void
    {
        $category = (string) $request->input('category', '');
        $category = in_array($category, self::CATEGORIES, true) ? $category : null;
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = Event::publicListing($category, $page, $perPage);

        $this->view('pages/events_public', [
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'activeCategory' => $category,
            'categories' => self::CATEGORIES,
            'meta' => [
                'title' => 'Events - Road2Job',
                'description' => 'Hackathons, job fairs, seminars and webinars hosted on Road2Job.',
            ],
        ], 'marketing');
    }

    public function show(Request $request, string $id): void
    {
        $event = Event::findPublished((int) $id);

        if ($event === null) {
            Response::abort(404);
            return;
        }

        $sessionUser = Session::get('_user');
        $isRegistered = $sessionUser !== null && EventRegistration::hasRegistered((int) $id, (int) $sessionUser['id']);

        $this->view('pages/event_show', [
            'event' => $event,
            'isLoggedIn' => $sessionUser !== null,
            'isRegistered' => $isRegistered,
            'meta' => [
                'title' => $event['title'] . ' - Road2Job',
                'description' => 'Event: ' . $event['title'] . ' hosted by ' . $event['organizer_name'] . '.',
            ],
        ], 'marketing');
    }

    /**
     * Reached from a public page. Inline auth check rather than route-group
     * middleware, since the GET pages around it are public and any role
     * (not just students) can register.
     */
    public function register(Request $request, string $eventId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in to register for this event.');
            $this->redirect('/login');
            return;
        }

        $event = Event::findPublished((int) $eventId);

        if ($event === null) {
            Session::flash('error', 'That event is not available.');
            $this->redirect('/events');
            return;
        }

        $userId = (int) $sessionUser['id'];

        if (EventRegistration::hasRegistered((int) $eventId, $userId)) {
            Session::flash('error', 'You are already registered for this event.');
            $this->redirect('/events/' . $eventId);
            return;
        }

        EventRegistration::create((int) $eventId, $userId);

        Session::flash('success', 'You are registered! See your registrations under Events in your dashboard.');
        $this->redirect('/events/' . $eventId);
    }

    // ---- Shared dashboard ----

    public function hub(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $roles = config('roles');
        $layout = $roles[$sessionUser['role_slug']]['layout'] ?? 'student';
        $isOrganizer = in_array($sessionUser['role_slug'], self::ORGANIZER_ROLES, true);

        $this->view('dashboard/events/hub', [
            'user' => $sessionUser,
            'isOrganizer' => $isOrganizer,
            'registrations' => EventRegistration::forUser((int) $sessionUser['id']),
            'hostedEvents' => $isOrganizer ? Event::forOrganizer((int) $sessionUser['id']) : [],
            'categories' => self::CATEGORIES,
        ], $layout);
    }

    public function certificate(Request $request, string $eventId): void
    {
        $sessionUser = Session::get('_user');
        $registration = EventRegistration::findForEventAndUser((int) $eventId, (int) $sessionUser['id']);

        if ($registration === null || $registration['status'] !== 'attended') {
            Response::abort(404);
            return;
        }

        $event = Event::find((int) $eventId);

        $this->view('dashboard/events/certificate', [
            'user' => $sessionUser,
            'event' => $event,
        ], 'certificate');
    }

    // ---- Organizer-only ----

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['organizer_user_id'] = (int) $sessionUser['id'];
        $data['is_online'] = $request->input('is_online') ? 1 : 0;

        Event::insert($data);

        Session::flash('success', 'Event created.');
        $this->redirect('/dashboard/events');
    }

    public function update(Request $request, string $id): void
    {
        $event = $this->ownedEvent($id);

        if ($event === null) {
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['is_online'] = $request->input('is_online') ? 1 : 0;
        Event::update((int) $id, $data);

        Session::flash('success', 'Event updated.');
        $this->redirect('/dashboard/events');
    }

    public function destroy(Request $request, string $id): void
    {
        $event = $this->ownedEvent($id);

        if ($event !== null) {
            Event::delete((int) $id);
            Session::flash('success', 'Event deleted.');
        }

        $this->redirect('/dashboard/events');
    }

    public function registrations(Request $request, string $eventId): void
    {
        $event = $this->ownedEvent($eventId);

        if ($event === null) {
            return;
        }

        $this->view('dashboard/events/registrations', [
            'user' => Session::get('_user'),
            'event' => $event,
            'registrations' => EventRegistration::forEvent((int) $eventId),
        ], $this->layoutForSession());
    }

    public function markAttended(Request $request, string $registrationId): void
    {
        $registration = EventRegistration::find((int) $registrationId);

        if ($registration === null || $this->ownedEvent((string) $registration['event_id']) === null) {
            return;
        }

        EventRegistration::markAttended((int) $registrationId);

        Session::flash('success', 'Marked attended.');
        $this->redirect('/dashboard/events/' . $registration['event_id'] . '/registrations');
    }

    protected function fields(): array
    {
        return ['category', 'title', 'description', 'location', 'starts_at', 'ends_at', 'status'];
    }

    protected function rules(): array
    {
        return [
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'title' => 'required|min:2|max:200',
            'starts_at' => 'required',
            'ends_at' => '',
            'status' => 'required|in:draft,published,completed',
        ];
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only($this->fields());

        $validator = Validator::make($data);
        $validator->validate($this->rules());

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/events');
            return null;
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    protected function ownedEvent(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $event = Event::find((int) $id);

        if ($event === null || (int) $event['organizer_user_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That event could not be found.');
            $this->redirect('/dashboard/events');
            return null;
        }

        return $event;
    }

    protected function layoutForSession(): string
    {
        $sessionUser = Session::get('_user');
        $roles = config('roles');

        return $roles[$sessionUser['role_slug']]['layout'] ?? 'student';
    }
}
