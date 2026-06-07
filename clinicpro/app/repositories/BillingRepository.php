<?php
// app/repositories/BillingRepository.php

class BillingRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $clinicId, array $f = [], int $limit = PER_PAGE, int $offset = 0): array {
        $where  = ['i.clinic_id=:cid'];
        $params = [':cid'=>$clinicId];

        if (!empty($f['search'])) {
            $where[] = '(p.name LIKE :s OR i.invoice_no LIKE :s)';
            $params[':s'] = '%'.$f['search'].'%';
        }
        if (!empty($f['status'])) { $where[] = 'i.status=:st'; $params[':st']=$f['status']; }
        if (!empty($f['from']))   { $where[] = 'i.invoice_date>=:fr'; $params[':fr']=$f['from']; }
        if (!empty($f['to']))     { $where[] = 'i.invoice_date<=:to'; $params[':to']=$f['to']; }

        $w    = implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT i.*, p.name AS patient_name, p.patient_id
            FROM invoices i JOIN patients p ON p.id=i.patient_id
            WHERE $w ORDER BY i.created_at DESC LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim',$limit,PDO::PARAM_INT);
        $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(int $clinicId, array $f=[]): int {
        $where=['i.clinic_id=:cid'];$params=[':cid'=>$clinicId];
        if (!empty($f['status'])) { $where[]='i.status=:st';$params[':st']=$f['status']; }
        $w=implode(' AND ',$where);
        $stmt=$this->db->prepare("SELECT COUNT(*) FROM invoices i JOIN patients p ON p.id=i.patient_id WHERE $w");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id, int $clinicId): ?array {
        $stmt = $this->db->prepare("
            SELECT i.*, p.name AS patient_name, p.phone AS patient_phone, p.patient_id, p.address AS patient_address,
                   d.name AS doctor_name
            FROM invoices i
            JOIN patients p ON p.id=i.patient_id
            LEFT JOIN doctors d ON d.id=i.doctor_id
            WHERE i.id=? AND i.clinic_id=? LIMIT 1
        ");
        $stmt->execute([$id,$clinicId]);
        $inv = $stmt->fetch() ?: null;
        if ($inv) {
            $items = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY sort_order");
            $items->execute([$id]);
            $inv['items'] = $items->fetchAll();
            $payments = $this->db->prepare("SELECT * FROM payments WHERE invoice_id=? ORDER BY payment_date");
            $payments->execute([$id]);
            $inv['payments'] = $payments->fetchAll();
        }
        return $inv;
    }

    public function create(array $invoice, array $items): int {
        $this->db->beginTransaction();
        try {
            $next = next_sequence('invoices','invoice_no',$invoice['clinic_id']);
            $invoice['invoice_no'] = generate_id('INV',$next);
            $invoice['invoice_date'] = date('Y-m-d');

            // Calculate totals
            $subtotal = 0;
            foreach ($items as &$item) {
                $item['total'] = ($item['quantity'] * $item['unit_price']) - $item['discount'];
                $item['gst_amount'] = round($item['total'] * $item['gst_percent'] / 100, 2);
                $item['total'] += $item['gst_amount'];
                $subtotal += $item['total'];
            }
            $invoice['subtotal']       = $subtotal;
            $invoice['discount_amount'] = $invoice['discount_type']==='percentage'
                ? round($subtotal * $invoice['discount_value'] / 100, 2)
                : (float)($invoice['discount_value'] ?? 0);
            $invoice['tax_amount']     = array_sum(array_column($items,'gst_amount'));
            $invoice['total_amount']   = $subtotal - $invoice['discount_amount'];
            $invoice['balance_amount'] = $invoice['total_amount'];
            $invoice['status']         = 'pending';

            $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($invoice)));
            $vals = implode(',', array_map(fn($k)=>":$k", array_keys($invoice)));
            $this->db->prepare("INSERT INTO invoices ($cols) VALUES ($vals)")->execute($invoice);
            $invId = (int)$this->db->lastInsertId();

            $iStmt = $this->db->prepare("INSERT INTO invoice_items (invoice_id,item_type,description,quantity,unit_price,discount,gst_percent,gst_amount,total,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)");
            foreach ($items as $i => $item) {
                $iStmt->execute([$invId,$item['item_type'],$item['description'],$item['quantity'],$item['unit_price'],$item['discount'],$item['gst_percent'],$item['gst_amount'],$item['total'],$i]);
            }
            $this->db->commit();
            return $invId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recordPayment(int $invoiceId, int $clinicId, float $amount, string $method, string $ref, string $date): bool {
        $this->db->beginTransaction();
        try {
            $next = next_sequence('payments','payment_no',$clinicId);
            $pno  = generate_id('PAY',$next);
            $this->db->prepare("INSERT INTO payments (clinic_id,invoice_id,payment_no,amount,method,reference_no,payment_date,created_by) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$clinicId,$invoiceId,$pno,$amount,$method,$ref,$date,auth()['id']]);

            // Update invoice
            $this->db->prepare("UPDATE invoices SET paid_amount=paid_amount+?, balance_amount=balance_amount-?,
                status=CASE WHEN balance_amount-? <=0 THEN 'paid' WHEN paid_amount+?>0 THEN 'partial' ELSE status END,
                updated_at=NOW() WHERE id=? AND clinic_id=?")
                ->execute([$amount,$amount,$amount,$amount,$invoiceId,$clinicId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function revenueStats(int $clinicId, string $from, string $to): array {
        $stmt = $this->db->prepare("
            SELECT
                SUM(total_amount) AS gross,
                SUM(paid_amount)  AS collected,
                SUM(balance_amount) AS pending,
                COUNT(*)           AS invoices
            FROM invoices WHERE clinic_id=? AND invoice_date BETWEEN ? AND ? AND status != 'cancelled'
        ");
        $stmt->execute([$clinicId,$from,$to]);
        return $stmt->fetch();
    }
}
