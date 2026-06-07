<?php
// app/controllers/PharmacyController.php

class PharmacyController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('pharmacy','stock');

        $search = sanitize($_GET['q'] ?? '');
        $category = sanitize($_GET['category'] ?? '');
        $filter   = sanitize($_GET['filter'] ?? ''); // low_stock | expiring

        $where  = ['m.clinic_id=:cid', 'm.status="active"'];
        $params = [':cid' => clinic_id()];
        if ($search)   { $where[] = '(m.name LIKE :s OR m.generic_name LIKE :s)'; $params[':s']='%'.$search.'%'; }
        if ($category) { $where[] = 'm.category=:cat'; $params[':cat']=$category; }

        $having = '';
        if ($filter === 'low_stock') $having = 'HAVING total_stock < 10';
        if ($filter === 'expiring')  $having = 'HAVING MIN(ms.expiry_date) IS NOT NULL AND MIN(ms.expiry_date) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)';

        $w = implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT m.*, COALESCE(SUM(ms.quantity),0) AS total_stock,
                   MIN(ms.expiry_date) AS nearest_expiry
            FROM medicines m
            LEFT JOIN medicine_stock ms ON ms.medicine_id=m.id
            WHERE $w GROUP BY m.id $having ORDER BY m.name
        ");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        $medicines = $stmt->fetchAll();

        $categories = $this->db->prepare("SELECT DISTINCT category FROM medicines WHERE clinic_id=? AND category IS NOT NULL ORDER BY category");
        $categories->execute([clinic_id()]);
        $categories = $categories->fetchAll(PDO::FETCH_COLUMN);

        // Summary stats
        $stats = $this->db->query("
            SELECT
                COUNT(DISTINCT m.id) AS total_medicines,
                SUM(ms.quantity) AS total_units,
                SUM(ms.quantity * m.selling_price) AS total_value,
                SUM(ms.quantity < 10) AS low_stock_count
            FROM medicines m
            LEFT JOIN medicine_stock ms ON ms.medicine_id=m.id
            WHERE m.clinic_id=" . clinic_id() . " AND m.status='active'
        ")->fetch();

        render('pharmacy/index', compact('medicines','categories','search','category','filter','stats'));
    }

    public function addMedicine(): void {
        AuthMiddleware::check();
        require_permission('pharmacy','stock');
        render('pharmacy/create', ['medicine' => null]);
    }

    public function storeMedicine(): void {
        AuthMiddleware::check();
        require_permission('pharmacy','stock');
        verify_csrf();

        $data = [
            'clinic_id'      => clinic_id(),
            'name'           => sanitize($_POST['name'] ?? ''),
            'generic_name'   => sanitize($_POST['generic_name'] ?? '') ?: null,
            'category'       => sanitize($_POST['category'] ?? '') ?: null,
            'type'           => sanitize($_POST['type'] ?? 'tablet'),
            'unit'           => sanitize($_POST['unit'] ?? 'mg'),
            'manufacturer'   => sanitize($_POST['manufacturer'] ?? '') ?: null,
            'gst_percent'    => sanitize_float($_POST['gst_percent'] ?? 12),
            'purchase_price' => sanitize_float($_POST['purchase_price'] ?? 0),
            'selling_price'  => sanitize_float($_POST['selling_price'] ?? 0),
            'mrp'            => sanitize_float($_POST['mrp'] ?? 0),
        ];

        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO medicines ($cols) VALUES ($vals)")->execute($data);
        $medId = (int)$this->db->lastInsertId();

        // Initial stock
        if (!empty($_POST['initial_qty']) && (int)$_POST['initial_qty'] > 0) {
            $this->db->prepare("INSERT INTO medicine_stock (clinic_id,medicine_id,batch_no,expiry_date,quantity,purchase_price,selling_price) VALUES (?,?,?,?,?,?,?)")
                ->execute([clinic_id(), $medId, sanitize($_POST['batch_no']??''), $_POST['expiry_date']??null, (int)$_POST['initial_qty'], $data['purchase_price'], $data['selling_price']]);
        }

        flash('success','Medicine added.');
        redirect(APP_URL.'/pharmacy.php');
    }

    public function addStock(): void {
        AuthMiddleware::check();
        require_permission('pharmacy','stock');

        $medId = sanitize_int($_GET['medicine_id'] ?? 0);
        $medicine = null;
        if ($medId) {
            $s = $this->db->prepare("SELECT * FROM medicines WHERE id=? AND clinic_id=? LIMIT 1");
            $s->execute([$medId, clinic_id()]);
            $medicine = $s->fetch();
        }
        $medicines = $this->db->prepare("SELECT id,name,type FROM medicines WHERE clinic_id=? AND status='active' ORDER BY name");
        $medicines->execute([clinic_id()]);
        $medicines = $medicines->fetchAll();

        render('pharmacy/add_stock', compact('medicine','medicines'));
    }

    public function storeStock(): void {
        AuthMiddleware::check();
        require_permission('pharmacy','stock');
        verify_csrf();

        $data = [
            'clinic_id'      => clinic_id(),
            'medicine_id'    => sanitize_int($_POST['medicine_id']),
            'batch_no'       => sanitize($_POST['batch_no'] ?? ''),
            'expiry_date'    => $_POST['expiry_date'] ?: null,
            'quantity'       => sanitize_int($_POST['quantity']),
            'purchase_price' => sanitize_float($_POST['purchase_price']),
            'selling_price'  => sanitize_float($_POST['selling_price']),
        ];

        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO medicine_stock ($cols) VALUES ($vals)")->execute($data);

        flash('success','Stock added successfully.');
        redirect(APP_URL.'/pharmacy.php');
    }

    public function search(): void {
        AuthMiddleware::check();
        $q = sanitize($_GET['q'] ?? '');
        $stmt = $this->db->prepare("
            SELECT m.id, m.name, m.type, m.unit, m.selling_price, COALESCE(SUM(ms.quantity),0) AS stock
            FROM medicines m
            LEFT JOIN medicine_stock ms ON ms.medicine_id=m.id
            WHERE m.clinic_id=? AND m.status='active' AND (m.name LIKE ? OR m.generic_name LIKE ?)
            GROUP BY m.id HAVING stock > 0 LIMIT 20
        ");
        $stmt->execute([clinic_id(), "%$q%", "%$q%"]);
        json_response($stmt->fetchAll());
    }
}
