<?php
namespace App\Models;

require_once __DIR__. '/../../config/authorization.php';

class Auth {
    public function validateBearerToken($token) {
      return $token === BEARER_TOKEN;
    }
}
