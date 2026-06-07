<?php
// app/repositories/AppointmentRepository.php

class AppointmentRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $clinicId, array $f = [], int $limit = PER_PAGE, int $offset = 0): array {
        $where  = ['a.clinic_id=:cid'];
        $params = [':cid' => $clinicId];

        if (!empty($f['date']))      { $where[] = 'a.appointment_date=:dt';   $params[':dt'] = $f['date']; }
        if (!empty($f['doctor_id'])) { $where[] = 'a.doctor_id=:did';         $params[':did'] = $f['doctor_id']; }
        if (!empty($f['status']))    { $where[] = 'a.status=:st';             $params[':st'] = $f['status']; }
        if (!empty($f['search']))    {
            $where[] = '(p.name LIKE :s OR p.phone LIKE :s OR a.appointment_no LIKE :s)';
            $params[':s'] = '%'.$f['search'].'%';
        }

        $w    = implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT a.*, p.name AS patient_name, p.phone AS patient_phone, p.patient_id,
                   d.name AS doctor_name, d.specialization
            FROM appointments a
            JOIN patients p ON p.id=a.patient_id
            JOIN doctors  d ON d.id=a.doctor_id
            WHERE $w ORDER BY a.appointment_date DESC, a.appointment_time ASC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(int $clinicId, array $f = []): int {
        $where  = ['a.clinic_id=:cid'];
        $params = [':cid' => $clinicId];
        if (!empty($f['date'])) { $where[] = 'a.appointment_date=:dt'; $params[':dt']=$f['date']; }
        if (!empty($f['status'])) { $where[] = 'a.status=:st'; $params[':st']=$f['status']; }
        $w    = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE $w");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id, int $clinicId): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, p.name AS patient_name, p.phone AS patient_phone, p.patient_id,
                   d.name AS doctor_name, d.specialization, d.consultation_fee
            FROM appointments a
            JOIN patients p ON p.id=a.patient_id
            JOIN doctors  d ON d.id=a.doctor_id
            WHERE a.id=? AND a.clinic_id=? LIMIT 1
        ");
        $stmt->execute([$id,$clinicId]);
        return $stmt->fetch() ?: null;
    }

    public function todayStats(int $clinicId): array {
        $today = date('Y-m-d');
        $stmt  = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(status='scheduled') AS scheduled,
                SUM(status='completed') AS completed,
                SUM(status='waiting')   AS waiting,
                SUM(status='cancelled') AS cancelled
            FROM appointments WHERE clinic_id=? AND appointment_date=?
        ");
        $stmt->execute([$clinicId, $today]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $next = next_sequence('appointments','appointment_no',$data['clinic_id']);
        $data['appointment_no'] = generate_id('APT',$next);

        // Token for the day+doctor
        $tok = $this->db->prepare("SELECT COUNT(*)+1 FROM appointments WHERE clinic_id=? AND doctor_id=? AND appointment_date=? AND status NOT IN('cancelled','no_show')");
        $tok->execute([$data['clinic_id'],$data['doctor_id'],$data['appointment_date']]);
        $data['token_no'] = (int)$tok->fetchColumn();

        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO appointments ($cols) VALUES ($vals)");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, int $clinicId, string $status, string $reason = ''): bool {
        $stmt = $this->db->prepare("UPDATE appointments SET status=?, cancelled_reason=?, updated_at=NOW() WHERE id=? AND clinic_id=?");
        return $stmt->execute([$status, $reason, $id, $clinicId]);
    }

    public function getAvailableSlots(int $doctorId, string $date): array {
        $dow  = (int) date('w', strtotime($date));
        $db   = $this->db;
        // Get schedule
        $sch  = $db->prepare("SELECT * FROM doctor_schedules WHERE doctor_id=? AND day_of_week=? AND is_active=1 LIMIT 1");
        $sch->execute([$doctorId,$dow]);
        $sched = $sch->fetch();
        if (!$sched) return [];

        // Existing appointments
        $booked = $db->prepare("SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=? AND status NOT IN('cancelled','no_show')");
        $booked->execute([$doctorId,$date]);
        $bookedTimes = $booked->fetchAll(PDO::FETCH_COLUMN);

        $slots = [];
        $current = strtotime($date . ' ' . $sched['start_time']);
        $end     = strtotime($date . ' ' . $sched['end_time']);
        $step    = $sched['slot_mins'] * 60;
        while ($current < $end) {
            $t = date('H:i:s', $current);
            $slots[] = ['time' => $t, 'available' => !in_array($t, $bookedTimes, true)];
            $current += $step;
        }
        return $slots;
    }
}
