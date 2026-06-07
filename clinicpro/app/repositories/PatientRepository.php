<?php
// app/repositories/PatientRepository.php

class PatientRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $clinicId, array $filters = [], int $limit = PER_PAGE, int $offset = 0): array {
        $where = ['p.clinic_id = :cid'];
        $params = [':cid' => $clinicId];

        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :s OR p.phone LIKE :s OR p.patient_id LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['gender'])) {
            $where[] = 'p.gender = :gender';
            $params[':gender'] = $filters['gender'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }

        $w = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT * FROM patients p WHERE $w ORDER BY p.id DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(int $clinicId, array $filters = []): int {
        $where = ['clinic_id = :cid'];
        $params = [':cid' => $clinicId];
        if (!empty($filters['search'])) {
            $where[] = '(name LIKE :s OR phone LIKE :s OR patient_id LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        $w    = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM patients WHERE $w");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, int $clinicId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id=? AND clinic_id=? LIMIT 1");
        $stmt->execute([$id, $clinicId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $next = next_sequence('patients', 'patient_id', $data['clinic_id']);
        $data['patient_id'] = generate_id('PT', $next);
        $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k) => ":$k", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO patients ($cols) VALUES ($vals)");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $clinicId, array $data): bool {
        $sets  = implode(',', array_map(fn($k) => "`$k`=:$k", array_keys($data)));
        $stmt  = $this->db->prepare("UPDATE patients SET $sets WHERE id=:id AND clinic_id=:cid");
        $data['id']  = $id;
        $data['cid'] = $clinicId;
        return $stmt->execute($data);
    }

    public function getHistory(int $patientId, int $clinicId): array {
        $stmt = $this->db->prepare("
            SELECT v.*, d.name AS doctor_name, d.specialization
            FROM opd_visits v
            JOIN doctors d ON d.id = v.doctor_id
            WHERE v.patient_id=? AND v.clinic_id=?
            ORDER BY v.visit_date DESC
        ");
        $stmt->execute([$patientId, $clinicId]);
        return $stmt->fetchAll();
    }
}
