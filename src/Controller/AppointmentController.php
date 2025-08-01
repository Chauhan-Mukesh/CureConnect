<?php

declare(strict_types=1);

/**
 * Appointment Controller
 *
 * Handles appointment management including creation, updating, deletion,
 * and viewing of medical appointments.
 *
 * @package CureConnect\Controller
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Controller;

use CureConnect\Core\Request;
use CureConnect\Core\Security;
use CureConnect\Controller\BaseController;
use CureConnect\Models\Appointment;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for managing medical appointments
 */
class AppointmentController extends BaseController
{
    /**
     * Display appointments listing
     */
    public function index(): Response
    {
        try {
            $appointmentModel = new Appointment($this->app->getDatabase());
            $appointments = $appointmentModel->getAll();
            
            return $this->render('appointments/index', [
                'appointments' => $appointments,
                'title' => 'Appointments'
            ]);
        } catch (\Exception $e) {
            error_log('Error fetching appointments: ' . $e->getMessage());
            return $this->render('error', [
                'message' => 'Unable to load appointments at this time.'
            ]);
        }
    }

    /**
     * Show single appointment
     */
    public function show(): Response
    {
        $id = (int) $this->request->query->get('id', 0);
        
        if ($id <= 0) {
            return $this->render('error', ['message' => 'Invalid appointment ID'], 400);
        }

        try {
            $appointmentModel = new Appointment($this->app->getDatabase());
            $appointment = $appointmentModel->findById($id);

            if (!$appointment) {
                return $this->render('error', ['message' => 'Appointment not found'], 404);
            }

            return $this->render('appointments/show', [
                'appointment' => $appointment,
                'title' => 'Appointment Details'
            ]);
        } catch (\Exception $e) {
            error_log('Error fetching appointment: ' . $e->getMessage());
            return $this->render('error', [
                'message' => 'Unable to load appointment details.'
            ], 500);
        }
    }

    /**
     * Create a new appointment
     */
    public function create(): Response
    {
        if ($this->request->isMethod('POST')) {
            // Validate CSRF token
            $token = $this->request->request->get('csrf_token', '');
            if (!Security::verifyCsrfToken($token)) {
                return $this->render('error', ['message' => 'Invalid CSRF token'], 403);
            }

            try {
                $appointmentModel = new Appointment($this->app->getDatabase());
                $data = $this->sanitizeAppointmentData($this->request->request->all());
                
                if ($appointmentModel->validate($data) && $appointmentModel->create($data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Appointment created successfully'];
                    return $this->redirect('/appointments');
                }
                
                return $this->render('appointments/create', [
                    'data' => $data,
                    'errors' => $appointmentModel->getErrors(),
                    'csrf_token' => Security::generateCsrfToken(),
                    'title' => 'Create Appointment'
                ]);
            } catch (\Exception $e) {
                error_log('Error creating appointment: ' . $e->getMessage());
                return $this->render('appointments/create', [
                    'data' => $this->request->request->all(),
                    'errors' => ['general' => 'Unable to create appointment. Please try again.'],
                    'csrf_token' => Security::generateCsrfToken(),
                    'title' => 'Create Appointment'
                ]);
            }
        }

        return $this->render('appointments/create', [
            'data' => [],
            'errors' => [],
            'csrf_token' => Security::generateCsrfToken(),
            'title' => 'Create Appointment'
        ]);
    }

    /**
     * Update an existing appointment
     */
    public function update(): Response
    {
        $id = (int) $this->request->query->get('id', 0);
        
        if ($id <= 0) {
            return $this->render('error', ['message' => 'Invalid appointment ID'], 400);
        }

        try {
            $appointmentModel = new Appointment($this->app->getDatabase());
            $appointment = $appointmentModel->findById($id);

            if (!$appointment) {
                return $this->render('error', ['message' => 'Appointment not found'], 404);
            }

            if ($this->request->isMethod('POST')) {
                // Validate CSRF token
                $token = $this->request->request->get('csrf_token', '');
                if (!Security::verifyCsrfToken($token)) {
                    return $this->render('error', ['message' => 'Invalid CSRF token'], 403);
                }

                $data = $this->sanitizeAppointmentData($this->request->request->all());
                
                if ($appointmentModel->validate($data) && $appointmentModel->update($id, $data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Appointment updated successfully'];
                    return $this->redirect('/appointments');
                }
                
                return $this->render('appointments/update', [
                    'appointment' => array_merge($appointment, $data),
                    'errors' => $appointmentModel->getErrors(),
                    'csrf_token' => Security::generateCsrfToken(),
                    'title' => 'Update Appointment'
                ]);
            }

            return $this->render('appointments/update', [
                'appointment' => $appointment,
                'errors' => [],
                'csrf_token' => Security::generateCsrfToken(),
                'title' => 'Update Appointment'
            ]);
        } catch (\Exception $e) {
            error_log('Error updating appointment: ' . $e->getMessage());
            return $this->render('error', [
                'message' => 'Unable to update appointment details.'
            ], 500);
        }
    }

    /**
     * Delete an appointment
     */
    public function delete(): Response
    {
        $id = (int) $this->request->query->get('id', 0);
        
        if ($id <= 0) {
            return $this->render('error', ['message' => 'Invalid appointment ID'], 400);
        }

        if ($this->request->isMethod('POST')) {
            // Validate CSRF token
            $token = $this->request->request->get('csrf_token', '');
            if (!Security::verifyCsrfToken($token)) {
                return $this->render('error', ['message' => 'Invalid CSRF token'], 403);
            }

            try {
                $appointmentModel = new Appointment($this->app->getDatabase());
                
                if ($appointmentModel->delete($id)) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Appointment deleted successfully'];
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unable to delete appointment'];
                }
            } catch (\Exception $e) {
                error_log('Error deleting appointment: ' . $e->getMessage());
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unable to delete appointment'];
            }
        }

        return $this->redirect('/appointments');
    }

    /**
     * Sanitize appointment data input
     *
     * @param array $data Raw input data
     * @return array Sanitized data
     */
    private function sanitizeAppointmentData(array $data): array
    {
        return [
            'patient_name' => Security::sanitizeInput($data['patient_name'] ?? ''),
            'patient_email' => filter_var($data['patient_email'] ?? '', FILTER_SANITIZE_EMAIL),
            'patient_phone' => Security::sanitizeInput($data['patient_phone'] ?? ''),
            'appointment_date' => Security::sanitizeInput($data['appointment_date'] ?? ''),
            'appointment_time' => Security::sanitizeInput($data['appointment_time'] ?? ''),
            'doctor_id' => (int) ($data['doctor_id'] ?? 0),
            'service_type' => Security::sanitizeInput($data['service_type'] ?? ''),
            'notes' => Security::sanitizeInput($data['notes'] ?? ''),
            'status' => Security::sanitizeInput($data['status'] ?? 'pending')
        ];
    }
}
