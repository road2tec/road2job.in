<?php

namespace App\Controllers;

use App\Models\ContactMessage;
use App\Services\MailService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class ContactController extends Controller
{
    public function show(Request $request): void
    {
        $this->view('contact/show', [
            'meta' => [
                'title' => 'Contact Us - Road2Job',
                'description' => 'Get in touch with the Road2Job team.',
            ],
        ], 'marketing');
    }

    public function submit(Request $request): void
    {
        $data = $request->only(['name', 'email', 'subject', 'message']);

        $validator = Validator::make($data);
        $validator->validate([
            'name' => 'required|min:2|max:150',
            'email' => 'required|email',
            'subject' => 'required|min:3|max:150',
            'message' => 'required|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('_old', $data);
            $this->redirect('/contact');
            return;
        }

        ContactMessage::submit($data['name'], $data['email'], $data['subject'], $data['message']);

        $adminEmail = config('mail.from_address');
        $body = '<p>New contact form submission from <strong>' . e($data['name']) . '</strong> (' . e($data['email']) . ')</p>'
            . '<p><strong>Subject:</strong> ' . e($data['subject']) . '</p>'
            . '<p>' . nl2br(e($data['message'])) . '</p>';

        (new MailService())->send(
            $adminEmail,
            'Road2Job Admin',
            'New contact message: ' . $data['subject'],
            $body,
            $data['email'],
            $data['name']
        );

        Session::flash('success', "Thanks for reaching out, {$data['name']}. We'll get back to you soon.");
        $this->redirect('/contact');
    }
}
