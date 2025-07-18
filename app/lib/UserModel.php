<?php
class UserModel {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function findByUsername($username) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['pw']);
    }
} 