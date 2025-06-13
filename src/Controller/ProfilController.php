<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Order;
use App\Entity\State;
use App\Entity\Unit;
use App\Form\ProfilType;
use App\Repository\InterventionRepository;
use App\Repository\InterventionTypeRepository;
use App\Repository\OrderRepository;
use App\Repository\OsChoiceRepository;
use App\Repository\StateRepository;
use App\Repository\UnitRepository;
use App\Repository\UnitTypeRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function profil(): Response
    {
        if ($this->getUser()) 
        {
            $user = $this ->getUser();
            
            return $this->render('profil/profil.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->redirectToRoute('login');
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
    public function orders(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UnitRepository $unitRepository, 
        StateRepository $stateRepository,
        UnitTypeRepository $unitTypeRepository,
        OsChoiceRepository $osChoiceRepository,
        OrderRepository $orderRepository,
        InterventionTypeRepository $interventionTypeRepository
        ): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        $user = $this->getUser();
        $orders = $user->getOrders();
        $unitTypes = $unitTypeRepository->findAll();
        $unitStates = $stateRepository->findAll();
        $osChoices = $osChoiceRepository->findAll();
        $nbrActiveOrders = count($orderRepository->findActives($user));
        $nbrInactivesOrders = count($orderRepository->findBy(['customer' => $user])) - $nbrActiveOrders;

        // Démarrer une unité
        if ($request->request->has('start_unit')) 
        {
            $unitId = $request->request->get('start_unit');
            $unit = $unitRepository->find($unitId);

            if ($unit && $unit->getState()->getName() === 'Arrêt') 
            {
                $unit->setState($entityManager->getRepository(State::class)->findOneBy(['name' => 'OK']));
                $entityManager->persist($unit);
                $entityManager->flush();
                $this->addFlash('success', 'L’unité a bien été démarrée.');
            }
            return $this->redirectToRoute('orders');
        }

        // Arrêter une unité
        if ($request->request->has('stop_unit')) {
            $unitId = $request->request->get('stop_unit');
            $unit = $unitRepository->find($unitId);

            if ($unit && $unit->getState()->getName() === 'OK') {
                $unit->setState($entityManager->getRepository(State::class)->findOneBy(['name' => 'Arrêt']));
                $entityManager->persist($unit);
                $entityManager->flush();
                $this->addFlash('success', 'L’unité a bien été arrêtée.');
            }
            return $this->redirectToRoute('orders');
        }

        // Vérifier si un changement de type a été demandé
        if ($request->isMethod('POST') && $request->request->has('unit_id') && $request->request->has('unit_type')) {
            $unitId = $request->request->get('unit_id');
            $newType = $request->request->get('unit_type');
            $unit = $unitRepository->find($unitId);

            // Mettre à jour le type
            $unit->setType($unitTypeRepository->find($newType));
            $entityManager->persist($unit);
            $entityManager->flush();

            return $this->redirectToRoute('orders'); // Recharge la page
        }


        // Vérifier si un changement d'OS a été demandé
        if ($request->isMethod('POST') && $request->request->has('unit_id') && $request->request->has('os_choice')) {
            $unitId = $request->request->get('unit_id');
            $newOsChoice = $request->request->get('os_choice');
            $unit = $unitRepository->find($unitId);

            if ($unit->getType()->getReference() != "Inutilisé")
            {
                // Mettre à jour l'OS
                $unit->setOsChoice($osChoiceRepository->find($newOsChoice));
                $entityManager->persist($unit);
                $entityManager->flush();
            }

            return $this->redirectToRoute('orders'); // Recharge la page
        }

        // Lancer une intervention pour installer l'OS
        if ($request->request->has('install_os')) {
            $unitId = $request->request->get('install_os');
            $unit = $unitRepository->find($unitId);

            if ($unit->getType()->getReference() != "Inutilisé" && $unit->getOsChoice() != null)
            {
                $newIntervention = new Intervention();
                $newIntervention->setStartDate(new DateTime());
                $newIntervention->setEndDate(new DateTime());
                $newIntervention->setUnit($unit);
                $interventionType = $interventionTypeRepository->findBy(["reference" => "Installation OS"]);
                $newIntervention->setType($interventionType[0]);

                $entityManager->persist($newIntervention);
                $entityManager->flush();
                $this->addFlash('success', 'L’intervention a bien été lancée.');
            }
            return $this->redirectToRoute('orders');
        }


        // Modifier la date de fin d'une commande
        if ($request->request->has('update_end_date')) {
            $orderId = $request->request->get('update_end_date');
            $order = $entityManager->getRepository(Order::class)->find($orderId);
            
            $now = new DateTime();
            
            if ($order && ($order->getEndDate() === null || $order->getEndDate()->format("d/m/y") >= $now->format("d/m/y"))) {
                $newEndDate = $request->request->get('end_date');

                if ($newEndDate) {
                    try {
                        $order->setEndDate(new DateTime($newEndDate));
                        $entityManager->persist($order);
                        $entityManager->flush();
                        $this->addFlash('success', 'Date de fin mise à jour.');
                    } catch (\Exception $e) {
                        $this->addFlash('danger', 'Format de date invalide.');
                    }
                } else {
                    $this->addFlash('warning', 'Veuillez saisir une date.');
                }
            }
            return $this->redirectToRoute('orders');
        }

        return $this->render('profil/orders.html.twig', [
            'orders' => $orders,
            'nbrInactivesOrders' => $nbrInactivesOrders,
            'nbrActiveOrders' => $nbrActiveOrders,
            'unitTypes' => $unitTypes,
            'unitStates' => $unitStates,
            'osChoices' => $osChoices,
        ]);
    }
}
