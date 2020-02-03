<?php
/**
 * Created by PhpStorm.
 * User: alamirault
 * Date: 22/06/19
 * Time: 09:44
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer implements UserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $googleAuthenticatorSecret;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $login;

    public function __construct(string $login)
    {
        $this->login = $login;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): self
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;

        return $this;
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
    
    public function isEqualTo(UserInterface $user){
      return $this->getLogin() === $user->getLogin()  
    }
}
