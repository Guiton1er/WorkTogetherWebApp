<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Order;
use App\Repository\OfferRepository;
use App\Repository\SettingRepository;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OfferController extends AbstractController
{
    #[Route('/offres', name: 'offers')]
    public function index(OfferRepository $offerRepository, SettingRepository $settingRepository): Response
    {
        $offers = $offerRepository->findBy(array("available" => true));
        $unitPrice = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
        //$mostPopulars = $offerRepository->findMostPopular();

        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
            'unitPrice' => intval($unitPrice->getValue(), 10),
            //'mostPopulars' => $mostPopulars,
        ]);
    }

    // #[Route('/offres/{id}', name: 'offer', methods: ['GET', 'POST'])]
    // public function offer(Offer $offer, UnitRepository $unitRepository, SettingRepository $settingRepository, Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $unitPrice = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
    //     $unitPrice = intval($unitPrice->getValue(), 10);

    //     $availableUnits = $unitRepository->findBy(["currentOrder" => null]);
    //     $nbrUnits = array_count_values($availableUnits);

    //     // Vérifier si le formulaire a été soumis
    //     if ($request->isMethod('POST')) 
    //     {
    //         if ($this->getUser())
    //         {
    //             // Créer une nouvelle commande
    //             $order = new Order();
    //             $order->setCustomer($this->getUser());
    //             //$order->setOffer($offer);
    //             $order->setStartDate(new \DateTime());
    //             $order->setUnitPrice($unitPrice);
                
    //             $entityManager->persist($order);

    //             for ($i=0; $i <= $offer->getUnitLimit(); $i++) 
    //             { 
    //                 $unit = $availableUnits[$i];
    //                 $unit->setCurrentOrder($order);
    //                 $entityManager->persist($unit);
    //             }

    //             // Sauvegarder dans la BDD
    //             $entityManager->flush();

    //             // Rediriger vers une page de confirmation
    //             return $this->redirectToRoute('order_success');
    //         }

    //         return $this->redirectToRoute('login');
    //     }

    //     if ($offer->getUnitLimit() < $nbrUnits)
    //     {
    //         return $this->render('offer/offer.html.twig', [
    //             'offer' => $offer,
    //             'unitPrice' => $unitPrice,
    //             'isCurrentlyAvailable' => false,
    //         ]);
    //     }

    //     return $this->render('offer/offer.html.twig', [
    //         'offer' => $offer,
    //         'unitPrice' => $unitPrice,
    //         'isCurrentlyAvailable' => true,
    //     ]);
    // }

    #[Route('/offres/{id}', name: 'offer', methods: ['GET', 'POST'])]
    public function offer(
        Offer $offer, 
        UnitRepository $unitRepository, 
        SettingRepository $settingRepository, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération du prix unitaire
        $unitPriceSetting = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
        if (!$unitPriceSetting) {
            throw $this->createNotFoundException("Le prix unitaire n'est pas défini dans les paramètres.");
        }
        $unitPrice = intval($unitPriceSetting->getValue(), 10);

        // Récupération des unités disponibles
        $availableUnits = $unitRepository->findBy(["currentOrder" => null]);
        $nbrUnits = count($availableUnits);

        // Vérification si le formulaire a été soumis
        if ($request->isMethod('POST')) {
            // Vérification si l'utilisateur est connecté
            if (!$this->getUser()) {
                return $this->redirectToRoute('login');
            }

            // Vérification si suffisamment d'unités sont disponibles
            if ($offer->getUnitLimit() > $nbrUnits) {
                $this->addFlash('danger', 'Stock insuffisant pour passer cette commande.');
                return $this->redirectToRoute('offer', ['id' => $offer->getId()]);
            }

            // Création d'une nouvelle commande
            $order = new Order();
            $order->setCustomer($this->getUser());
            $order->setOffer($offer);
            $order->setStartDate(new \DateTime());
            $order->setEndDate(null);
            $order->setUnitPrice($unitPrice);

            $entityManager->persist($order);

            // Affectation des unités à la commande
            for ($i = 0; $i < $offer->getUnitLimit(); $i++) { 
                $unit = $availableUnits[$i];
                $unit->setCurrentOrder($order);
                $entityManager->persist($unit);
            }

            // Sauvegarde dans la base de données
            $entityManager->flush();

            // Redirection vers la page de confirmation
            return $this->redirectToRoute('order_success');
        }

        return $this->render('offer/offer.html.twig', [
            'offer' => $offer,
            'unitPrice' => $unitPrice,
            'isCurrentlyAvailable' => $offer->getUnitLimit() <= $nbrUnits,
        ]);
    }

    #[Route('/success', name: 'order_success')]
    public function orderSuccess(): Response
    {
        return $this->render('offer/success.html.twig');
    }
}
