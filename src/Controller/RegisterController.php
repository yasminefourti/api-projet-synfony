<?php
// src/Controller/RegisterController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $lastname = $data['lastname'] ?? null;
        $firstname = $data['firstname'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? 'ROLE_USER';

        if (!$lastname || !$firstname || !$email || !$password) {
            return $this->json(['message' => 'Tous les champs sont requis'], 400);
        }

        if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $this->json(['message' => 'Email déjà utilisé'], 409);
        }

        $user = new User();
        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setEmail($email);
        $user->setRoles($data['role'] ?? ['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Inscription réussie'], 201);
    }
}
