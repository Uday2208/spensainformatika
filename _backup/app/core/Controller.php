<?php

class Controller {
    protected $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo;
    }

    public function view($view, $data = []) {
        extract($data);
        $viewFile = "app/views/" . $view . ".php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View " . $view . " not found");
        }
    }
    
    public function redirect($url) {
        header("Location: " . BASE_URL . "/" . ltrim($url, '/'));
        exit;
    }
}
