<?php

declare(strict_types=1);

/**
 * Page Controller
 *
 * @package CureConnect\Controller
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Controller;

use CureConnect\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for static pages (About, Contact, Gallery, Government Schemes)
 */
class PageController extends BaseController
{
    /**
     * Display about page
     *
     * @return Response
     */
    public function about(): Response
    {
        $metaTags = $this->generateMetaTags(
            $this->trans('About CureConnect - Leading Medical Tourism Platform in India'),
            $this->trans('Learn about CureConnect\'s mission to connect international patients with India\'s world-class healthcare providers.'),
            'medical tourism india, about cureconnect, healthcare india, medical visa'
        );

        return $this->render('pages/about.html.twig', [
            'meta' => $metaTags,
            'body_class' => 'about-page'
        ]);
    }

    /**
     * Display contact page and handle form submission
     *
     * @return Response
     */
    public function contact(): Response
    {
        $message = null;
        $messageType = null;

        // Handle contact form submission
        if ($this->request->isMethod('POST')) {
            $result = $this->handleContactForm();
            $message = $result['message'];
            $messageType = $result['type'];
        }

        $metaTags = $this->generateMetaTags(
            $this->trans('Contact CureConnect - Get Free Medical Tourism Consultation'),
            $this->trans('Contact CureConnect for personalized medical tourism assistance. Get free consultation, treatment cost estimates, and visa guidance.'),
            'contact medical tourism, free consultation india, medical visa help'
        );

        return $this->render('pages/contact.html.twig', [
            'meta' => $metaTags,
            'body_class' => 'contact-page',
            'message' => $message,
            'message_type' => $messageType,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Display gallery page
     *
     * @return Response
     */
    public function gallery(): Response
    {
        $metaTags = $this->generateMetaTags(
            $this->trans('Medical Tourism Gallery - Hospitals & Treatment Facilities in India'),
            $this->trans('Explore world-class medical facilities, hospitals, and treatment centers in India.'),
            'medical tourism gallery, hospitals india, medical facilities'
        );

        // Gallery categories
        $categories = [
            'all' => $this->trans('All Images'),
            'hospitals' => $this->trans('Hospitals'),
            'treatments' => $this->trans('Treatments'),
            'facilities' => $this->trans('Facilities'),
            'patient-rooms' => $this->trans('Patient Rooms'),
            'equipment' => $this->trans('Medical Equipment'),
            'doctors' => $this->trans('Doctors & Staff')
        ];

        // Sample gallery items (in production, fetch from database)
        $galleryItems = [
            [
                'id' => 1,
                'title' => $this->trans('Apollo Hospital Chennai - Main Building'),
                'category' => 'hospitals',
                'image' => $this->app->getConfig()['app']['assets_url'] . '/images/Gemini_Generated_Image_hcdewthcdewthcde.png',
                'thumbnail' => $this->app->getConfig()['app']['assets_url'] . '/images/logo_250x150.svg',
                'description' => $this->trans('State-of-the-art medical facility with 500+ beds'),
                'hospital' => 'Apollo Hospital Chennai'
            ],
            [
                'id' => 2,
                'title' => $this->trans('Cardiac Surgery Suite'),
                'category' => 'treatments',
                'image' => $this->app->getConfig()['app']['assets_url'] . '/images/Gemini_Generated_Image_hcdewthcdewthcde.png',
                'thumbnail' => $this->app->getConfig()['app']['assets_url'] . '/images/logo_100x100.svg',
                'description' => $this->trans('Advanced cardiac surgery operating theater'),
                'hospital' => 'Fortis Hospital Delhi'
            ]
        ];

        return $this->render('pages/gallery.html.twig', [
            'meta' => $metaTags,
            'body_class' => 'gallery-page',
            'categories' => $categories,
            'gallery_items' => $galleryItems
        ]);
    }

    /**
     * Display government schemes page
     *
     * @return Response
     */
    public function governmentSchemes(): Response
    {
        $metaTags = $this->generateMetaTags(
            $this->trans('Government Schemes & e-Medical Visa for Medical Tourism in India'),
            $this->trans('Learn about Indian government initiatives supporting medical tourism including e-Medical visa process.'),
            'government schemes medical tourism, e-medical visa india, heal in india'
        );

        // Government schemes data
        $schemes = [
            [
                'title' => $this->trans('Heal in India'),
                'description' => $this->trans('National initiative to position India as a global healthcare destination'),
                'icon' => 'fas fa-heart',
                'benefits' => [
                    $this->trans('Streamlined medical visa process'),
                    $this->trans('Quality assurance through accredited hospitals'),
                    $this->trans('24/7 helpline support for international patients'),
                    $this->trans('Promotional activities in target countries')
                ]
            ],
            [
                'title' => $this->trans('e-Medical Visa'),
                'description' => $this->trans('Online medical visa facility for 156+ countries'),
                'icon' => 'fas fa-passport',
                'benefits' => [
                    $this->trans('Online application process'),
                    $this->trans('Quick processing (72 hours)'),
                    $this->trans('Available for 156+ countries'),
                    $this->trans('Medical attendant visa facility')
                ]
            ]
        ];

        // Visa process steps
        $visaSteps = [
            [
                'step' => 1,
                'title' => $this->trans('Check Eligibility'),
                'description' => $this->trans('Verify if your country is eligible for e-Medical visa')
            ],
            [
                'step' => 2,
                'title' => $this->trans('Prepare Documents'),
                'description' => $this->trans('Gather required documents for visa application')
            ],
            [
                'step' => 3,
                'title' => $this->trans('Apply Online'),
                'description' => $this->trans('Complete the online visa application form')
            ]
        ];

        return $this->render('pages/government-schemes.html.twig', [
            'meta' => $metaTags,
            'body_class' => 'government-schemes-page',
            'schemes' => $schemes,
            'visa_steps' => $visaSteps
        ]);
    }

    /**
     * Display article page
     *
     * @param string $slug Article slug
     * @return Response
     */
    public function article(string $slug = 'welcome'): Response
    {
        $metaTags = $this->generateMetaTags(
            $this->trans('Medical Tourism Articles - ' . ucwords(str_replace('-', ' ', $slug))),
            $this->trans('Learn about medical tourism, healthcare in India, and treatment options.'),
            'medical tourism articles, healthcare india, treatment information'
        );

        // Sample article data (in production, fetch from database by slug)
        $article = [
            'title' => $this->trans('Welcome to CureConnect Medical Tourism'),
            'slug' => $slug,
            'content' => $this->trans('Discover world-class healthcare solutions in India with our comprehensive medical tourism services.'),
            'author' => 'CureConnect Team',
            'date' => date('Y-m-d'),
            'image' => $this->app->getConfig()['app']['assets_url'] . '/images/Gemini_Generated_Image_hcdewthcdewthcde.png'
        ];

        return $this->render('pages/article.html.twig', [
            'meta' => $metaTags,
            'body_class' => 'article-page',
            'article' => $article
        ]);
    }

    /**
     * Handle contact form submission
     *
     * @return array Result with message and type
     */
    private function handleContactForm(): array
    {
        $name = Security::sanitizeInput($this->request->request->get('name', ''));
        $email = Security::sanitizeInput($this->request->request->get('email', ''));
        $message = Security::sanitizeInput($this->request->request->get('message', ''));
        $csrfToken = $this->request->request->get('csrf_token', '');

        // Validate CSRF token
        if (!Security::verifyCsrfToken($csrfToken)) {
            return [
                'message' => $this->trans('Security token mismatch. Please try again.'),
                'type' => 'error'
            ];
        }

        // Validate required fields
        if (empty($name) || empty($email) || empty($message)) {
            return [
                'message' => $this->trans('Please fill in all required fields.'),
                'type' => 'error'
            ];
        }

        // Validate email
        if (!Security::validateEmail($email)) {
            return [
                'message' => $this->trans('Please provide a valid email address.'),
                'type' => 'error'
            ];
        }

        // TODO: Save to database and send email notification
        // For now, just log the inquiry
        error_log("New contact inquiry from: {$name} ({$email})");

        return [
            'message' => $this->trans('Thank you for your inquiry. We will contact you soon!'),
            'type' => 'success'
        ];
    }
}
