<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    /**
     * Show contact page.
     */
    public function index()
    {
        return view('pages.public.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function send(Request $request)
    {
        // Rate limiting: max 3 messages per hour per IP
        $key = 'contact-form:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Trop de messages envoyés. Réessayez dans {$seconds} secondes.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:20|max:2000',
            'type' => 'required|in:general,support,partnership,demo',
        ], [
            'name.required' => 'Veuillez entrer votre nom.',
            'email.required' => 'Veuillez entrer votre email.',
            'email.email' => 'Veuillez entrer un email valide.',
            'subject.required' => 'Veuillez entrer un sujet.',
            'message.required' => 'Veuillez entrer votre message.',
            'message.min' => 'Votre message doit contenir au moins 20 caractères.',
            'type.required' => 'Veuillez sélectionner un type de demande.',
        ]);

        RateLimiter::hit($key, 3600); // 1 hour

        // Get contact email from settings or use default
        $contactEmail = SystemSetting::get('contact_email', config('mail.from.address', 'contact@menupro.com'));

        try {
            // Send email
            Mail::send('emails.contact', [
                'data' => $validated,
            ], function ($message) use ($validated, $contactEmail) {
                $message->to($contactEmail)
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('[MenuPro Contact] ' . $validated['subject']);
            });

            return back()->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.')->withInput();
        }
    }
}
