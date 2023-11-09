<?php

class Inventories extends Controller
{
    private $db;

    public function __construct()
    {
        // Inicializálj egy PDO kapcsolatot az adatbázishoz
        $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $this->db->exec("set names 'utf8'");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function index() {
        $data = [
            'title' => 'Client',
            'description' => ''
        ];
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $gepid = $_POST['gepid'];
    
            // Példa SQL lekérdezés a gép adatainak lekérésére
            $stmt = $this->db->prepare('SELECT * FROM gep WHERE id = :gepid');
            $stmt->bindParam(':gepid', $gepid, PDO::PARAM_INT);
            $stmt->execute();
            $data['computers'] = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            // Példa SQL lekérdezés a géphez tartozó telepített szoftverek lekérésére
            $stmt = $this->db->prepare('SELECT sz.nev, sz.kategoria, t.verzio FROM telepites t
                JOIN szoftver sz ON t.szoftverid = sz.id
                WHERE t.gepid = :gepid');
            $stmt->bindParam(':gepid', $gepid, PDO::PARAM_INT);
            $stmt->execute();
            $installedSoftware = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            // Csoportosítás név, kategória és verzió alapján
            $groupedSoftware = [];
            foreach ($installedSoftware as $software) {
                $key = $software->nev . '_' . $software->kategoria . '_' . $software->verzio;
                if (!isset($groupedSoftware[$key])) {
                    $groupedSoftware[$key] = $software;
                }
            }
    
            $data['installedSoftware'] = array_values($groupedSoftware);
        }
    
        $this->view('pages/inventory', $data);
    }
    
}