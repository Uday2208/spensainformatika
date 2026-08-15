<?php

class AuthController extends Controller {
    
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'];
            $this->redirect("/app/$role/dashboard");
        }
        $this->view('auth/login');
    }

    public function processLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Username dan password tidak boleh kosong.";
            $this->redirect("/login");
        }
        
        // Anti SQL Injection through PDO prepared statement
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['siswa_id'] = $user['siswa_id'];
            
            $this->redirect("/app/" . $user['role'] . "/dashboard");
        } else {
            $_SESSION['error'] = "Username atau password salah.";
            $this->redirect("/login");
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect("/");
    }
}
