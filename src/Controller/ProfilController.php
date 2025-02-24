<?php

namespace App\Controller;

use App\Form\ProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface; // Import correct
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function profil(): Response
    {
        return $this->render('profil/profil.html.twig', [
        ]);
    }

    #[Route('/profil/modify', name: 'profil_modify')]
    public function profil_modify(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) 
        {
            $user = $this->getUser();
            $form = $this->createForm(ProfilType::class, $user);
    
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                // Vérification du mot de passe actuel
                $currentPassword = $form->get('currentPassword')->getData();
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    throw new AccessDeniedException('Mot de passe actuel incorrect.');
                }
    
                // Hasher le mot de passe uniquement si un nouveau a été saisi
                if ($form->get('password')->getData()) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
                    $user->setPassword($hashedPassword);
                }
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                return $this->redirectToRoute('home');
            }
    
            return $this->render('profil/profil_modify.html.twig', [
                'user' => $user,
                'profilForm' => $form,
            ]);
        }
    
        return $this->redirectToRoute('login');
    }

    #[Route('/profil/commandes', name: 'orders')]
    public function orders(): Response
    {
        if ($this->getUser()) 
        {
            $orders = $this->getUser()->getOrders();

            return $this->render('profil/orders.html.twig', [
                'orders' => $orders,
            ]);
        }

        return $this->redirectToRoute('login');
    }
}
