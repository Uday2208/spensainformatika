<?php
class HomeController extends Controller {
    public function index() {
        $stmt = $this->pdo->query("SELECT * FROM web_profile WHERE id = 1");
        $profile = $stmt->fetch();
        $this->view('home', ['profile' => $profile]);
    }
    public function about() {
        echo "About page";
    }
    public function portfolio() {
        echo "Portfolio page";
    }
}
