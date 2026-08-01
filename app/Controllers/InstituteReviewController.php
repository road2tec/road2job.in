<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteReview;
use Core\Controller;
use Core\Request;
use Core\Session;

class InstituteReviewController extends Controller
{
    public function store(Request $request, string $instituteId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to leave a review.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can leave reviews.');
            $this->redirect('/institutes/' . $instituteId);
            return;
        }

        $userId = (int) $sessionUser['id'];
        $ownInstitute = Institute::findByUserId($userId);

        if ($ownInstitute !== null && (int) $ownInstitute['id'] === (int) $instituteId) {
            Session::flash('error', 'You cannot review your own institute.');
            $this->redirect('/institutes/' . $instituteId);
            return;
        }

        if (InstituteReview::findByInstituteAndUser((int) $instituteId, $userId) !== null) {
            Session::flash('error', 'You have already reviewed this institute.');
            $this->redirect('/institutes/' . $instituteId);
            return;
        }

        $rating = (int) $request->input('rating', 0);

        if ($rating < 1 || $rating > 5) {
            Session::flash('error', 'Please select a rating between 1 and 5.');
            $this->redirect('/institutes/' . $instituteId);
            return;
        }

        $reviewText = trim((string) $request->input('review_text', ''));

        InstituteReview::create((int) $instituteId, $userId, $rating, $reviewText !== '' ? $reviewText : null);

        Session::flash('success', 'Thanks for your review!');
        $this->redirect('/institutes/' . $instituteId);
    }
}
