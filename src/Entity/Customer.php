<?php
/**
 * Created by PhpStorm.
 * User: alamirault
 * Date: 22/06/19
 * Time: 09:44
 */

namespace App\Entity;


use Symfony\Component\Security\Core\User\UserInterface;

class Customer implements UserInterface
{
    private $login;

    public function __construct(string $login)
    {
        $this->login = $login;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }


    public function getPassword()
    {
        return "";
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
      return $this->getLogin();
    }

    public function eraseCredentials()
    {
       return null;
    }
}