<?php

namespace Helpers\Auth;

require_once('database/Connector.php');

use Database\Connector;

class AuthHelper
{
    private $connector;

    public function __construct()
    {
        $this->connector = new Connector;
    }

    // Busca al usuario por email.
    function findByEmail($email)
    {
        $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($this->connector->connection(), $sql)) {
            $stmt->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        }
    }

    // Loguea al usuario.
    public function loginUser($loginData)
    {
        $user = $this->findByEmail($loginData['email']);
        if ($user) {
            $passwordMatch = password_verify($loginData['password'], $user['password']);
            if ($passwordMatch) {
                // Generar Sesión
                $this->generateSession($user);
                return true;
            }
        }
        return false;
    }

    // Genera la sesión del usuario
    private function generateSession($userData)
    {
        $_SESSION['name'] = $userData['name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['tiempo_inicio'] = time();
    }

    // Termina la session actual.
    public function logout()
    {
        if (isset($_SESSION)) {
            session_destroy();
        }
    }

    // Registra al usuario.
    public function registerUser($registerData)
    {
        $user = $this->findByEmail($registerData['email']);
        if (!$user) {
            $registerSQL = 'INSERT INTO users(name, email, password) VALUES(?,?,?)';
            if ($stmt = mysqli_prepare($this->connector->connection(), $registerSQL)) {
                $password = password_hash($registerData['password'], PASSWORD_DEFAULT);
                mysqli_stmt_bind_param(
                    $stmt,
                    'sss',
                    $registerData['name'],
                    $registerData['email'],
                    $password
                );
                mysqli_stmt_execute($stmt);
                $this->connector->closeConnection();
                return true;
            }
        }
        return false;
    }
}
