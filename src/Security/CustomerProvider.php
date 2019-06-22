<?php
/**
 * Created by PhpStorm.
 * User: alamirault
 * Date: 22/06/19
 * Time: 10:12
 */

namespace App\Security;


use App\Entity\Customer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user, $safe = false)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function loadUserByUsername($login)
    {
        return $this->loadUserFromDb($login);
    }

    public function loadUserFromDb($login): ?Customer
    {
        // Here fetch user from database by login

        /** @var Customer[] $customers */
        $customers = [
            new Customer("alamirault"),
            new Customer("jdoe"),
        ];

        foreach ($customers as $customer){
            if($customer->getLogin() === $login){
                return $customer;
            }
        }

        return null;
    }

    public function supportsClass($class)
    {
        return $class === Customer::class;
    }
}