<?php

declare(strict_types=1);

/**
 * Home Controller
 *
 * @package CureConnect\Controller
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for homepage and main landing page
 */
class HomeController extends BaseController
{
    /**
     * Display homepage
     *
     * @return Response
     */
    public function index(): Response
    {
        $metaTags = $this->generateMetaTags(
            $this->trans('World-Class Healthcare in India'),
            $this->trans('Experience affordable, high-quality medical treatments with our comprehensive medical tourism services. Connect with top hospitals and specialists across India.'),
            'medical tourism india, healthcare india, cost savings, treatments',
            $this->app->getConfig()['app']['assets_url'] . '/images/hero-medical-tourism.jpg'
        );

        // Sample statistics data (in production, fetch from database)
        $statistics = [
            'medical_tourists' => 7300000,
            'cost_savings' => 70,
            'hospitals' => 500,
            'countries' => 156
        ];

        // Featured treatments data
        $featuredTreatments = [
            [
                'title' => $this->trans('Cardiology'),
                'description' => $this->trans('Advanced cardiac procedures including bypass surgery, angioplasty, and valve replacement with 95%+ success rates.'),
                'icon' => 'fas fa-heartbeat',
                'india_cost' => 300000,
                'usa_cost' => 2500000,
                'savings' => 88
            ],
            [
                'title' => $this->trans('Orthopedics'),
                'description' => $this->trans('Joint replacement, spine surgery, and sports medicine with cutting-edge technology and rehabilitation.'),
                'icon' => 'fas fa-bone',
                'india_cost' => 200000,
                'usa_cost' => 1800000,
                'savings' => 89
            ],
            [
                'title' => $this->trans('Oncology'),
                'description' => $this->trans('Comprehensive cancer treatment including chemotherapy, radiation therapy, and surgical oncology.'),
                'icon' => 'fas fa-user-md',
                'india_cost' => 500000,
                'usa_cost' => 3500000,
                'savings' => 86
            ]
        ];

        return $this->render('pages/home.html.twig', [
            'meta' => $metaTags,
            'statistics' => $statistics,
            'featured_treatments' => $featuredTreatments,
            'body_class' => 'home-page'
        ]);
    }
}
