<?php

declare(strict_types=1);

/**
 * Appointment Model
 *
 * Handles database operations for medical appointments including CRUD operations,
 * validation, and appointment management.
 *
 * @package CureConnect\Models
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Models;

use CureConnect\Core\Security;
use PDO;
use PDOException;

/**
 * Appointment model for managing medical appointments
 */
class Appointment
{
    private PDO $db;
    private array $errors = [];

    /**
     * Appointment constructor
     *
     * @param PDO $database Database connection
     */
    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    /**
     * Get all appointments
     *
     * @return array Array of appointments
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    p.email as patient_email,
                    p.phone as patient_phone,
                    d.name as doctor_name,
                    s.name as service_name
                FROM appointments a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN services s ON a.service_id = s.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching appointments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find appointment by ID
     *
     * @param int $id Appointment ID
     * @return array|null Appointment data or null if not found
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    p.email as patient_email,
                    p.phone as patient_phone,
                    d.name as doctor_name,
                    s.name as service_name
                FROM appointments a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN services s ON a.service_id = s.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Error fetching appointment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new appointment
     *
     * @param array $data Appointment data
     * @return bool Success status
     */
    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Insert or get patient
            $patientId = $this->getOrCreatePatient($data);
            if (!$patientId) {
                $this->db->rollBack();
                return false;
            }

            // Insert appointment
            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    patient_id, doctor_id, service_id, appointment_date, 
                    appointment_time, status, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $result = $stmt->execute([
                $patientId,
                $data['doctor_id'] ?? null,
                $data['service_id'] ?? null,
                $data['appointment_date'],
                $data['appointment_time'],
                $data['status'] ?? 'pending',
                $data['notes'] ?? ''
            ]);

            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error creating appointment: ' . $e->getMessage());
            $this->errors['general'] = 'Unable to create appointment';
            return false;
        }
    }

    /**
     * Update an existing appointment
     *
     * @param int $id Appointment ID
     * @param array $data Updated appointment data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Update or create patient
            $appointment = $this->findById($id);
            if (!$appointment) {
                $this->db->rollBack();
                return false;
            }

            $patientId = $this->getOrCreatePatient($data, $appointment['patient_id']);
            if (!$patientId) {
                $this->db->rollBack();
                return false;
            }

            // Update appointment
            $stmt = $this->db->prepare("
                UPDATE appointments SET
                    patient_id = ?, doctor_id = ?, service_id = ?, appointment_date = ?,
                    appointment_time = ?, status = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");

            $result = $stmt->execute([
                $patientId,
                $data['doctor_id'] ?? null,
                $data['service_id'] ?? null,
                $data['appointment_date'],
                $data['appointment_time'],
                $data['status'] ?? 'pending',
                $data['notes'] ?? '',
                $id
            ]);

            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error updating appointment: ' . $e->getMessage());
            $this->errors['general'] = 'Unable to update appointment';
            return false;
        }
    }

    /**
     * Delete an appointment
     *
     * @param int $id Appointment ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM appointments WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('Error deleting appointment: ' . $e->getMessage());
            $this->errors['general'] = 'Unable to delete appointment';
            return false;
        }
    }

    /**
     * Validate appointment data
     *
     * @param array $data Appointment data
     * @return bool Validation status
     */
    public function validate(array $data): bool
    {
        $this->errors = [];

        // Validate patient name
        if (empty($data['patient_name'])) {
            $this->errors['patient_name'] = 'Patient name is required';
        }

        // Validate patient email
        if (empty($data['patient_email'])) {
            $this->errors['patient_email'] = 'Patient email is required';
        } elseif (!filter_var($data['patient_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['patient_email'] = 'Please enter a valid email address';
        }

        // Validate patient phone
        if (empty($data['patient_phone'])) {
            $this->errors['patient_phone'] = 'Patient phone is required';
        } elseif (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $data['patient_phone'])) {
            $this->errors['patient_phone'] = 'Please enter a valid phone number';
        }

        // Validate appointment date
        if (empty($data['appointment_date'])) {
            $this->errors['appointment_date'] = 'Appointment date is required';
        } elseif (!$this->isValidDate($data['appointment_date'])) {
            $this->errors['appointment_date'] = 'Please enter a valid date';
        } elseif (strtotime($data['appointment_date']) < strtotime('today')) {
            $this->errors['appointment_date'] = 'Appointment date cannot be in the past';
        }

        // Validate appointment time
        if (empty($data['appointment_time'])) {
            $this->errors['appointment_time'] = 'Appointment time is required';
        } elseif (!$this->isValidTime($data['appointment_time'])) {
            $this->errors['appointment_time'] = 'Please enter a valid time';
        }

        // Validate service type
        if (!empty($data['service_type']) && !in_array($data['service_type'], [
            'consultation', 'surgery', 'treatment', 'diagnostic', 'follow-up'
        ])) {
            $this->errors['service_type'] = 'Please select a valid service type';
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get or create patient from appointment data
     *
     * @param array $data Appointment data
     * @param int|null $existingPatientId Existing patient ID for updates
     * @return int|null Patient ID or null on failure
     */
    private function getOrCreatePatient(array $data, ?int $existingPatientId = null): ?int
    {
        try {
            // Try to find existing patient by email
            $stmt = $this->db->prepare("SELECT id FROM patients WHERE email = ?");
            $stmt->execute([$data['patient_email']]);
            $existingPatient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingPatient) {
                // Update existing patient
                $stmt = $this->db->prepare("
                    UPDATE patients SET 
                        first_name = ?, last_name = ?, phone = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $names = explode(' ', $data['patient_name'], 2);
                $stmt->execute([
                    $names[0],
                    $names[1] ?? '',
                    $data['patient_phone'],
                    $existingPatient['id']
                ]);
                return (int) $existingPatient['id'];
            } else {
                // Create new patient
                $stmt = $this->db->prepare("
                    INSERT INTO patients (first_name, last_name, email, phone, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $names = explode(' ', $data['patient_name'], 2);
                $stmt->execute([
                    $names[0],
                    $names[1] ?? '',
                    $data['patient_email'],
                    $data['patient_phone']
                ]);
                return (int) $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log('Error managing patient: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate date format
     *
     * @param string $date Date string
     * @return bool Valid date
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate time format
     *
     * @param string $time Time string
     * @return bool Valid time
     */
    private function isValidTime(string $time): bool
    {
        $t = \DateTime::createFromFormat('H:i', $time);
        return $t && $t->format('H:i') === $time;
    }
}