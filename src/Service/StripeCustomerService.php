<?php

namespace App\Service;

use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use App\Entity\Customer as CustomerDB;

class StripeCustomerService
{
    private CustomerRepository $customerRepository;
    private EntityManagerInterface $em;

    public function __construct(CustomerRepository $customerRepository, EntityManagerInterface $em)
    {
        $this->customerRepository = $customerRepository;
        $this->em = $em;
    }

    public function createCustomer(string $email): CustomerDB
    {
        $customerFromDB = $this->customerRepository->findCustomerByEmail($email);

        if ($customerFromDB === null) {
            // Create a new Customer
            $customer = Customer::create([
                'email' => $email,
                'description' => 'Customer to invoice',
            ]);

            $customerToDB = (new CustomerDB)
                ->setStripeId($customer->id)
                ->setEmail($customer->email);

            $this->em->persist($customerToDB);
            $this->em->flush();

            return $customerToDB;
        }

        return $customerFromDB;
    }
}