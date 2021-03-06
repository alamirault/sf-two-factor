<?php
/**
 * Created by PhpStorm.
 * User: alamirault
 * Date: 22/06/19
 * Time: 10:12
 */

namespace App\Security;


use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerProvider implements UserProviderInterface
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;


    /**
     * CustomerProvider constructor.
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

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
        return $this->customerRepository->findOneBy([
            "login" => $login
        ]);
    }

    public function supportsClass($class)
    {
        return $class === Customer::class;
    }
}